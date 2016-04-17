<?php
class Message_manager_model extends CI_Model
{	
	private $message_table_name="message";
	private $message_user_table_name="message_user";	
	private $message_parent_properties_table_name="message_parent_properties";
	private $date_time_max="9999-12-31 23:59:59";

	//don't use previously used ids (indexes), just increase and use
	private $departments=array(
		1=>"customers"
		,2=>"agents"
		,3=>"management"
		);

	public function __construct()
	{
		parent::__construct();
		
		return;
	}

	public function install()
	{
		$tbl_name=$this->db->dbprefix($this->message_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`message_id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL
				,`message_parent_id` BIGINT UNSIGNED
				,`message_sender_type` enum('customer','department','user')
				,`message_sender_id` BIGINT UNSIGNED
				,`message_timestamp` DATETIME
				,`message_receiver_type` enum('customer','department','user')
				,`message_receiver_id` BIGINT UNSIGNED
				,`message_subject` VARCHAR(200)
				,`message_content` TEXT
				,`message_verifier_id` BIGINT UNSIGNED DEFAULT 0
				,`message_reply_id` BIGINT UNSIGNED DEFAULT 0
				,PRIMARY KEY (message_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->message_user_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mu_user_id` INT NOT NULL
				,`mu_departments` BIGINT DEFAULT 0
				,`mu_verifier` TINYINT NOT NULL DEFAULT 0 
				,`mu_supervisor` TINYINT NOT NULL DEFAULT 0
				,PRIMARY KEY (mu_user_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->message_parent_properties_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mpp_message_id` BIGINT UNSIGNED  NOT NULL
				,`mpp_last_activity` DATETIME DEFAULT NULL
				,PRIMARY KEY (mpp_message_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->module_manager_model->add_module("message","message_manager");
		$this->module_manager_model->add_module_names_from_lang_file("message");

		$this->module_manager_model->add_module("message_access","");
		$this->module_manager_model->add_module_names_from_lang_file("message_access");
		
		return;
	}

	public function uninstall()
	{

		return;
	}

	public function get_dashboard_info()
	{
		return "";
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('ae_module',$lang);		
		
		$data=array();
		$data['modules']=$this->get_all_modules_info($lang);
		$data['total_text']=$CI->lang->line("total");
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("module_dashboard"),$data,TRUE);
		
		return $ret;		
	}
	
	public function get_sidebar_text()
	{
		//return " (12) ";
	}

	public function get_departments()
	{
		return $this->departments;
	}

	public function get_user_access($user_id)
	{
		$result=$this->db
			->get_where($this->message_user_table_name,array("mu_user_id"=>$user_id))
			->row_array();

		$ret=array("verifier"=>0,"supervisor"=>0);
		$deps=0;
		if($result)
		{
			$ret['verifier']=$result['mu_verifier'];
			$ret['supervisor']=$result['mu_supervisor'];
			$deps=$result['mu_departments'];
		}
		
		$departments=array();
		foreach($this->departments as $dep_index=>$dep_name)
			if($deps & (1<<$dep_index))
				$departments[$dep_name]=$dep_index;
			else
				$departments[$dep_name]=0;

		$ret['departments']=$departments;

		return $ret;
	}

	public function set_user_access($user_id,$props)
	{
		$deps=0;
		foreach($this->departments as $dep_index=>$dep_name)
			if($props['departments'][$dep_name])
				$deps+=(1<<$dep_index);

		$rep=array(
			"mu_user_id"=>$user_id
			,"mu_verifier"=>(int)($props['verifier']==1)
			,"mu_supervisor"=>(int)($props['supervisor']==1)
			,"mu_departments"=>$deps
		);

		$this->db->replace($this->message_user_table_name, $rep);

		foreach($this->departments as $dep_index=>$dep_name)
			$rep['department_'.$dep_name]=(int)$props['departments'][$dep_name];

		$this->log_manager_model->info("MESSAGE_ACCESS_SET",$rep);

		return;
	}

	public function get_operations_access()
	{
		$user=$this->user_manager_model->get_user_info();
		$user_id=$user->get_id();

		$ret=array();
		
		$access=$this->get_user_access($user_id);
		$ret['users']=$access['supervisor'];		
		$ret['verifier']=$access['verifier'];
		$ret['customers']=$this->access_manager_model->check_access("customer",$user);
		
		$ret['departments']=array();
		if($ret['customers'])
			foreach($access['departments'] as $name => $id)
				$ret['departments'][$name]=$id;

		return $ret;
	}

	//retrieves a message details to be shown in the admin or customer envs
	//the first parameter is message_id
	//the second parameter is "customer" or "user"
	//the third parameter is the ID of the first type
	public function get_message($message_id,$viewer_type,$viewer_id)
	{
		if(($viewer_type !== "customer") && ($viewer_type!=="user"))
			return NULL;

		$parent_id=$this->db
			->select("message_parent_id")
			->get_where($this->message_table_name,array("message_id"=>$message_id))
			->row_array()['message_parent_id'];

		if(!$parent_id)
			return NULL;
		
		$reply_forward=array(
			"can_reply"=>FALSE
			,"can_forward"=>FALSE
		);

		$messages=$this->db
			->select(
				$this->message_table_name.".* 
				, sender_user.user_code as suc, sender_user.user_name as sun
				, sender_customer.customer_name as scn
				, receiver_user.user_code as ruc, receiver_user.user_name as run
				, receiver_customer.customer_name as rcn
				, verifier_user.user_code as vuc, verifier_user.user_name as vun
				")
			->from($this->message_table_name)
			->join("user as sender_user","message_sender_id = sender_user.user_id","left")
			->join("customer as sender_customer","message_sender_id = sender_customer.customer_id","left")
			->join("user as receiver_user","message_receiver_id = receiver_user.user_id","left")
			->join("customer as receiver_customer","message_receiver_id = receiver_customer.customer_id","left")
			->join("user as verifier_user","message_verifier_id = verifier_user.user_id","left")
			->where("message_parent_id",$parent_id)
			->order_by("message_id ASC")
			->get()
			->result_array();

		if($viewer_type === "customer")
		{
			$new_messages=[];

			foreach($messages as $index=>&$mess)
			{
				$st=$mess['message_sender_type'];
				$rt=$mess['message_receiver_type'];
				$si=$mess['message_sender_id'];
				$ri=$mess['message_receiver_id'];

				if(
					($st==="customer" && $rt==="customer") ||
					($st==="department" && $rt==="customer") ||
					($st==="customer" && $rt==="department")
					)
					if(($si==$viewer_id) || ($ri==$viewer_id))
						$new_messages[]=&$mess;
			}

			$messages=&$new_messages;
			if($messages)
			{
				$reply_forward['can_reply']=TRUE;
				$reply_forward['can_forward']=TRUE;
			}
		}

		if($viewer_type === "user")
		{
			$has_access=FALSE;
			$can_forward=FALSE;
			$can_reply=FALSE;

			$oa=$this->get_operations_access();

			$deps=array();
			$departments=$this->get_departments();
			foreach($departments as $id => $name)
				$deps[$id]=$oa['departments'][$name];

			$mess=&$messages[0];
			$st=$mess['message_sender_type'];
			$rt=$mess['message_receiver_type'];
			$si=$mess['message_sender_id'];
			$ri=$mess['message_receiver_id'];

			if($st==="user" && $rt==="user")
			{
				if($oa['users'] || ($si==$viewer_id) || ($ri==$viewer_id))
					$has_access=TRUE;

				if(($si==$viewer_id) || ($ri==$viewer_id))
				{
					$can_reply=TRUE;
					$can_forward=TRUE;
				}
			}

			if(($st==="customer")&&($rt==="customer"))
				if($oa['customers'])
					$has_access=TRUE;

			if(($st==="customer")&&($rt==="department"))
				if($deps[$ri])
				{
					$has_access=TRUE;
					$can_reply=TRUE;
					$can_forward=TRUE;
				}

			if(($st==="department")&&($rt==="customer"))
				if($deps[$si])
				{
					$has_access=TRUE;
					$can_reply=TRUE;
					$can_forward=TRUE;
				}

			if(!$has_access)
				$messages=NULL;
			else
			{
				$reply_forward['can_reply']=$can_reply;
				$reply_forward['can_forward']=$can_forward;
			}
		}

		return array("messages"=>&$messages,"reply_forward"=>$reply_forward);		
	}

	public function get_total_messages(&$filters)
	{
		$this->db->select("COUNT(*) as count");
		$this->db->from($this->message_table_name)
			->join($this->message_parent_properties_table_name,"message_id = mpp_message_id","left")
			->join("user as sender_user","message_sender_id = sender_user.user_id","left")
			->join("customer as sender_customer","message_sender_id = sender_customer.customer_id","left")
			->join("user as receiver_user","message_receiver_id = receiver_user.user_id","left")
			->join("customer as receiver_customer","message_receiver_id = receiver_customer.customer_id","left")
			;
		
		$this->set_search_where_clause($filters);

		$query=$this->db->get();

		$row=$query->row_array();

		return $row['count'];
	}

	public function get_messages(&$filters)
	{
		$this->db
			->select(
				$this->message_table_name.".* 
				, sender_user.user_code as suc, sender_user.user_name as sun
				, sender_customer.customer_name as scn
				, receiver_user.user_code as ruc, receiver_user.user_name as run
				, receiver_customer.customer_name as rcn
				")
			->from($this->message_table_name)
			->join($this->message_parent_properties_table_name,"message_id = mpp_message_id","left")
			->join("user as sender_user","message_sender_id = sender_user.user_id","left")
			->join("customer as sender_customer","message_sender_id = sender_customer.customer_id","left")
			->join("user as receiver_user","message_receiver_id = receiver_user.user_id","left")
			->join("customer as receiver_customer","message_receiver_id = receiver_customer.customer_id","left")
			;

		$this->set_search_where_clause($filters);

		$result=$this->db->get()->result_array();

		return $result;
	}

	private function set_search_where_clause(&$filters)
	{
		$this->db->where("( (message_id = message_parent_id) || (message_verifier_id = message_parent_id))");
		if(isset($filters['start_date']))
			$this->db->where("message_timestamp >=",$filters['start_date']);

		if(isset($filters['end_date']))
			$this->db->where("message_timestamp <=",$filters['end_date']." 23:59:59");

		if(isset($filters['response_status']))
		{
			if($filters['response_status']==="yes")
				$this->db->where("message_reply_id !=",0);
			else
				$this->db->where("message_reply_id",0);
		}

		if(isset($filters['verification_status']))
		{
			$this->db
				->where("message_sender_type","customer")
				->where("message_receiver_type","customer");

			if($filters['verification_status']==="yes")
				$this->db->where("message_verifier_id !=",0);
			else
				$this->db->where("message_verifier_id",0);
		}

		$mess_types="0";
		//bprint_r($filters['message_types']);
		//exit(0);
		foreach($filters['message_types'] as $mess)
		{
			$query="1";
			foreach($mess as $field => $value)
			{
				$exfield=explode("_", $field);
				$del=$exfield[sizeof($exfield)-1];

				if($del==="type" || $del==="id" || $del==="code")
					$query.=" && ( ".$field."='".$value."' )";

				if($del==="in" && is_array($value))
				{
					unset($exfield[sizeof($exfield)-1]);
					$field=implode("_", $exfield);
					$value="('".implode("','", $value)."')";

					$query.=" && ( ".$field." in ".$value." )";
				}

				if($del==="name")
				{
					$value=prune_for_like_query($value);
					$query.=" && ( ".$field." like '%".$value."%' )";
				}

				//echo $del."<br>";
				
			}

			$mess_types.=" || ( ".$query." ) "; 
		}
		//echo $mess_types."<br>";exit();

		$this->db->where((" ( ".$mess_types." )"));

		if(isset($filters['order_by']))
			$this->db->order_by($filter['order_by']);
		else
			$this->db->order_by("mpp_last_activity DESC");

		if(isset($filters['start']) && isset($filters['length']))
			$this->db->limit((int)$filters['length'],(int)$filters['start']);

		return;
	}

	public function send_c2d_message(&$props)
	{
		$mess=array(
			"message_sender_type"		=>"customer"
			,"message_sender_id"			=>$props['customer_id']
			,"message_receiver_type"	=>"department"
			,"message_receiver_id"		=>$props['department']
			,"message_subject"			=>$props['subject']
			,"message_content"			=>$props['content']
		);

		$id=$this->add_message($mess);

		$mess['message_type']="c2u";
		$mess['message_id']=$id;
		$mess['departement_name']=$this->get_departments()[$props['department']];
	
		$this->load->model("customer_manager_model");
		$this->customer_manager_model->add_customer_log($props['customer_id'],'MESSAGE_SEND',$mess);
		$this->customer_manager_model->set_customer_event($props['customer_id'],"has_message");

		return $id;
	}

	public function verify_c2c_messages($verifier_id,&$v,&$nv)
	{
		$ret=["v"=>0,"nv"=>0];
		if($v)
		{
			$this->db
				->set("message_verifier_id",$verifier_id)
				->where("message_sender_type","customer")
				->where("message_receiver_type","customer")
				->where("message_verifier_id",0)
				->where_in("message_id",$v)
				->update($this->message_table_name);
			
			$ret['v']=$this->db->affected_rows();
		}

		if($nv)
		{
			$this->db
				->set("message_verifier_id",0)
				->where("message_sender_type","customer")
				->where("message_receiver_type","customer")
				->where("message_verifier_id !=",0)
				->where_in("message_id",$nv)
				->update($this->message_table_name);

			$ret['nv']=$this->db->affected_rows();
		}

		$result=array(
			"verifier_id"=>$verifier_id
			,"requested_verified_ids"=>implode(",", $v)
			,"verified_count"=>$ret['v']
			,"requested_not_verified_ids"=>implode(",", $nv)
			,"not_verified_count"=>$ret['nv']
		);
		$this->log_manager_model->info("MESSAGE_VERIFY",$result);

		return $ret;
	}

	//$addons['reply_to_message_id'] should be set for messages that are reply to another message
	//$addons['forward_of_message_id'] should be set for messages that are forward of another message
	private function add_message(&$props,$addons=array())
	{
		$props['message_timestamp']=get_current_time();

		//to show the message in the list
		if(isset($addons['forward_of_message_id']))
			$props['message_verifier_id']=$props['message_parent_id'];

		$this->db->insert($this->message_table_name,$props);

		$id=$this->db->insert_id();
		$props['message_id']=$id;

		if(!isset($props['message_parent_id']))
		{
			$this->db
				->set("message_parent_id",$id)
				->where("message_id",$id)
				->update($this->message_table_name);

			$parent_id=$id;
			$props['message_parent_id']=$parent_id;
		}	
		else
			$parent_id=$props['message_parent_id'];

		$this->db->replace($this->message_parent_properties_table_name,array(
			"mpp_message_id"=>$parent_id,
			"mpp_last_activity"=>$props['message_timestamp']
			));

		if(isset($addons['reply_to_message_id']))
		{
			$this->db
				->set("message_reply_id",$id)
				->where("message_id",$addons['reply_to_message_id'])
				->update($this->message_table_name);
			$props['reply_to_message_id']=$addons['reply_to_message_id'];
		}

		if(isset($addons['forward_of_message_id']))
			$props['forward_of_message_id']=$addons['forward_of_message_id'];
		
		$this->log_manager_model->info("MESSAGE_SEND",$props);

		return $id;
	}
}