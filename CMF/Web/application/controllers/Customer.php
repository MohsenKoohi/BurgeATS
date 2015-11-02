<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customer extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model("customer_manager_model");
	}

	public function index()
	{

		$this->lang->load('admin_customer',$this->selected_lang);
		
		if($this->input->post())
		{
			$this->lang->load('error',$this->selected_lang);

			if("add_customer" === $this->input->post("post_type"))
				$this->add_customer();
		}
		//$this->data['users_info']=$this->user_manager_model->get_all_users_info();
	
		$this->data['lang_pages']=get_lang_pages(get_link("admin_customer",TRUE));
		$this->data['header_title']=$this->lang->line("customers");
		$this->data['customer_types']=$this->customer_manager_model->get_customer_types();

		$this->send_admin_output("customer");

		return;	 
	}

	private function add_customer()
	{
		$customer_name=$this->input->post("customer_name");
		$customer_type=$this->input->post("customer_type");
		$desc=$this->input->post("desc");

		if(!$customer_type || !$customer_name)
			$this->data['message']=$this->lang->line("fill_all_fields");
		else
		{
			$res=$this->customer_manager_model->add_customer($customer_name,$customer_type);
			if($res)
				$this->data['message']=$this->lang->line("added_successfully");
		}

		return;
	}

	private function modify_users()
	{
		$res=FALSE;
		$users=$this->user_manager_model->get_all_users_info();
		foreach ($users as $user)
		{
			$uid=$user['user_id'];

			//check if user has been deleted
			$delete_string="delete_user_id_".$uid;
			$post_delete=$this->input->post($delete_string);
			if($post_delete==="on")
			{
				$this->user_manager_model->delete_user($uid,$user['user_email']);
				$res=TRUE;

				continue;
			}

			//check if password has been changed
			$pass_string="pass_user_id_".$uid;
			$post_pass=$this->input->post($pass_string);
			$post_pass=trim($post_pass);
			if($post_pass)
			{
				$this->user_manager_model->change_user_pass($user['user_email'],$post_pass);
				$res=TRUE;		
			}
			
		}

		if($res)
			$this->data['message']=$this->lang->line("modfied_successfully");
	}
}