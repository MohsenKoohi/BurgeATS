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

		$this->set_messages();

		$this->data['lang_pages']=get_lang_pages(get_link("admin_message",TRUE));
		$this->data['header_title']=$this->lang->line("messages");

		$this->send_admin_output("message");

		return;	 
	}	

	private function set_messages()
	{
		$filters=array();

		$this->data['raw_page_url']=get_link("admin_message");
		
		$this->initialize_filters($filters);

		$total=$this->message_manager_model->get_total_messages($filters);
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
			$filters['start']=$start;
			$filters['length']=$per_page;
			
			$this->data['messages']=$this->message_manager_model->get_messages($filters);
			
			$end=$start+sizeof($this->data['messages'])-1;

			unset($filters['start']);
			unset($filters['length']);

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
		
		unset($filters['message_types']);
		$this->data['filters']=$filters;

		return;
	}

	private function initialize_filters(&$filters)
	{
		$fields=array(
			"start_date","end_date"
			,"response_status","verification_status"
			,"receiver_type","sender_type"
			,"sender_department","sender_user","sender_customer"
			,"receiver_department","receiver_user","receiver_customer"
		);

		foreach($fields as $field)
		{
			$filters[$field]=$this->input->get($field);
			$filters[$field]=persian_normalize($filters[$field]);		
		}
		
		$op_access=$this->data['op_access'];
		$filters['message_types']=array();

		if(($filters['sender_type']!=="department") && 
			($filters['sender_type']!=="customer")   &&
			($filters['receiver_type']!=="department") && 
			($filters['receiver_type']!=="customer"))
				$this->set_user_message_types($op_access,$filters);

		if(($filters['sender_type']!=="department") && 
			($filters['sender_type']!=="user") &&
			($filters['sender_type']!=="me") &&
			($filters['receiver_type']!=="department") && 
			($filters['receiver_type']!=="user") &&
			($filters['receiver_type']!=="me")
			)
				$this->set_customer_message_types($op_access,$filters);
		
		if(($filters['receiver_type']!=="user") && 
			($filters['sender_type']!=="user") &&
			($filters['receiver_type']!=="me") && 
			($filters['sender_type']!=="me") &&
			!(($filters['receiver_type']==="customer") && ($filters['sender_type']==="customer"))
			)
				$this->set_departments_message_types($op_access,$filters);

		//bprint_r($op_access);
		bprint_r($filters['message_types']);

	}

	private function set_customer_message_types(&$op_access, &$filters)
	{
		if(!$op_access['customers'])
			return;

		$mess=array();
		$mess['message_sender_type']="customer";
		$mess['message_receiver_type']="customer";
		$this->set_mess_sr_type("sender","customer",$mess,$filters);
		$this->set_mess_sr_type("receiver","customer",$mess,$filters);

		$filters['message_types'][]=$mess;

		return;
	}

	private function set_departments_message_types(&$op_access, &$filters)
	{
		if(!$op_access['departments'])
			return;

		$departments=$this->message_manager_model->get_departments();
		$user_departments=array();
		foreach($departments as $index => $name)
			if($op_access['departments'][$name])
				$user_departments[]=$index;
		unset($departments);

		if($filters['receiver_type']!=="customer")
		{
			$mess=array();
			$mess['message_sender_type']="customer";
			$mess['message_receiver_type']="departments";
			$this->set_mess_sr_type("sender","customer",$mess,$filters);
			$this->set_mess_sr_type("receiver","department",$mess,$filters,$user_departments);
			$filters['message_types'][]=$mess;
		}

		if($filters['sender_type']!=="customer")
		{
			$mess=array();
			$mess['message_receiver_type']="customer";
			$mess['message_sender_type']="departments";
			$this->set_mess_sr_type("receiver","customer",$mess,$filters);
			$this->set_mess_sr_type("sender","department",$mess,$filters,$user_departments);
			$filters['message_types'][]=$mess;
		}

		return;
	}

	private function set_user_message_types(&$op_access, &$filters)
	{
		$user_id=$this->user_manager_model->get_user_info()->get_id();

		if(!$op_access['users'])
		{	
			//this user has no access to other users messages

			if(($filters['receiver_user']) ||
				($filters['sender_type']==="me") ||
				(!$filters['sender_type'] && !$filters['receiver_type'])
				)
			{
				$mess=array();
				$mess['message_sender_type']="user";
				$mess['message_receiver_type']="user";
				$mess['message_sender_id']=$user_id;
				$this->set_mess_sr_type("receiver","user",$mess,$filters);
				$filters['message_types'][]=$mess;
			}

			if(($filters['sender_user']) ||
				($filters['receiver_type']==="me") ||
				(!$filters['sender_type'] && !$filters['receiver_type'])
				)
			{
				$mess=array();
				$mess['message_sender_type']="user";
				$mess['message_receiver_type']="user";
				$mess['message_receiver_id']=$user_id;
				$this->set_mess_sr_type("sender","user",$mess,$filters);
				$filters['message_types'][]=$mess;			
			}
		}
		else
		{
			$mess=array();
			$mess['message_sender_type']="user";
			$mess['message_receiver_type']="user";
			$this->set_mess_sr_type("sender","user",$mess,$filters);
			$this->set_mess_sr_type("receiver","user",$mess,$filters);
			$filters['message_types'][]=$mess;
		}

		return;
	}

	private function set_mess_sr_type($sr,$type,&$mess,&$filters,$departments=array())
	{
		if($filters[$sr.'_'.$type])
		{
			if((int)$filters[$sr.'_'.$type])
				$mess['message_'.$sr.'_id']=(int)$filters[$sr.'_'.$type];
			else
				$mess[$sr.'.'.$type.'_name']=$filters[$sr.'_'.$type];
		}
		else
			if($type==="department")
				$mess[$sr."_".$type."_in"]=$departments;

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