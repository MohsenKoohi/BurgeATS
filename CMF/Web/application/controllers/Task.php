<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Task extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model("task_manager_model");
		$this->lang->load('admin_task',$this->selected_lang);

		return;
	}

	public function index()
	{		
		if($this->input->post())
		{
			if($this->input->post("post_type") === "add_task")
				return $this->add_task();
		}

		$message=get_message();
		if($message)
			$this->data['message']=$message;

		//$this->data['tasks_info']=$this->task_manager_model->get_all_tasks();
	
		$this->data['lang_pages']=get_lang_pages(get_link("admin_task",TRUE));
		$this->data['header_title']=$this->lang->line("tasks");
		
		$this->send_admin_output("task");

		return;	 
	}

	private function add_task()
	{
		$task_props=array(
			"task_name"	=>	$this->input->post("name")
			,"task_desc"	=>	$this->input->post("desc")
		);

		$this->task_manager_model->add_task($task_props);

		set_message($this->lang->line("task_added_successfully"));

		redirect(get_link("admin_task"));
	}
}