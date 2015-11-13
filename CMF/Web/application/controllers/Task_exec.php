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
		return;
		if($this->input->post())
		{
			if($this->input->post("post_type") === "add_task")
				return $this->add_task();
		}

		$message=get_message();
		if($message)
			$this->data['message']=$message;

		$this->data['tasks_info']=$this->task_manager_model->get_all_tasks();
	
		$this->data['lang_pages']=get_lang_pages(get_link("admin_task",TRUE));
		$this->data['header_title']=$this->lang->line("tasks");
		
		$this->send_admin_output("task");

		return;	 
	}
}