<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Task_exec extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model("task_manager_model");
		$this->load->model("task_exec_manager_model");

		$this->lang->load('admin_task_exec',$this->selected_lang);

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
		
		$filter=array();
		$model_filter=array();

		if($this->input->get("date"))
		{
			$date=$this->input->get("date");
			$filter['date']=$date;

			$date_splitted=explode("-", $date);
			if(sizeof($date_splitted)==2)
			{
				$end=(int)$date_splitted[0];
				$start=(int)$date_splitted[1];

				$model_filter['end_interval']=$end;
				$model_filter['start_interval']=$start;
			}
			else
			{
				$end=(int)$date;
				$model_filter['end_interval']=$end;
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

		if($this->input->get("user"))
		{
			$user_id=(int)$this->input->get("user");
			$filter['user']=$user_id;
			$model_filter['user_id']=$user_id;
		}

		if($this->input->get("note"))
		{
			$note=$this->input->get("note");
			$filter['note']=$note;
			$model_filter['requires_manager_note']=(int)("yes" === $note);
		}





		$this->data['filter']=$filter;

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
}