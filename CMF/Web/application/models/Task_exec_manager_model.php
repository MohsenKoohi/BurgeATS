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

	public function get_counts()
	{
		$df=DATE_FUNCTION;
		list($y,$m,$d)=explode("/", $df("Y/m/d"));

		$year_start="$y-01-01 00:00:00";
		$month_start="$y-$m-01 00:00:00";
		$day_start="$y-$m-$d 00:00:00";

		$tb=$this->db->dbprefix($this->task_exec_table); 

		$result=$this->db->query("
			SELECT 
				 (SELECT COUNT(*) FROM $tb WHERE te_last_exec_timestamp >= '$year_start') as year_count
				,(SELECT COUNT(*) FROM $tb WHERE te_last_exec_timestamp >= '$month_start') as month_count
				,(SELECT COUNT(*) FROM $tb WHERE te_last_exec_timestamp >= '$day_start') as day_count
		");

		$row=$result->row_array();

		return $row;
	}

	public function get_dashbord_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('admin_task_exec',$lang);		
		
		$data=array();
		$data['this_year_text']=$CI->lang->line("this_year");
		$data['this_month_text']=$CI->lang->line("this_month");
		$data['today_text']=$CI->lang->line("today");

		$counts=$this->get_counts();

		$data['year_count']=$counts['year_count'];
		$data['month_count']=$counts['month_count'];
		$data['day_count']=$counts['day_count'];
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("task_exec_dashboard"),$data,TRUE);
		
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

	public function set_manager_note($customer_id, $task_id, $props_array)
	{
		$props=select_allowed_elements($props_array,array("te_status","te_last_exec_manager_note","te_next_exec"));
		$props['te_last_exec_manager_note']=persian_normalize_word($props['te_last_exec_manager_note']);

		$this->db->set($props);
		$this->db->where(array(
			"te_customer_id"=>$customer_id
			,"te_task_id"=>$task_id
		));
	   $this->db->update($this->task_exec_table);

	   $props['te_customer_id']=$customer_id;
		$props['te_task_id']=$task_id;

		$this->load->model("customer_manager_model");
		
		$props['te_last_exec_manager_note']=nl2br($props['te_last_exec_manager_note']);
		$props=delete_prefix_of_indexes($props,"te_");

		$this->log_manager_model->info("CUSTOMER_TASK_MANAGER_NOTE",$props);
		
		unset($props['customer_id']);
		$this->customer_manager_model->add_customer_log($customer_id,'CUSTOMER_TASK_MANAGER_NOTE',$props);

		return;
	}


	public function update_task_exec_info($customer_id, $task_id, $props_array)
	{
		$props=select_allowed_elements($props_array,$this->task_exec_props_for_write);
		if(isset($props['te_last_exec_result']))
			$props['te_last_exec_result']=persian_normalize_word($props['te_last_exec_result']);

		$this->db->set("te_customer_id",$customer_id);
		$this->db->set("te_task_id",$task_id);
		$this->db->set($props);
		if(!isset($props['te_last_exec_manager_note']))
			$this->db->set("te_last_exec_manager_note",'NULL',FALSE);
		$insert_sql=$this->db->get_compiled_insert($this->task_exec_table);
		
		$this->db->reset_query();
		$this->db->set($props);
		if(!isset($props['te_last_exec_manager_note']))
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

		unset($props['customer_id']);
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

		//we should call classes of each task and specify customers 
		//who the task should be execute for 
		//class of each task should be placed in libraries/tasks folder
		$result_array=array();
		$remained_count=$total_count;

		for($i=0;($i<sizeof($user_tasks)) && ($remained_count>0);$i++)
		{
			$task=$user_tasks[$i];

			$class_name=strtolower($task['task_class_name']);

			$task_file_name="tasks/".$class_name;
			$this->load->library($task_file_name,NULL);
			
			$task_result=$this->$class_name->get_customers($task,$remained_count);

			$remained_count-=sizeof($task_result);

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