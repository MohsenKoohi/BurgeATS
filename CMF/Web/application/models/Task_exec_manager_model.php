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

	//this is our scheduler method, 
	//which may be call a scheduler class in next versions
	//and returns the firt $total_count important task should be done by $user_id  user
	//in an array of obejcts with task_id and customer_id indexes
	public function get_tasks($user_id, $total_count)
	{

		//1) we should read all tasks this user can do
		$this->load->model("task_manager_model");

		$user_tasks=$this->task_manager_model->get_user_tasks($user_id);
		if(!$user_tasks)
			return;

		//2) we should find the most important works with the same priority
		$high_priority_tasks=array();
		$hp=$user_tasks[0]['task_priority'];
		for($i=0;$i<sizeof($user_tasks);$i++)
			if($user_tasks[$i]['task_priority'] == $hp)
				$high_priority_tasks[]=$user_tasks[$i];
			else
				break;

		//3) we assume number of customers for each task is more  than $total_count,
		//thus we just return the first priorities.
		//however there is a hole for next version works
		//this assumption can be deleted
		//
		//in this section we specify number of customers for each specified tasks 
		//in previous section
		$tasks=$high_priority_tasks;
		$total_tasks=sizeof($tasks);
		$sum=0;
		$counts=array();
		for($i=0;$i<$total_tasks;$i++)
		{
			$task_count=(int)(($total_count-$sum)/($total_tasks-$i));
			$counts[]=$task_count;
			$sum+=$task_count;
		}

		//4) now we have two array $tasks , $counts
		//we should call classes of each task and specify customers 
		//who the task should be execute for 
		//class of each task should be placed in libraries/tasks folder
		$result_array=array();
		for($i=0;$i<sizeof($tasks);$i++)
		{
			$task=$tasks[$i];
			$count=$counts[$i];

			$class_name=$task['task_class_name'];

			$task_file_name="tasks/".strtolower($class_name);
			$this->load->library($task_file_name,NULL,"temp_task");

			$task_result=$this->temp_task->get_customers($task,$count);

			$result_array=array_merge($result_array,$task_result);
		}

		return $result_array;
	}

}