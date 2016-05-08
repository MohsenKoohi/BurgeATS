<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Message extends Burge_CMF_Controller {
	function __construct()
	{
		parent::__construct();

		$this->load->model(array("customer_manager_model","message_manager_model"));
		$this->data['customer_logged_in']=$this->customer_manager_model->has_customer_logged_in();
		$this->lang->load('ce_message',$this->selected_lang);		
	}

	public function details($message_id)
	{
		$message_id=(int)$message_id;

		if(!$message_id || !$this->data['customer_logged_in'])
			redirect(get_link("customer_login"));

		$this->data['message_id']=$message_id;
		$customer_id=$this->customer_manager_model->get_logged_customer_id();

		$result=$this->message_manager_model->get_customer_message($message_id,$customer_id);
		if($result)
		{
			$this->data['message_info']=$result['message'];
			$this->data['threads']=$result['threads'];
			$this->data['captcha']=get_captcha();
		}
		else
			$this->data['message_info']=NULL;
		
		$this->data['page_link']=get_customer_message_details_link($message_id);
		$this->data['departments']=$this->message_manager_model->get_departments();
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_customer_message_details_link($message_id,TRUE));

		$this->data['header_title']=$this->lang->line("message")." ".$message_id.$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("message_details");	
	}

	public function message()
	{
		if(!$this->data['customer_logged_in'])
			redirect(get_link("customer_login"));

		$this->data['message']=get_message();
		$this->data['departments']=$this->message_manager_model->get_departments();
		$this->data['page_link']=get_link("customer_message");
		
		$this->set_messages();

		$this->data['lang_pages']=get_lang_pages(get_link("customer_message",TRUE));

		$this->data['header_title']=$this->lang->line("messages").$this->lang->line("header_separator").$this->data['header_title'];

		$this->send_customer_output("message_list");	
	}

	private function set_messages()
	{
		$customer_id=$this->customer_manager_model->get_logged_customer_id();
		$total=$this->message_manager_model->get_customer_total_messages($customer_id);
		//echo $total;

		if($total)
		{
			$per_page=10;
			$total_pages=ceil($total/$per_page);
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");
			if($page>$total_pages)
				$page=$total_pages;

			$start=($page-1)*$per_page;
			$filters=array();
			$filters['start']=$start;
			$filters['length']=$per_page;
			
			$this->data['messages']=$this->message_manager_model->get_customer_messages($customer_id,$filters);
			//bprint_r($this->data['messages']);
			
			$end=$start+sizeof($this->data['messages'])-1;

			$this->data['messages_current_page']=$page;
			$this->data['messages_total_pages']=$total_pages;
			$this->data['messages_total']=$total;
			$this->data['messages_start']=$start+1;
			$this->data['messages_end']=$end+1;		
		}
		else
		{
			$this->data['messages_current_page']=0;
			$this->data['messages_total_pages']=0;
			$this->data['messages_total']=$total;
			$this->data['messages_start']=0;
			$this->data['messages_end']=0;
		}

		return;
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

					$this->message_manager_model->add_c2d_message($props);

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