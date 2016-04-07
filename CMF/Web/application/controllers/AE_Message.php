<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Message extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_message',$this->selected_lang);
		$this->load->model(array("user_manager_model","message_manager_model"));
	}

	public function index()
	{
		$this->data['op_access']=$this->message_manager_model->get_operations_access();

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_link("admin_message",TRUE));
		$this->data['header_title']=$this->lang->line("messages");

		$this->send_admin_output("message");

		return;	 
	}	

	public function access($user_id=0)
	{
		$user_id=(int)$user_id;

		if($this->input->post("post_type")==="set_access")
			return $this->set_access($user_id);

		$this->data['users']=$this->user_manager_model->get_all_users_info();
		$this->data['user_id']=$user_id;

		$this->data['departments']=$this->message_manager_model->get_departments();

		if($user_id)
			$this->data['message_access']=$this->message_manager_model->get_user_access($user_id);

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_link("admin_message_access",TRUE));
		$this->data['header_title']=$this->lang->line("message_access");

		$this->send_admin_output("message_access");
	}

	private function set_access($user_id)
	{
		$props=array();
		$props['supervisor']=($this->input->post("supervisor")==="on");
		$props['verifier']=($this->input->post("verifier")==="on");
		$props['departments']=array();

		foreach($this->message_manager_model->get_departments() as $dep)
			$props['departments'][$dep]=($this->input->post($dep)==="on");

		$this->message_manager_model->set_user_access($user_id,$props);

		set_message($this->lang->line("user_access_set_successfully"));

		return redirect(get_admin_message_access_user_link($user_id));
	}
}