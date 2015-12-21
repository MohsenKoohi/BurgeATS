<?php
class Task_manager_model extends CI_Model
{
	private $task_table="task";
	private $task_user_table="task_user";
	private $task_props_for_write=array(
		"task_id","task_name","task_desc","task_class_name"
		,"task_period","task_active","task_priority"
	);
	
	public function __construct()
	{
		parent::__construct();
	
		return;
	}

	public function install()
	{
		$table_name=$this->db->dbprefix($this->task_table); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table_name (
				`task_id` int NOT NULL 
				,`task_name` varchar(100)
				,`task_desc` text
				,`task_class_name` varchar(100)
				,`task_period` int DEFAULT -1
				,`task_priority` int DEFAULT 1
				,`task_active` tinyint DEFAULT 1
				,PRIMARY KEY (task_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$table_name=$this->db->dbprefix($this->task_user_table); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table_name (
				`tu_task_id` int NOT NULL
				,`tu_user_id` int NOT NULL
				,`tu_is_manager` tinyint DEFAULT 0
				,PRIMARY KEY (tu_task_id, tu_user_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("task","task_manager");
		$this->module_manager_model->add_module_names_from_lang_file("task");
		
		return;
	}

	public function get_all_tasks()
	{
		$this->db->from($this->task_table);
		$this->db->order_by("task_id DESC");
		$result=$this->db->get();

		return $result->result_array();
	}

	//returns all fields of a task without its users
	public function get_task_details($task_id)
	{
		$result=$this->db->get_where($this->task_table,array("task_id"=>$task_id));

		return $result->row_array();
	}

	//returns all users of a task
	public function get_task_users($task_id)
	{	
		$this->db->select("task_user.tu_is_manager as is_manager, user_id , user_name , user_code");
		$this->db->from("task_user");
		$this->db->join("user","tu_user_id = user.user_id","left");
		$this->db->where("tu_task_id",$task_id);
		$this->db->order_by("tu_user_id desc");
		$result=$this->db->get();

		return $result->result_array();
	}

	//returns 0 if user can't execute task
	//returns 1 if user can execute task but is not its manager
	//returns 2 if user can execute and also is its manager
	public function check_user_can_execute_task($user_id,$task_id)
	{
		//we may check if task is active 
		//in future

		$result=$this->db->get_where($this->task_user_table
			,array(
				"tu_task_id"=>$task_id
				,"tu_user_id"=>$user_id
			)
		);

		$row=$result->row_array();
		if(!$row)
			return 0;

		if(!$row['tu_is_manager'])
			return 1;
		else
			return 2;
	}

	public function get_task_users_ids($task_id)
	{	
		$this->db->select("task_user.tu_user_id");
		$this->db->from("task_user");
		$this->db->where("tu_task_id",$task_id);
		$result=$this->db->get();

		$ret=array();
		foreach($result->result_array() as $row)
			$ret[]=$row['tu_user_id'];

		return $ret;
	}

	public function get_user_tasks($user_id,$task_should_be_active=TRUE)
	{
		$this->db->from($this->task_table);
		$this->db->join($this->task_user_table,"task_id = tu_task_id");
		$this->db->where("tu_user_id",$user_id);
		if($task_should_be_active)
			$this->db->where("task_active",1);
		$this->db->order_by("task_priority DESC, task_name ASC");
		$result=$this->db->get();

		return $result->result_array();
	}

	public function set_task_users($task_id,$user_ids,$manager_ids)
	{
		$this->log_manager_model->info("TASK_USERS_CHANGE",array(
			"task_id"=>$task_id
			,"new_user_ids"=>implode(' , ', $user_ids)
		));

		$this->db->where("tu_task_id",$task_id);
		$this->db->delete($this->task_user_table);

		if(!$user_ids)
			return;
		
		$batch_arr=array();
		foreach($user_ids as $uid)
			$batch_arr[]=array(
				"tu_task_id"=>$task_id
				,"tu_user_id"=>$uid
				,'tu_is_manager'=>(int)in_array($uid,$manager_ids)
			);
	

		$this->db->insert_batch($this->task_user_table,$batch_arr);

		return;
	}

	public function add_task($props)
	{
		$props_array=select_allowed_elements($props,$this->task_props_for_write);

		$this->db->insert($this->task_table,$props_array);
		
		$props_array['task_id']=$this->db->insert_id();
		$this->log_manager_model->info("TASK_ADD",$props_array);

		return;
	}

	public function set_task_info($task_id,$props)
	{
		$props_array=select_allowed_elements($props,$this->task_props_for_write);
		$this->db->set($props_array);
		$this->db->where("task_id",$task_id);
		$this->db->update($this->task_table);

		$props_array['task_id']=$task_id;
		$this->log_manager_model->info("TASK_INFO_CHANGE",$props_array);

		return;
	}

	public function get_changeable_task_props()
	{
		return $this->task_props_for_write;
	}


	public function uninstall()
	{
	
		return;
	}

	private function get_counts()
	{
		$table_name=$this->db->dbprefix($this->task_table); 

		$result=$this->db->query("
			SELECT 
				(SELECT COUNT(*) FROM $table_name ) as total,
				(SELECT COUNT(*) FROM $table_name WHERE task_active = 1) as active
		");
		
		$row=$result->row_array();
		return $row;
	}

	public function get_dashbord_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('ae_task',$lang);		
		
		$data=array();
		$data['total_text']=$CI->lang->line("total");
		$data['active_text']=$CI->lang->line("active");

		$counts=$this->get_counts();

		$data['total_count']=$counts['total'];
		$data['active_count']=$counts['active'];
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("task_dashboard"),$data,TRUE);
		
		return $ret;		
	}


}