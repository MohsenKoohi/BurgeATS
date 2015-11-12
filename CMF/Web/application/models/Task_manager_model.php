<?php
class Task_manager_model extends CI_Model
{
	private $task_table="task";
	private $task_user_table="task_user";
	private $task_props_for_write=array(
		"task_name","task_desc","task_class_name"
		,"task_period","task_active"
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
				`task_id` int NOT NULL AUTO_INCREMENT
				,`task_name` varchar(100)
				,`task_desc` text
				,`task_class_name` varchar(20)
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
		$this->db->order_by("task_id ASC");
		$result=$this->db->get();

		return $result->result_array();
	}

	//returns all fields of a task without its users
	public function get_task_details($task_id)
	{
		$result=$this->db->get_where($this->task_table,array("task_id"=>$task_id));

		return $result->result_array();
	}

	//returns all users of a task
	public function get_task_users($task_id)
	{	
		$this->db->select("task_user.* , user.user_email");
		$this->db->from("task_user");
		$this->db->join("user","tu_user_id = user.user_id","left");
		$this->db->where("tu_task_id",$task_id);
		$result=$this->db->get();

		return $result->result_array();
	}

	public function add_task($props)
	{
		$props_array=select_allowed_elements($props,$this->task_props_for_write);

		$this->db->insert($this->task_table,$props_array);

		$this->log_manager_model->info("TASK_ADD",$props_array);

		return;
	}

	public function uninstall()
	{
	
		return;
	}

	public function get_dashbord_info()
	{
		return "";
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('admin_hit_counter',$lang);		
		
		$data=array();
		$data['month_text']=$CI->lang->line("monthly_visit");
		$data['year_text']=$CI->lang->line("yearly_visit");
		$data['total_text']=$CI->lang->line("total_visit");

		$counts=$this->get_all_counts();
		$data['total_count']=$counts[0]['total_count'];
		$data['year_count']=$counts[0]['year_count'];
		$data['month_count']=$counts[0]['month_count'];
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("hit_counter_dashboard"),$data,TRUE);
		
		return $ret;		
	}


}