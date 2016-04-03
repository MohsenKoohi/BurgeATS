<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Login extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

	}

	public function login()
	{
		$this->load->model("customer_manager_model");
		
		if($this->customer_manager_model->has_customer_logged_in())
		{
			redirect(get_link("customer_dashboard"));
			return;
		}
		
		$lang=$this->language->get();
		$this->lang->load('ce_login',$lang);

		if($this->input->post())
		{
			$this->lang->load('error',$lang);

			if($this->input->post("email") && $this->input->post("pass"))
			{
				if(verify_captcha($this->input->post("captcha")))
				{
					$pass=$this->input->post("pass");
					$email=$this->input->post("email");
					
					if($this->customer_manager_model->login($email,$pass))
					{
						redirect(get_link("customer_dashboard"));
						return;
					}
					else				
						$message=$this->lang->line("incorrect_fields");
				}
				else
					$message=$this->lang->line("captcha");
			}
			else
				$message=$this->lang->line("fill_all_fields");
		}
	
		$this->data['lang_pages']=get_lang_pages(get_link("customer_login",TRUE));

		if(isset($message))
			$this->data['message']=$message;
		else
			$this->data['message']=get_message();

		$this->data['header_title'].=$this->lang->line("header_title");
		$this->data['header_meta_description'].=$this->lang->line("header_meta_description");
		$this->data['header_meta_keywords'].=$this->lang->line("header_meta_keywords");
		$this->data['header_meta_robots']="noindex";

		$this->data['header_canonical_url']=get_link("home_url");
		
		$this->data['captcha']=get_captcha();

		$this->send_customer_output("login");
		
		return;	 
	}

	public function logout()
	{
		$this->load->model("customer_manager_model");
		$this->customer_manager_model->set_customer_logged_out();
		
		redirect(get_link("home_url"));
		return;		
	}
}