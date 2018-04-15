<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_News_Letter extends Burge_CMF_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_news_letter',$this->selected_lang);
		$this->load->model("news_letter_manager_model");
	}

	public function index()
	{
		if($this->input->post("post_type")==="add_email")
			return $this->add_email();

		if($this->input->post("post_type")==="remove_email")
			return $this->remove_email();

		$this->data['message']=get_message();

		$this->data['captcha']=get_captcha();

		$this->data['raw_page_url']=get_link("customer_news_letter");
		$this->data['lang_pages']=get_lang_pages(get_link("customer_news_letter",TRUE));
		$this->data['header_title']=$this->lang->line("news_letter").$this->lang->line("header_separator").$this->data['header_title'];;

		$this->send_customer_output("news_letter_email");

		return;	 
	}	

	private function add_email()
	{
		if(verify_captcha($this->input->post("captcha")))
		{
			$fields=array("email");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);

			if($props['email'])
			{
				$this->news_letter_manager_model->add_email($props);

				set_message($this->lang->line('email_added_successfully'));
			}
			else
				set_message($this->lang->line("fill_all_fields"));
		}
		else
			set_message($this->lang->line("captcha_incorrect"));

		if($this->input->post("back_url"))
			return redirect($this->input->post("back_url"));

		return redirect(get_link("customer_news_letter"));
	}

	private function remove_email()
	{
		if(verify_captcha($this->input->post("captcha")))
		{
			$fields=array("email");
			$props=array();
			foreach($fields as $field)
				$props[$field]=$this->input->post($field);

			if($props['email'])
			{
				$this->news_letter_manager_model->remove_email($props);

				set_message($this->lang->line('email_removed_successfully'));
			}
			else
				set_message($this->lang->line("fill_all_fields"));
		}
		else
			set_message($this->lang->line("captcha_incorrect"));
	
		return redirect(get_link("customer_news_letter"));
	}
}