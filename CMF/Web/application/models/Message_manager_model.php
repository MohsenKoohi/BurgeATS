<?php
class Message_manager_model extends CI_Model
{
	private $message_user_table_name="message_user";
	private $message_table_name="message";

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
		$module_table=$this->db->dbprefix($this->message_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $module_table (
				`message_id` BIGINT AUTO_INCREMENT NOT NULL
				,`message_parent_id` BIGINT
				,`message_sender_type` enum('customer','department','user')
				,`message_sender_id` BIGINT
				,`message_timestamp` DATETIME
				,`message_receiver_type` enum('customer','department','user')
				,`message_receiver_id` BIGINT
				,`message_subject` VARCHAR(200)
				,`message_content` TEXT
				,`message_verifier_id` INT DEFAULT 0
				,`message_reply_id` BIGINT DEFAULT 0
				,PRIMARY KEY (message_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$module_table=$this->db->dbprefix($this->message_user_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $module_table (
				`mu_user_id` INT NOT NULL
				,`mu_departments` BIGINT DEFAULT 0
				,`mu_verifier` TINYINT NOT NULL DEFAULT 0 
				,`mu_supervisor` TINYINT NOT NULL DEFAULT 0
				,PRIMARY KEY (mu_user_id)	
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

	public function get_total_messages(&$filters)
	{
		$this->db->select("COUNT(*) as count");
		$this->db->from($this->message_table_name);
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

		if(isset($filters['order_by']))
			$this->db->order_by($filter['order_by']);
		else
			$this->db->order_by("message_id DESC");

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
		$this->log_manager_model->info("MESSAGE_SEND",$mess);

		$this->load->model("customer_manager_model");
		$this->customer_manager_model->add_customer_log($props['customer_id'],'MESSAGE_SEND',$mess);
		$this->customer_manager_model->set_customer_event($props['customer_id'],"has_message");

		return $id;
	}

	private function add_message(&$props)
	{
		$should_set_parent_id=!isset($props['message_parent_id']);

		$props['message_timestamp']=get_current_time();

		$this->db->insert($this->message_table_name,$props);

		$id=$this->db->insert_id();

		$this->db
			->set("message_parent_id",$id)
			->where("message_id",$id)
			->update($this->message_table_name);

		if(isset($props['reply_to_message_id']))
			$this->db
				->set("message_reply_id",$id)
				->where("message_id",$props['reply_to_message_id'])
				->update($this->message_table_name);

		return $id;
	}
}