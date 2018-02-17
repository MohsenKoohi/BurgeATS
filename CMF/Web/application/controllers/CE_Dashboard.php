<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Dashboard extends Burge_CMF_Controller {
	protected $hit_level=-1;

	function __construct()
	{
		parent::__construct();
	}

	public function index()
	{	

		$this->load->model("customer_manager_model");
		
		if(!$this->customer_manager_model->has_customer_logged_in())
			return redirect(get_link("customer_login"));

		$this->lang->load('ce_dashboard',$this->language->get());

		$info=$this->customer_manager_model->get_logged_customer_info();
		$this->data['customer_name']=$info['customer_name'];

		$this->data['message']=get_message();

		$this->data['lang_pages']=get_lang_pages(get_link("customer_dashboard",TRUE));

		$this->data['header_title']=$this->lang->line("header_title").$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']="";
		$this->data['header_meta_keywords']="";
		$this->data['header_meta_robots']="noindex";
		
		$this->send_customer_output("dashboard");

		return;
	}
}