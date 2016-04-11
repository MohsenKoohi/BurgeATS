<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Message extends Burge_CMF_Controller {
	function __construct()
	{
		parent::__construct();

		$this->load->model(array("customer_manager_model","message_manager_model"));
		$this->data['customer_logged_in']=$this->customer_manager_model->has_customer_logged_in();
		$this->lang->load('ce_message',$this->selected_lang);		
	}

	public function message()
	{
		if(!$this->data['customer_logged_in'])
			redirect(get_link("customer_login"));

		echo get_message();
	}

	public function c2d()
	{	
		if($this->input->post())
			return $this->add_new_c2d_message();

		$this->data['message']=get_message();
		$this->data['departments']=$this->message_manager_model->get_departments();
		$this->data['captcha']=get_captcha();
		$this->data['lang_pages']=get_lang_pages(get_link("customer_contact_us",TRUE));

		$this->data['subject']=$this->session->flashdata("message_c2d_subject");
		$this->data['content']=$this->session->flashdata("message_c2d_content");
		
		$this->data['header_meta_robots']="noindex";

		$this->data['header_title']=$this->lang->line("contact_us").$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$this->data['header_title'];
		$this->data['header_meta_keywords']=$this->data['header_title'];

		$this->data['header_canonical_url']=get_link("customer_contact_us");

		$this->send_customer_output("message_c2d");

		return;
	}

	private function add_new_c2d_message()
	{
		if($this->data['customer_logged_in'])
		{
			if(verify_captcha($this->input->post("captcha")))
			{
				$fields=array("department","subject","content");
				$props=array();
				foreach($fields as $field)
					$props[$field]=$this->input->post($field);
				
				if($props['subject']  && $props['department'] && $props['content'] )
				{
					persian_normalize($props);

					$customer_info=$this->customer_manager_model->get_logged_customer_info();
					$props['customer_id']=$customer_info['customer_id'];

					$this->message_manager_model->send_c2d_message($props);

					set_message($this->lang->line("message_sent_successfully"));

					redirect(get_link("customer_messages"));
				}
				else
					set_message($this->lang->line("fill_all_fields"));
			}
			else
				set_message($this->lang->line("captcha_incorrect"));
		}
		else
			set_message($this->lang->line("to_send_message_you_should_login"));

		$this->session->set_flashdata("message_c2d_subject",$this->input->post("subject"));
		$this->session->set_flashdata("message_c2d_content",$this->input->post("content"));

		redirect(get_link("customer_contact_us"));

		return;
	}
}