<?php
class Message_manager_model extends CI_Model
{	
	private $message_user_access_table_name = "message_user_access";	
	private $message_info_table_name 		 = "message_info";
	private $message_participant_table_name = "message_participant";
	private $message_thread_table_name		 = "message_thread";
	
	private $date_time_max="9999-12-31 23:59:59";

	//don't use previously used ids (indexes), just increase and use
	private $departments=array(
		1=>"customers"
		,2=>"agents"
		,3=>"management"
		);

	private $c2c_response_department_id=1;

	public function __construct()
	{
		parent::__construct();
		
		return;
	}

	public function install()
	{
		$tbl_name=$this->db->dbprefix($this->message_user_access_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mu_user_id` INT NOT NULL
				,`mu_departments` BIGINT DEFAULT 0
				,`mu_verifier` TINYINT NOT NULL DEFAULT 0 
				,`mu_supervisor` TINYINT NOT NULL DEFAULT 0
				,PRIMARY KEY (mu_user_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->message_info_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mi_message_id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL
				,`mi_sender_type` ENUM ('customer','department','user')
				,`mi_sender_id` BIGINT UNSIGNED
				,`mi_receiver_type` ENUM ('customer','department','user')
				,`mi_receiver_id` BIGINT UNSIGNED
				,`mi_last_activity` DATETIME
				,`mi_subject` VARCHAR(200)
				,`mi_complete` BIT(1) DEFAULT 0
				,`mi_active` BIT(1) DEFAULT 1
				,PRIMARY KEY (`mi_message_id`)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->message_participant_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mp_message_id` BIGINT UNSIGNED  NOT NULL
				,`mp_participant_type` ENUM ('department','user')
				,`mp_participant_id` BIGINT
				,PRIMARY KEY (`mp_message_id`,`mp_participant_type`,`mp_participant_id`)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl_name=$this->db->dbprefix($this->message_thread_table_name);
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl_name (
				`mt_thread_id` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL
				,`mt_message_id` BIGINT UNSIGNED  NOT NULL
				,`mt_sender_type` ENUM ('customer','department','user')
				,`mt_sender_id` BIGINT
				,`mt_content` TEXT
				,`mt_timestamp` DATETIME
				,`mt_attachment` VARCHAR(127) DEFAULT NULL
				,`mt_verifier_id` BIGINT DEFAULT 0
				,PRIMARY KEY (`mt_thread_id`)	
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
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('ae_message',$lang);		
		
		$data=array();
		$res=$this->get_dashboard_totals();
		$data['total']=$res['total'];
		$data['complete']=$res['complete'];
		$data['total_text']=$CI->lang->line("total");
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("message_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	private function get_dashboard_totals()
	{
		$ret=array();

		$ret['complete']=$this->db
			->select("COUNT(*) as count ")
			->from($this->message_info_table_name)
			->where("mi_complete",1)
			->get()
			->row_array()['count'];

		$ret['total']=$this->db
			->select("COUNT(*) as count ")
			->from($this->message_info_table_name)
			->get()
			->row_array()['count'];

		return $ret;
	}
	
	public function get_sidebar_text()
	{
		//return " (12) ";
	}

	public function get_c2c_response_department_id()
	{
		return $this->c2c_response_department_id;
	}

	public function get_departments()
	{
		return $this->departments;
	}

	public function set_participants($message_id,$deps,$users)
	{
		$this->db
			->where("mp_message_id",$message_id)
			->delete($this->message_participant_table_name);

		$ins=array();
		foreach($deps as $dep)
			$ins[]=array(
				"mp_message_id"=>$message_id
				,"mp_participant_type"=>"department"
				,"mp_participant_id"=>$dep
			);

		foreach($users as $user)
			$ins[]=array(
				"mp_message_id"=>$message_id
				,"mp_participant_type"=>"user"
				,"mp_participant_id"=>$user
			);

		if($ins)
			$this->db->insert_batch($this->message_participant_table_name,$ins);

		$this->log_manager_model->info("MESSAGE_SET_PARTICIPANTS",array(
			"message_id"	=> $message_id
			,"departments"	=> implode(",",$deps)
			,"users"			=> implode(",",$users)
		));

		return;
	}

	public function get_user_access($user_id)
	{
		$result=$this->db
			->get_where($this->message_user_access_table_name,array("mu_user_id"=>$user_id))
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

		$this->db->replace($this->message_user_access_table_name, $rep);

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
		else
			foreach($access['departments'] as $name => $id)
				$ret['departments'][$name]=0;

		return $ret;
	}

	public function get_customer_message($message_id,$customer_id)
	{
		$message=$this->db
			->select(
				$this->message_info_table_name.".*,
				, sender_customer.customer_name as scn
				, receiver_customer.customer_name as rcn
				")
			->from($this->message_info_table_name)
			->join("customer as sender_customer","mi_sender_id = sender_customer.customer_id","LEFT")
			->join("customer as receiver_customer","mi_receiver_id = receiver_customer.customer_id","LEFT")
			->where("mi_message_id",$message_id)
			->where("(
				( mi_sender_type = 'customer' AND mi_sender_id = $customer_id)
				|| ( mi_receiver_type = 'customer' AND mi_receiver_id = $customer_id)
				)")
			->where("mi_active",1)
			->get()
			->row_array();
		
		if(!$message)
			return NULL;
		
		$threads=$this->db
			->select(
				$this->message_info_table_name.".*,".$this->message_thread_table_name.".*,
				, sender_customer.customer_name as scn
				")
			->from($this->message_info_table_name)
			->join($this->message_thread_table_name,"mi_message_id = mt_message_id","LEFT")
			->join("customer as sender_customer","mt_sender_id = sender_customer.customer_id","LEFT")
			->where("mi_message_id",$message_id)
			->where("(
				( mt_sender_type = 'customer' AND mt_sender_id = $customer_id)
				|| ( mt_sender_type = 'customer' AND mt_sender_id != $customer_id AND mt_verifier_id !=0 )
				|| ( mt_sender_type = 'department' )
				)")
			->order_by("mt_thread_id ASC")
			->get()
			->result_array();

		if(!$threads)
			return NULL;

		return array(
			"message"	=> $message
			,"threads"	=> $threads
		);
	}

	public function get_admin_message($message_id)
	{
		$access=$this->get_user_access_to_message($message_id);
		if(!$access)
			return NULL;

		$message=$this->db
			->select(
				$this->message_info_table_name.".*,
				, sender_user.user_code as suc, sender_user.user_name as sun
				, sender_customer.customer_name as scn
				, receiver_user.user_code as ruc, receiver_user.user_name as run
				, receiver_customer.customer_name as rcn
				")
			->from($this->message_info_table_name)
			->join("user as sender_user","mi_sender_id = sender_user.user_id","LEFT")
			->join("customer as sender_customer","mi_sender_id = sender_customer.customer_id","LEFT")
			->join("user as receiver_user","mi_receiver_id = receiver_user.user_id","LEFT")
			->join("customer as receiver_customer","mi_receiver_id = receiver_customer.customer_id","LEFT")
			->where("mi_message_id",$message_id)
			->get()
			->row_array();

		$threads=$this->db
			->select(
				$this->message_info_table_name.".*,".$this->message_thread_table_name.".*,
				, verifier_user.user_code as vuc, verifier_user.user_name as vun
				, sender_user.user_code as suc, sender_user.user_name as sun
				, sender_customer.customer_name as scn
				")
			->from($this->message_info_table_name)
			->join($this->message_thread_table_name,"mi_message_id = mt_message_id","LEFT")
			->join("user as verifier_user","mt_verifier_id = verifier_user.user_id","left")
			->join("user as sender_user","mt_sender_id = sender_user.user_id","LEFT")
			->join("customer as sender_customer","mt_sender_id = sender_customer.customer_id","LEFT")
			->where("mi_message_id",$message_id)
			->order_by("mt_thread_id ASC")
			->get()
			->result_array();

		return array(
			"message"	=> $message
			,"threads"	=> $threads
			,"access"	=> $access
		);		
	}

	private function get_user_access_to_message($message_id)
	{
		$op_access=$this->get_operations_access();

		$all_departemnts=$this->get_departments();
		$user_deps=array();
		foreach($all_departemnts as $id => $name)
			if($op_access['departments'][$name])
				$user_deps[]=$id;

		$user_id=$this->user_manager_model->get_user_info()->get_id();

		$results=$this->db
			->select($this->message_info_table_name.".*")
			->from($this->message_info_table_name)
			->select($this->message_participant_table_name.".*")
			->join($this->message_participant_table_name,"mi_message_id = mp_message_id","LEFT")
			->select("user.user_name, user.user_code")
			->join("user","mp_participant_id = user_id","LEFT")
			->where("mi_message_id",$message_id)
			->get()
			->result_array();

		if(!$results)
			return NULL;

		$has_access=FALSE;

		$st=$results[0]['mi_sender_type'];
		$rt=$results[0]['mi_receiver_type'];
		$si=$results[0]['mi_sender_id'];
		$ri=$results[0]['mi_receiver_id'];

		if($st==="user" && $rt==="user")
			if($op_access['users'] || ($si==$user_id) || ($ri==$user_id))
				$has_access=TRUE;

		if(($st==="customer")&&($rt==="customer"))
			if($op_access['customers'])
				$has_access=TRUE;

		if(($st==="customer")&&($rt==="department"))
			if(in_array($ri,$user_deps))
				$has_access=TRUE;

		if(($st==="department")&&($rt==="customer"))
			if(in_array($si,$user_deps))
				$has_access=TRUE;
		
		$access_users=array();
		$access_departments=array();
	
		foreach($results as $row)
		{
			if(!$row['mp_participant_id'])
				continue; 

			if($row['mp_participant_type']==="department")
			{
				$dep_id=$row['mp_participant_id'];
				$access_departments[$dep_id]=$all_departemnts[$dep_id];
				if(!$has_access && in_array($dep_id,$user_deps))
					$has_access=TRUE;
			}
			
			if($row['mp_participant_type']==="user")
			{
				$puser_id=$row['mp_participant_id'];
				$access_users[$puser_id]=$row['user_code']." - ".$row['user_name'];
				if($puser_id === $user_id)
					$has_access=TRUE;
			}
		}

		if(!$has_access)
			return NULL;
		
		return array(
			"has_access" 			=> TRUE
			,"supervisor"			=> $op_access['users']
			,"verifier"				=> $op_access['verifier']
			,"added_departments"	=> $access_departments
			,"added_users"			=> $access_users
		);
	}

	public function get_customer_total_messages($customer_id)
	{
		$ttbl=$this->db->dbprefix($this->message_thread_table_name);
		return $this->db
			->select("COUNT(mi_message_id) as count")
			->from($this->message_info_table_name)
			->join(
				"(SELECT * from $ttbl 
						INNER JOIN (
							SELECT max(mt_thread_id) as max FROM $ttbl 
								WHERE (
									(mt_sender_type != 'user') AND
									(
										( mt_sender_type = 'customer' AND mt_sender_id = $customer_id ) || 
										(mt_verifier_id !=0)
									)
								)
								GROUP BY mt_message_id 
							) AS mtb ON mtb.max = mt_thread_id
				) as mt"
				,"mi_message_id = mt_message_id","INNER")
			->where("
				(
					( mi_sender_type = 'customer' AND mi_sender_id = $customer_id ) ||
					( mi_receiver_type = 'customer' AND mi_receiver_id = $customer_id ) 
				)")
			->where("mi_active",1)
			->get()
			->row_array()['count'];
	}

	public function get_customer_messages($customer_id,$filters)
	{
		$ttbl=$this->db->dbprefix($this->message_thread_table_name);
		return $this->db
			->select(
				$this->message_info_table_name.".*, mt.*
				, sender_customer.customer_name as scn
				, receiver_customer.customer_name as rcn
				")
			->from($this->message_info_table_name)
			->join("customer as sender_customer","mi_sender_id = sender_customer.customer_id","LEFT")
			->join("customer as receiver_customer","mi_receiver_id = receiver_customer.customer_id","LEFT")
			->join(
				"(SELECT * from $ttbl 
						INNER JOIN (
							SELECT max(mt_thread_id) as max FROM $ttbl 
								WHERE (
									(mt_sender_type != 'user') AND
									(
										( mt_sender_type = 'customer' AND mt_sender_id = $customer_id ) || 
										(mt_verifier_id !=0)
									)
								)
								GROUP BY mt_message_id 
							) AS mtb ON mtb.max = mt_thread_id
				) as mt"
				,"mi_message_id = mt_message_id","INNER")
			->where("
				(
					( mi_sender_type = 'customer' AND mi_sender_id = $customer_id ) ||
					( mi_receiver_type = 'customer' AND mi_receiver_id = $customer_id ) 
				)")
			->where("mi_active",1)
			->order_by("mi_last_activity DESC")
			->limit((int)$filters['length'],(int)$filters['start'])
			->get()
			->result_array();
	}

	public function get_total_messages($filters,$access)
	{
		$this->db->select("COUNT( DISTINCT mi_message_id ) as count");
		$this->db->from($this->message_info_table_name)
			->join("user as sender_user","mi_sender_id = sender_user.user_id","left")
			->join("customer as sender_customer","mi_sender_id = sender_customer.customer_id","left")
			->join("user as receiver_user","mi_receiver_id = receiver_user.user_id","left")
			->join("customer as receiver_customer","mi_receiver_id = receiver_customer.customer_id","left")
			;

		$ttbl=$this->db->dbprefix($this->message_thread_table_name);
		if(!isset($filters['verified']))
			/*$this->db->join(
				"(SELECT * from $ttbl 
						INNER JOIN (
							SELECT max(mt_thread_id) as max FROM $ttbl 
								GROUP BY mt_message_id
							) AS mtb ON mtb.max = mt_thread_id
				) as mt"
				,"mi_message_id = mt_message_id","INNER")*/;
		else
			$this->db->join(
				"(SELECT * from $ttbl 
						INNER JOIN (
							SELECT max(mt_thread_id) as max FROM $ttbl 
								WHERE mt_sender_type = 'customer'
								GROUP BY mt_message_id 
							) AS mtb ON mtb.max = mt_thread_id
				) as mt"
				,"mi_message_id = mt_message_id","INNER");

		if(isset($access['type']) && ($access['type']==='user') && isset($access['id']))
			$this->db->join(
				$this->message_participant_table_name." as pu"
				,"
					mi_message_id = pu.mp_message_id 
					AND pu.mp_participant_type = 'user'
					AND pu.mp_participant_id = ".$access['id']."
				"
				,"LEFT"
			);

		if(isset($access['department_ids']) && $access['department_ids'])
			$this->db->join(
				$this->message_participant_table_name." as pd"
				,"
					mi_message_id = pd.mp_message_id 
					AND pd.mp_participant_type = 'department'
					AND pd.mp_participant_id IN (".implode(",", $access['department_ids']).")
				"
				,"LEFT"
			);

		$this->set_search_where_clause($filters,$access,TRUE);

		//echo $this->db->get_compiled_select();

		$query=$this->db->get();

		$row=$query->row_array();

		return $row['count'];
	}

	public function get_messages(&$filters,$access)
	{
		$this->db
			->select(
				$this->message_info_table_name.".*, mt.*
				, sender_user.user_code as suc, sender_user.user_name as sun
				, sender_customer.customer_name as scn
				, receiver_user.user_code as ruc, receiver_user.user_name as run
				, receiver_customer.customer_name as rcn
				")
			->from($this->message_info_table_name)
			->join("user as sender_user","mi_sender_id = sender_user.user_id","LEFT")
			->join("customer as sender_customer","mi_sender_id = sender_customer.customer_id","LEFT")
			->join("user as receiver_user","mi_receiver_id = receiver_user.user_id","LEFT")
			->join("customer as receiver_customer","mi_receiver_id = receiver_customer.customer_id","LEFT")
			;

		$ttbl=$this->db->dbprefix($this->message_thread_table_name);
		if(!isset($filters['verified']))
			$this->db->join(
				"(SELECT * from $ttbl 
						INNER JOIN (
							SELECT max(mt_thread_id) as max FROM $ttbl 
								GROUP BY mt_message_id
							) AS mtb ON mtb.max = mt_thread_id
				) as mt"
				,"mi_message_id = mt_message_id","INNER");
		else
			$this->db->join(
				"(SELECT * from $ttbl 
						INNER JOIN (
							SELECT max(mt_thread_id) as max FROM $ttbl 
								WHERE mt_sender_type = 'customer'
								GROUP BY mt_message_id 
							) AS mtb ON mtb.max = mt_thread_id
				) as mt"
				,"mi_message_id = mt_message_id","INNER");

		if(isset($access['type']) && ($access['type']==='user') && isset($access['id']))
			$this->db->join(
				$this->message_participant_table_name." as pu"
				,"
					 mi_message_id = pu.mp_message_id 
					AND pu.mp_participant_type = 'user'
					AND pu.mp_participant_id = ".$access['id']."
				"
				,"LEFT"
			);

		if(isset($access['department_ids']) && $access['department_ids'])
			$this->db->join(
				$this->message_participant_table_name." as pd"
				,"
					 mi_message_id = pd.mp_message_id 
					AND pd.mp_participant_type = 'department'
					AND pd.mp_participant_id IN (".implode(",", $access['department_ids']).")
				"
				,"LEFT"
			);

		$this->set_search_where_clause($filters,$access);

		$result=$this->db->get()->result_array();

		return $result;
	}

	private function set_search_where_clause(&$filters,$access,$count=FALSE)
	{
		if(isset($filters['start_date']))
			$this->db->where("mi_last_activity >=",$filters['start_date']);

		if(isset($filters['end_date']))
			$this->db->where("mi_last_activity <=",$filters['end_date']." 23:59:59");

		if(isset($filters['status']))
		{
			if($filters['status']==="complete")
				$this->db->where("mi_complete",1);
			else
				$this->db->where("mi_complete",0);
		}

		if(isset($filters['verified']))
		{
			$this->db
				->where("mi_sender_type","customer")
				->where("mi_receiver_type","customer");

			if($filters['verified']==="yes")
				$this->db->where("mt_verifier_id !=",0);
			else
				$this->db->where("mt_verifier_id",0);
		}

		if(isset($filters['active']))
		{
			if($filters['active']==="yes")
				$this->db->where("mi_active",1);
			else
				$this->db->where("mi_active",0);
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

		$this->db->where((" ( ".$mess_types." )"));
		//echo $mess_types."<br>";exit();

		$access_where=" 0 ";
		{
			$user_id=0;
			if(($access['type'] == "user")  && isset($access['id']))
				$user_id=$access['id'];

			$department_ids=NULL;
			if(isset($access['department_ids']) && $access['department_ids'])
				$department_ids="(".implode(",",$access['department_ids']).")";

			//for u2u 
			{
				//tq= type query , aq= access query
				$tq = " (mi_sender_type = 'user') AND (mi_receiver_type = 'user') ";
				$aq = " 0 ";
				if($user_id)
				{
					$aq .= " || ( mi_sender_id = $user_id )";
					$aq .= " || ( mi_receiver_id = $user_id )";
					$aq .= " || ( !ISNULL(pu.mp_participant_id) )";
				}
				if($access['op_access']['users'])
					$aq .= " || ( 1 )";
				if($department_ids)
					$aq .= " || ( !ISNULL(pd.mp_participant_id) )";	

				$access_where .= " || ( $tq AND ( $aq ) ) ";
			}

			//for c2c
			{
				$tq = " (mi_sender_type = 'customer') AND (mi_receiver_type = 'customer') ";
				$aq = " 0 ";
				if($user_id)
					$aq .= " || ( !ISNULL(pu.mp_participant_id) )";
				if($access['op_access']['customers'])
					$aq .= " || ( 1 )";
				if($department_ids)
					$aq .= " || ( !ISNULL(pd.mp_participant_id) )";	

				$access_where .= " || ( $tq AND ( $aq ) ) ";	
			}

			//for c2d
			{
				$tq = " (mi_sender_type = 'customer') AND (mi_receiver_type = 'department') ";
				$aq = " 0 ";
				if($user_id)
					$aq .= " || ( !ISNULL(pu.mp_participant_id) )";
				if($department_ids)
				{
					$aq .= " || ( mi_receiver_id IN $department_ids )";	
					$aq .= " || ( !ISNULL(pd.mp_participant_id) )";	
				}

				$access_where .= " || ( $tq AND ( $aq ) ) ";	
			}

			//for d2c
			{
				$tq = " (mi_sender_type = 'department') AND (mi_receiver_type = 'customer') ";
				$aq = " 0 ";
				if($user_id)
					$aq .= " || ( !ISNULL(pu.mp_participant_id) )";
				if($department_ids)
				{
					$aq .= " || ( mi_sender_id IN $department_ids )";	
					$aq .= " || ( !ISNULL(pd.mp_participant_id) )";	
				}

				$access_where .= " || ( $tq AND ( $aq ) ) ";	
			}
		}
		
		$this->db->where((" ( ".$access_where." )"));
		//echo $access_where;
		//bprint_r($access);exit();	

		if(!$count)
		{
			if(isset($filters['order_by']))
				$this->db->order_by($filter['order_by']);
			else
				$this->db->order_by("mi_last_activity DESC");
		}

		if(isset($filters['start']) && isset($filters['length']))
			$this->db->limit((int)$filters['length'],(int)$filters['start']);

		if(!$count)
			$this->db->group_by("mi_message_id");

		return;
	}

	public function add_u2u_message($props)
	{
		$ret=array();

		foreach($props['receiver_ids'] as $rid)
		{
			$mess=array(
				"mi_sender_type"		=>"user"
				,"mi_sender_id"		=>$props['sender_id']
				,"mi_receiver_type"	=>"user"
				,"mi_receiver_id"		=>$rid
				,"mi_subject"			=>$props['subject']
			);

			$mid=$this->add_message($mess);

			$thr=array(
				'mt_message_id'	=> $mid
				,'mt_sender_type'	=> "user"
				,'mt_sender_id'	=> $props['sender_id']
				,'mt_content'		=> $props['content']
				,'mt_attachment'	=> $props['attachment']
			);

			$tid=$this->add_thread($thr);

			$ret[]=$mid;
		}


		return $ret;
	}

	public function add_d2c_message($props)
	{
		$ret=array();

		foreach($props['receiver_ids'] as $rid)
		{
			$mess=array(
				"mi_sender_type"		=>"department"
				,"mi_sender_id"		=>$props['sender_id']
				,"mi_receiver_type"	=>"customer"
				,"mi_receiver_id"		=>$rid
				,"mi_subject"			=>$props['subject']
			);

			$mid=$this->add_message($mess);

			$mess['message_type']="d2c";
			$mess['message_id']=$mid;
			$mess['departement_name']=$this->get_departments()[$props['sender_id']];
			
			$this->load->model("customer_manager_model");
			$this->customer_manager_model->add_customer_log($rid,'MESSAGE_ADD',$mess);

			$thr=array(
				'mt_message_id'	=> $mid
				,'mt_sender_type'	=> "department"	
				,'mt_sender_id'	=> $props['sender_id']
				,'mt_verifier_id' => $props['verifier_id']
				,'mt_content'		=> $props['content']
				,'mt_attachment'	=> $props['attachment']
			);

			$tid=$this->add_thread($thr);

			$thr['mt_thread_id']=$tid;
			$thr['departement_name']=$this->get_departments()[$props['sender_id']];
			$this->customer_manager_model->add_customer_log($rid,'MESSAGE_THREAD_ADD',$thr);

			$ret[]=$mid;
		}


		return $ret;
	}

	public function add_c2c_message(&$props)
	{
		$mess=array(
			"mi_sender_type"		=>"customer"
			,"mi_sender_id"		=>$props['sender_id']
			,"mi_receiver_type"	=>"customer"
			,"mi_receiver_id"		=>$props['receiver_id']
			,"mi_subject"			=>$props['subject']
		);

		$mid=$this->add_message($mess);
		$mess['message_type']="c2c";
		$mess['message_id']=$mid;
		
		$this->load->model("customer_manager_model");
		$this->customer_manager_model->set_customer_event($props['sender_id'],"has_message");
		$this->customer_manager_model->add_customer_log($props['sender_id'],'MESSAGE_ADD',$mess);

		$this->customer_manager_model->set_customer_event($props['receiver_id'],"has_message");
		$this->customer_manager_model->add_customer_log($props['receiver_id'],'MESSAGE_ADD',$mess);

		$thr=array(
			'mt_message_id'	=> $mid
			,'mt_sender_type'	=> "customer"
			,'mt_sender_id'	=> $props['sender_id']
			,'mt_content'		=> $props['content']
		);

		$tid=$this->add_thread($thr);

		$thr['mt_thread_id']=$tid;		
		$this->customer_manager_model->add_customer_log($props['sender_id'],'MESSAGE_THREAD_ADD',$thr);

		return $mid;
	}

	public function add_c2d_message(&$props)
	{
		$mess=array(
			"mi_sender_type"		=>"customer"
			,"mi_sender_id"		=>$props['customer_id']
			,"mi_receiver_type"	=>"department"
			,"mi_receiver_id"		=>$props['department']
			,"mi_subject"			=>$props['subject']
		);

		$mid=$this->add_message($mess);
		$mess['message_type']="c2d";
		$mess['message_id']=$mid;
		$mess['departement_name']=$this->get_departments()[$props['department']];
		
		$this->load->model("customer_manager_model");
		$this->customer_manager_model->set_customer_event($props['customer_id'],"has_message");
		$this->customer_manager_model->add_customer_log($props['customer_id'],'MESSAGE_ADD',$mess);

		$thr=array(
			'mt_message_id'	=> $mid
			,'mt_sender_type'	=> "customer"
			,'mt_sender_id'	=> $props['customer_id']
			,'mt_content'		=> $props['content']
			,'mt_attachment'	=> $props['attachment']
		);

		$tid=$this->add_thread($thr);

		$thr['mt_thread_id']=$tid;		
		$this->customer_manager_model->add_customer_log($props['customer_id'],'MESSAGE_THREAD_ADD',$thr);

		return $mid;
	}

	public function verify_c2c_messages($verifier_id,&$v,&$nv)
	{
		$ret=["v"=>0,"nv"=>0];
		if($v)
		{
			$this->db
				->set("mt_verifier_id",$verifier_id)
				->where("mt_sender_type","customer")
				->where("mt_verifier_id",0)
				->where_in("mt_thread_id",$v)
				->update($this->message_thread_table_name);
			
			$ret['v']=$this->db->affected_rows();
		}

		if($nv)
		{
			$this->db
				->set("mt_verifier_id",0)
				->where("mt_sender_type","customer")
				->where("mt_verifier_id !=",0)
				->where_in("mt_thread_id",$nv)
				->update($this->message_thread_table_name);

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

	public function add_comment($message_id,$message_props,$thread_props)
	{	
		$attachment=NULL;
		if(isset($thread_props['attachment']))
			$attachment=$thread_props['attachment'];

		$current_time=get_current_time();

		$props=array(
			"mt_sender_type"	=> "user"
			,"mt_sender_id"	=> $thread_props['user_id']
			,"mt_timestamp"	=> $current_time
			,"mt_message_id"	=> $message_id
			,"mt_content"		=> $thread_props['content']
			,"mt_attachment"	=> $attachment
		);
		$this->add_thread($props);

		$mprops=array(
			"mi_last_activity"=>$current_time
			,"mi_complete"=>$message_props['complete']
		);
		if(isset($message_props['active']))
			$mprops['mi_active']=(int)$message_props['active'];

		$this->update_message($message_id,$mprops);

		return;
	}

	public function add_customer_reply($message_id,$customer_id,$content,$attachment)
	{
		$current_time=get_current_time();

		$tprops=array(
			"mt_sender_type"	=> "customer"
			,"mt_sender_id"	=> $customer_id
			,"mt_timestamp"	=> $current_time
			,"mt_message_id"	=> $message_id
			,"mt_content"		=> $content
			,"mt_attachment"	=> $attachment
		);

		$tid=$this->add_thread($tprops);
		$tprops['thread_id']=$tid;

		$mprops=array(
			"mi_last_activity"=>$current_time
			,"mi_complete"=>0
		);
		
		$this->update_message($message_id,$mprops);
		
		$this->load->model("customer_manager_model");
		$this->customer_manager_model->set_customer_event($customer_id,"has_message");
		$this->customer_manager_model->add_customer_log($customer_id,'MESSAGE_THREAD_ADD',$tprops);

		return $tid;	
	}

	public function add_reply($message_id,$message_props,$thread_props)
	{
		$attachment=NULL;
		if(isset($thread_props['attachment']))
			$attachment=$thread_props['attachment'];

		$current_time=get_current_time();

		$tprops=array(
			"mt_sender_type"	=> $thread_props['sender_type']
			,"mt_sender_id"	=> $thread_props['sender_id']
			,"mt_timestamp"	=> $current_time
			,"mt_message_id"	=> $message_id
			,"mt_content"		=> $thread_props['content']
			,"mt_attachment"	=> $attachment
		);

		if(isset($thread_props['verifier_id']))
			$tprops['mt_verifier_id']=$thread_props['verifier_id'];

		$this->add_thread($tprops);

		$mprops=array(
			"mi_last_activity"=>$current_time
			,"mi_complete"=>$message_props['complete']
		);
		
		if(isset($message_props['active']))
			$mprops['mi_active']=(int)$message_props['active'];

		$this->update_message($message_id,$mprops);

		return;
	}

	private function update_message($message_id, $props)
	{
		$this->db
			-> set($props)
			-> where("mi_message_id",$message_id)
			-> update($this->message_info_table_name);

		$props['mi_message_id']=$message_id;
		$this->log_manager_model->info("MESSAGE_SET_PROPS",$props);
		
		return;
	}

	private function add_message($props)
	{
		$props['mi_last_activity']=get_current_time();

		$this->db->insert($this->message_info_table_name,$props);
		$id=$this->db->insert_id();
		
		$props['mi_message_id']=$id;
		$this->log_manager_model->info("MESSAGE_ADD",$props);

		return $id;
	}

	private function add_thread(&$props)
	{
		if(!isset($props['mt_timestamp']))
			$props['mt_timestamp']=get_current_time();

		if(isset($props['mt_attachment']) && $props['mt_attachment'])
		{
			$temp_name=$props['mt_attachment']['temp_name'];
			$extension=$props['mt_attachment']['extension'];

			$props['mt_attachment']=get_random_word(8).".".$extension;
		}
		else
			$props['mt_attachment']=NULL;

		$this->db->insert($this->message_thread_table_name,$props);
		$id=$this->db->insert_id();

		if($props['mt_attachment'])
		{
			$path=get_message_thread_attachment_path($props['mt_message_id'],$id,$props['mt_attachment']);
			move_uploaded_file($temp_name, $path);

			$props['mt_attachment']=get_message_thread_attachment_url($props['mt_message_id'],$id,$props['mt_attachment']);
		}

		$props['mt_thread_id']=$id;
		$this->log_manager_model->info("MESSAGE_THREAD_ADD",$props);

		return $id;
	}
}