<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Es extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_es',$this->selected_lang);
		$this->load->model("es_manager_model");

	}

	public function index()
	{
		//$this->es_manager_model->send_email_now(1, "aa","bb", "phpbur@gmail.com", "سلام", "خوبی؟‌ خوب نیستی؟");
		$this->es_manager_model->send_sms_now(1, "aa","bb", "123", "خوبی؟‌ خوب نیستی؟");
		//$this->set_footer();

		$this->data['message']=get_message();
		//$this->data['links']=$this->footer_link_manager_model->get_links();

		$this->data['raw_page_url']=get_link("admin_es");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_es",TRUE));
		$this->data['header_title']=$this->lang->line("es");

		$this->send_admin_output("es");

		return;	 
	}

}