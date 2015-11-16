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
			
		$this->data['lang_pages']=get_lang_pages(get_link("admin_task_exec",TRUE));
		$this->data['header_title']=$this->lang->line("tasks_exec");
		
		$this->send_admin_output("task_exec");

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