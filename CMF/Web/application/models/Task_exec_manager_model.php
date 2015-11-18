<?php
class Task_exec_manager_model extends CI_Model
{
	private $task_exec_table="task_exec";
	private $task_statuses=array('changing','complete','canceled');
	private $task_exec_props_for_write=array(
		'te_status'
		,'te_next_exec'
		,'te_last_exec_user_id'
		,'te_last_exec_timestamp'
		,'te_last_exec_result'
		,'te_last_exec_result_file_name'
		,'te_last_exec_requires_manager_note'
		,'te_last_exec_manager_note'
	);
	
	public function __construct()
	{
		parent::__construct();
		
		return;
	}

	public function get_task_statuses()
	{
		return $this->task_statuses;
	}

	public function install()
	{
		$table_name=$this->db->dbprefix($this->task_exec_table); 
		$task_statuses_text="'".implode("','",$this->task_statuses)."'";

		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table_name (
				`te_task_id` int NOT NULL
				,`te_customer_id` int NOT NULL
				,`te_status` enum($task_statuses_text) DEFAULT 'changing'
				,`te_next_exec` DATETIME
				,`te_last_exec_user_id` int
				,`te_last_exec_timestamp` DATETIME
				,`te_last_exec_result` varchar(500)
				,`te_last_exec_result_file_name` varchar(200)
				,`te_last_exec_requires_manager_note` tinyint DEFAULT 0
				,`te_last_exec_manager_note` varchar(500) 
				,`te_exec_count` int DEFAULT 1
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

	public function get_task_exec_count($customer_id,$task_id)
	{
		$this->db->select("te_exec_count");
		$result=$this->db->get_where($this->task_exec_table,array(
			"te_customer_id"	=> $customer_id
			,"te_task_id"		=> $task_id
		));

		$row=$result->row_array();
		if($row)
			return $row['te_exec_count'];
		else
			return 0;
	}

	public function update_task_exec_info($customer_id, $task_id, $props_array)
	{
		$props=select_allowed_elements($props_array,$this->task_exec_props_for_write);
		if(isset($props['te_last_exec_result']))
			$props['te_last_exec_result']=persian_normalize_word($props['te_last_exec_result']);

		$this->db->set("te_customer_id",$customer_id);
		$this->db->set("te_task_id",$task_id);
		$this->db->set($props);
		if(!iset($props['te_last_exec_manager_note']))
			$this->db->set("te_last_exec_manager_note",'NULL',FALSE);
		$insert_sql=$this->db->get_compiled_insert($this->task_exec_table);
		
		$this->db->reset_query();
		$this->db->set($props);
		if(!iset($props['te_last_exec_manager_note']))
			$this->db->set("te_last_exec_manager_note",'NULL',FALSE);
		$this->db->set("te_exec_count","te_exec_count + 1",FALSE);
	   $update_sql=$this->db->get_compiled_update($this->task_exec_table);
	   $update_sql=preg_replace('/UPDATE.*?SET/',' ON DUPLICATE KEY UPDATE',$update_sql);
	   $this->db->reset_query();
	   $this->db->query($insert_sql.$update_sql);

	   $props['te_customer_id']=$customer_id;
		$props['te_task_id']=$task_id;

		$this->load->model("customer_manager_model");
		
		unset($props['te_last_exec_user_id']);
		unset($props['te_last_exec_timestamp']);
		
		$props['te_last_exec_result']=nl2br($props['te_last_exec_result']);
		$props=delete_prefix_of_indexes($props,"te_");

		$this->log_manager_model->info("CUSTOMER_TASK_EXEC",$props);
		
		$this->customer_manager_model->add_customer_log($customer_id,'CUSTOMER_TASK_EXEC',$props);

		return;
	}

	//this is our scheduler method, 
	//which may be call$this->db->reset_query() a scheduler class in next versions
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

	public function get_task_exec_info($filter)
	{	
		$this->db->select($this->task_exec_table.".* , user_name,user_code , customer.customer_name, task_name");

		$this->set_task_exec_info_query_fields($filter);
		
		$result=$this->db->get();

		return $result->result_array();
	}

	public function get_task_exec_info_total($filter)
	{
		$this->db->select("COUNT(*) as count");

		$this->set_task_exec_info_query_fields($filter);

		$result=$this->db->get();
		$row=$result->row_array();

		return $row['count'];		
	}

	private function set_task_exec_info_query_fields($filter)
	{
		$this->db->from($this->task_exec_table);
		$this->db->join("user","te_last_exec_user_id = user_id","left");
		$this->db->join("customer","te_customer_id = customer_id","left");
		$this->db->join("task","te_task_id = task_id","left");

		if(isset($filter['task_id']))
			$this->db->where("te_task_id",$filter['task_id']);

		if(isset($filter['customer_id']))
			$this->db->where("te_customer_id",$filter['customer_id']);
		
		if(isset($filter['start_date']))
			$this->db->where("te_last_exec_timestamp >=",$filter['start_date']);

		if(isset($filter['end_date']))
			$this->db->where("te_last_exec_timestamp <=",$filter['end_date']);

		if(isset($filter['customer_name']))
		{
			$filter['customer_name']=persian_normalize_word($filter['customer_name']);
			$this->db->where("`customer_name` LIKE '%".str_replace(" ", "%",$filter['customer_name'])."%'");
		}

		if(isset($filter['last_exec_user_id']))
			$this->db->where("te_last_exec_user_id",$filter['last_exec_user_id']);

		if(isset($filter['last_exec_requires_manager_note']))
			$this->db->where("te_last_exec_requires_manager_note",$filter['last_exec_requires_manager_note']);

		if(isset($filter['start']))
			$this->db->limit($filter['length'],$filter['start']);

		return;	
	}
}