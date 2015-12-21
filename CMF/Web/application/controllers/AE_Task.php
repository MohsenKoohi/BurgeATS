<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Task extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model("task_manager_model");
		$this->lang->load('ae_task',$this->selected_lang);

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

		$this->data['tasks_info']=$this->task_manager_model->get_all_tasks();
	
		$this->data['lang_pages']=get_lang_pages(get_link("admin_task",TRUE));
		$this->data['header_title']=$this->lang->line("tasks");
		
		$this->send_admin_output("task");

		return;	 
	}

	private function add_task()
	{
		$task_props=array(
			"task_id"		=>	persian_normalize_word($this->input->post("id"))
			,"task_name"		=>	persian_normalize_word($this->input->post("name"))
			,"task_desc"	=>	persian_normalize_word($this->input->post("desc"))
		);

		$result=$this->task_manager_model->add_task($task_props);
		if($result)
			set_message($this->lang->line("task_added_successfully"));
		else
			set_message($this->lang->line("task_number_has_been_used"));

		redirect(get_link("admin_task"));

		return;
	}

	public function task_details($task_id)
	{
		$task_id=(int)$task_id;

		if($this->input->post())
		{
			if($this->input->post('post_type')==="edit_task")
				return $this->edit_task_info($task_id);
		}

		$this->data['potential_users']=$this->access_manager_model->get_users_have_access_to_module("task_exec");
		$this->data['task_info']=$this->task_manager_model->get_task_details($task_id);
		
		$task_users=$this->task_manager_model->get_task_users($task_id);
		$task_users_ids=array();
		$task_managers_ids=array();
		foreach ($task_users as $tu)
		{
			$task_users_ids[]=$tu['user_id'];
			if($tu['is_manager'])
				$task_managers_ids[]=$tu['user_id'];
		}
		$this->data['task_users_ids']=$task_users_ids;
		$this->data['task_managers_ids']=$task_managers_ids;

		$message=get_message();
		if($message)
			$this->data['message']=$message;
	
		$this->data['lang_pages']=get_lang_pages(get_admin_task_details_link($task_id,TRUE));
		$this->data['header_title']=$this->lang->line("task_details");
		
		$this->send_admin_output("task_details");

		return;
	}

	private function edit_task_info($task_id)
	{
		$new_task_id=persian_normalize_word($this->input->post("task_id"));
		$result=$this->task_manager_model->set_task_info($task_id,array(
			"task_id"=>$new_task_id
			,"task_name"=>persian_normalize_word($this->input->post("task_name"))
			,"task_desc"=>persian_normalize_word($this->input->post("task_desc"))
			,"task_class_name"=>persian_normalize_word($this->input->post("task_class_name"))
			,"task_active"=>(($this->input->post("task_active")==="on")?1:0)
			,"task_period"=>(int)$this->input->post("task_period")
			,"task_priority"=>(int)$this->input->post("task_priority")
		));

		if(!$result)
		{
			set_message($this->lang->line("task_number_has_been_used"));
			redirect(get_admin_task_details_link($task_id));

			return;
		}

		$pusers=$this->access_manager_model->get_users_have_access_to_module("task_exec");
		
		$task_users=array();
		$task_managers=array();
		foreach ($pusers as $user)
		{
			$user_id=$user['user_id'];
			$iname="task_user_".$user_id;
			if($this->input->post($iname) === "on")
			{
				$task_users[]=$user_id;
				$iname="task_user_is_manager_".$user_id;
				if($this->input->post($iname) === "on")
					$task_managers[]=$user_id;
			}
		}

		$this->task_manager_model->set_task_users($task_id,$task_users,$task_managers);

		set_message($this->lang->line("task_changed_successfully"));
		
		redirect(get_admin_task_details_link($new_task_id));

		return;		
	}
}