<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Login extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model("customer_manager_model");
		$this->lang->load('ce_login',$this->selected_lang);
	}

	public function login()
	{
		$backurl=$this->session->userdata("backurl");
		if(!$backurl)
			$backurl=get_link("customer_dashboard");

		if($this->customer_manager_model->has_customer_logged_in())
		{	
			$this->session->unset_userdata("backurl");
			return redirect($backurl);
		}		

		if($this->input->post())
		{
			$this->lang->load('error',$this->selected_lang);

			if($this->input->post("email") && $this->input->post("pass"))
			{
				if(verify_captcha($this->input->post("captcha")))
				{
					$pass=$this->input->post("pass");
					$email=$this->input->post("email");
					
					if($this->customer_manager_model->login($email,$pass))
					{
						$this->session->unset_userdata("backurl");
						return redirect($backurl);
					}
					else				
						$message=$this->lang->line("incorrect_fields");
				}
				else
					$message=$this->lang->line("captcha");
			}
			else
				$message=$this->lang->line("please_fill_all_fields");

			set_message($message);
			return redirect(get_link("customer_login"));
		}
	
		$this->data['lang_pages']=get_lang_pages(get_link("customer_login",TRUE));

		if(isset($message))
			$this->data['message']=$message;
		else
			$this->data['message']=get_message();

		$this->data['header_title']=$this->lang->line("header_title").$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$this->lang->line("header_meta_description");
		$this->data['header_meta_keywords'].=",".$this->lang->line("header_meta_keywords");
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

	public function forgotten_password()
	{
		if($this->input->post())
		{
			if($this->input->post("email"))
			{
				$this->load->library('form_validation');
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
				
				if($this->form_validation->run())
				{
					if(verify_captcha($this->input->post("captcha")))
					{
						$email=$this->input->post("email");
						$pass=$this->customer_manager_model->set_new_password($email);

						if($pass!==FALSE)
						{
							$this->lang->load('email_lang',$this->selected_lang);		
						
							$subject=$this->lang->line("new_password_email_subject");
							$subject=$subject.$this->lang->line("header_separator").$this->lang->line("main_name");
							$content=str_replace(
								array('$pass')
								,array($pass)
								,$this->lang->line("new_password_email_content")
							);
							
							$message=str_replace(
								array('$content','$slogan','$response_to'),
								array($content,$this->lang->line("slogan"),"")
								,$this->lang->line("email_template")
							);								

							burge_cmf_send_mail($email,$subject,$message);
						}				
						
						$message=$this->lang->line("new_password_sent_to_your_email");
					}
					else
						$message=$this->lang->line("wrong_captcha");
				}
				else
					$message=$this->lang->line("invalid_email");
			}
			else
				$message=$this->lang->line("please_fill_all_fields");
		}	

		set_message($message);

		redirect(get_link("customer_login"));

		return;				 
	}

	public function signup()
	{				
		
		$backurl=$this->session->userdata("backurl");
		if(!$backurl)
			$backurl=get_link("customer_dashboard");

		if($this->customer_manager_model->has_customer_logged_in())
		{	
			$this->session->unset_userdata("backurl");
			set_message($this->data['message']);
			redirect($backurl);
			return;
		}

		if($this->input->post())
		{
			if($this->input->post("email"))
			{
				if(verify_captcha($this->input->post("captcha")))
				{
					$email=$this->input->post("email");
					
					$result=$this->customer_manager_model->add_customer(
						array(
							"customer_email"=>$email
						)
						,"registerd in customer env"
						,TRUE
					);

					if($result)
					{
						set_message($this->lang->line("regiestered_successfully"));
						
						$this->session->unset_userdata("backurl");						
						redirect($backurl);						
						return;
					}
					
					set_message($this->lang->line("repeated_email"));

					redirect(get_link("customer_login"));
					return;
				}
				else
					$message=$this->lang->line("wrong_captcha");
			}
			else
				$message=$this->lang->line("please_fill_all_fields");

			set_message($message);

			return redirect(get_link("customer_signup"));
		}	

		$this->data['captcha']=get_captcha(rand(4,5));

		$this->data['lang_pages']=get_lang_pages(get_link("customer_signup",TRUE));

		$this->data['message']=get_message();

		$this->data['header_title']=$this->lang->line("signup").$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$this->lang->line("header_meta_description");
		$this->data['header_meta_keywords'].=",".$this->lang->line("header_meta_keywords");
		$this->data['header_meta_robots']="noindex";
		
		$this->send_customer_output("signup");

		return;				 
	}

}