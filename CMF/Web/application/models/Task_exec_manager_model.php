<?php
class Task_exec_manager_model extends CI_Model
{
	private $task_exec_table="task_exec";
	private $task_statuses=array('changing','complete','canceled');
	private $task_exec_props_for_write=array(
		
	);
	
	public function __construct()
	{
		parent::__construct();
	
		return;
	}

	public function install()
	{
		$table_name=$this->db->dbprefix($this->task_exec_table); 
		$task_statuses_text="'".implode("','",$this->task_statuses)."'";
		
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table_name (
				`te_task_id` int NOT NULL
				,`te_customer_id` int NOT NULL
				,`te_status` enum($task_statuses_text)
				,`te_last_exec_user_id` int
				,`te_last_exec_timestamp` DATETIME
				,`te_last_exec_result` varchar(500)
				,`te_last_exec_result_file_name` varchar(200)
				,`te_exec_count` tinyint
				,PRIMARY KEY (te_task_id,te_customer_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("task_exec","task_exec_manager");
		$this->module_manager_model->add_module_names_from_lang_file("task_exec");
		
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
		$CI->lang->load('admin_task',$lang);		
		
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