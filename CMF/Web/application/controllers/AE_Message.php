<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Message extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_message',$this->selected_lang);
		$this->load->model("message_manager_model");
	}

	public function index()
	{
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_link("admin_post",TRUE));
		$this->data['header_title']=$this->lang->line("posts");

		$this->send_admin_output("post");

		return;	 
	}	

	public function access()
	{
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_link("message_access",TRUE));
		$this->data['header_title']=$this->lang->line("message_access");

		$this->send_admin_output("message_access");
	}
}