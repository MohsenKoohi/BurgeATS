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
}