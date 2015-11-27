<?php 


class Task_Sample
{
	public function __construct()
	{
		
	}

	//this method recieve two args; $task which is a row of task table in db
	//and count, which specifies max number of customers this task can return
	//
	//return is an array of objects with task_id,task_name,customer_id,customer_name indexes
	//
	//Note that we (with intention to increase performance) don't run another 
	//query to filter customer_ids who the task has been
	//executed for. So, filter them here. When a task for a customer is completed 
	//its status is changed to 'complete', or if it is canceled by the user who executes the 
	//task, the status is changed to 'canceled', other wise its status is NULL or 'chaning'
	public function get_customers($task,$count)
	{
		$df=DATE_FUNCTION;
		$now=$df("Y-m-d H:i:s");

		$task_id=$task['task_id'];

		//a simple query which retreives all agent customers 

		$CI=&get_instance();

		$CI->db->select("*");
		$CI->db->from("customer");
		$CI->db->join("task_exec","customer_id = te_customer_id AND te_task_id = ".$task_id,"left");
		$CI->db->where("customer_type","agent");
		$CI->db->where("( ISNULL(te_status) OR  (te_status = 'changing' AND te_next_exec < '$now') )");
		$CI->db->order_by("customer_id ASC");
		$CI->db->limit($count,0);
		$res=$CI->db->get();

		foreach ($res->result_array() as $row)
			$result[]=array(
				"task_id"			=>$task_id
				,"task_name"		=>$task['task_name']
				,"customer_id"		=>$row['customer_id']
				,"customer_name"	=>$row['customer_name']
			);

		return $result;
	}
}