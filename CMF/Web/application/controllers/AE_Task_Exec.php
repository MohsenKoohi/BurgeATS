<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Task_Exec extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model("task_manager_model");
		$this->load->model("task_exec_manager_model");

		$this->lang->load('ae_task_exec',$this->selected_lang);

		return;
	}

	public function index()
	{	
		$user_id=$this->user->get_id();
		
		$this->data['tasks']=$this->task_exec_manager_model->get_tasks($user_id, 10);
		
		$this->executed_tasks_tab();		
		
		$this->data['lang_pages']=get_lang_pages(get_link("admin_task_exec",TRUE));
		$this->data['header_title']=$this->lang->line("tasks_exec");
		
		$this->send_admin_output("task_exec");

		return;	 
	}

	private function executed_tasks_tab()
	{
		$user_id=$this->user->get_id();

		$this->data['user_tasks']=$this->task_manager_model->get_user_tasks($user_id,FALSE);
		$this->data['users_info']=$this->user_manager_model->get_all_users_info();
		
		$this->data['raw_page_url']=get_link("admin_task_exec");
		$this->data['task_exec_statuses']=$this->task_exec_manager_model->get_task_statuses();
		
		$filter=array();
		$model_filter=array();

		$this->initialize_task_exec_info_filters($filter,$model_filter);

		$total=$this->task_exec_manager_model->get_task_exec_info_total($model_filter);
		if($total)
		{
			$logs_pp=10;
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");

			$start=($page-1)*$logs_pp;
			$model_filter['start']=$start;
			$model_filter['length']=$logs_pp;
			
			$this->data['task_exec_info']=$this->task_exec_manager_model->get_task_exec_info($model_filter);
			
			$end=$start+sizeof($this->data['task_exec_info'])-1;

			$this->data['logs_current_page']=$page;
			$this->data['logs_total_pages']=ceil($total/$logs_pp);
			$this->data['logs_total']=$total;
			$this->data['logs_start']=$start+1;
			$this->data['logs_end']=$end+1;		
		}
		else
		{
			$this->data['logs_current_page']=0;
			$this->data['logs_total_pages']=0;
			$this->data['logs_total']=$total;
			$this->data['logs_start']=0;
			$this->data['logs_end']=0;
		}
			
		$this->data['filter']=$filter;

		return;
	}

	private function initialize_task_exec_info_filters(&$filter, &$model_filter)
	{

		if($this->input->get("date"))
		{
			$date=$this->input->get("date");
			$filter['date']=$date;

			$date_splitted=explode("-", $date);
			if(sizeof($date_splitted)==2)
			{
				$end=(int)$date_splitted[0];
				$start=(int)$date_splitted[1];

				$model_filter['end_date']=$this->get_date_days_before($end)." 24:59:59";
				$model_filter['start_date']=$this->get_date_days_before($start)." 00:00:00";
			}
			else
			{
				$end=(int)$date;
				$model_filter['end_date']=$this->get_date_days_before($end)." 24:59:59";
			}
		}

		if($this->input->get("task"))
		{
			$task_id=(int)$this->input->get("task");;
			$filter['task']=$task_id;
			$model_filter['task_id']=$task_id;
		}

		if($this->input->get("name"))
		{
			$customer_name=$this->input->get("name");
			$filter['name']=$customer_name;
			$model_filter['customer_name']=$customer_name;
		}

		if($this->input->get("status"))
		{
			$result=$this->input->get("status");
			$filter['status']=$result;
			$model_filter['status']=$result;
		}

		if($this->input->get("user"))
		{
			$exec_user_id=(int)$this->input->get("user");
			$filter['user']=$exec_user_id;
			$model_filter['last_exec_user_id']=$exec_user_id;
		}

		if($this->input->get("note"))
		{
			$note=$this->input->get("note");
			$filter['note']=$note;
			$model_filter['last_exec_requires_manager_note']=(int)("yes" === $note);
		}

		return;
	}

	public function get_file($customer_id,$file_name)
	{
		$this->load->model("customer_manager_model");
		$path=$this->customer_manager_model->get_task_exec_file_path($customer_id,$file_name);

		/*
		header('Cache-Control: max-age=3600');
		header('Surrogate-Control: max-age=2592000');
		header('Expires: 21 Jan 2100 0:00:00 GMT');	
		header('Cache-control: max-age='.(60*60*24*365));	
		header('Last-Modified: 0 0 0 0 0');
		header('Pragma :');
		header('Content-Type: image/jpeg');
		*/

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		   header('HTTP/1.1 304 Not Modified');
		   die();
		}
			
		$extension=pathinfo($path, PATHINFO_EXTENSION);
		$this->output->set_content_type($extension);
		$this->output->set_output(file_get_contents($path));

		return;
	}

	private function get_date_days_before($days)
	{
		$df=DATE_FUNCTION;
		return $df("Y-m-d",time()-60*60*24*$days);
	}
}