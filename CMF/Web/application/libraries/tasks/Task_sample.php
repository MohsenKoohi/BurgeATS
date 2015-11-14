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
	public function get_customers($task,$count)
	{

		$task_id=$task['task_id'];


		//a simple query which retreives all agent customers 

		$CI=&get_instance();

		$CI->db->select("*");
		$CI->db->from("customer");
		$CI->db->join("task_exec","customer_id = te_customer_id AND te_task_id = ".$task_id,"left");
		$CI->db->where("customer_type","agent");
		$CI->db->where("( ISNULL(te_status) OR  te_status = 'changing')");
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