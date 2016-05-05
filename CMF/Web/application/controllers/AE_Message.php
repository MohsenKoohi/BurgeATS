<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Message extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_message',$this->selected_lang);
		$this->load->model(array("user_manager_model","message_manager_model"));
	}

	public function new_message()
	{
		$op_access=$this->message_manager_model->get_operations_access();
		$this->data['op_access']=$op_access;

		if($op_access['customers'] && $this->input->get("customer_ids"))
		{
			$this->load->model("customer_manager_model");
			$res=$this->customer_manager_model->get_customers(array(
				"id"=>explode(",",$this->input->get("customer_ids"))
				)
			);

			$receivers_ids=array();
			foreach($res as $row)
				$receivers_ids[$row['customer_id']]=$row['customer_name'];

			$this->data['receiver_type']="customer";
			$this->data['receivers_ids']=$receivers_ids;
		}
		else
		{
			$this->data['receiver_type']="";
			$this->data['receivers_ids']=array();
		}

		
		$user_info=$this->user_manager_model->get_user_info();
		$this->data['sender_user_id']=$user_info->get_id();
		$this->data['sender_user_name']=$user_info->get_code()." - ".$user_info->get_name();

		$this->data['sender_departments']=array();
		foreach($op_access['departments'] as $name => $id)
			if($id)
				$this->data['sender_departments'][$id]=$name;

		if($this->input->post("post_type")==="add_new_message")
			return $this->add_new_message();

		$this->data['users_search_url']=get_link("admin_user_search");
		$this->data['customers_search_url']=get_link("admin_customer_search");

		$this->data['message']=get_message();
		$this->data['departments']=$this->message_manager_model->get_departments();
		$this->data['lang_pages']=get_lang_pages(get_link("admin_message_new",TRUE));
		$this->data['header_title']=$this->lang->line("add_new_message");

		$this->send_admin_output("message_new");

		return;	 
	}

	private function add_new_message()
	{
		$rt=$this->input->post("receiver_type");
		$rids=explode(",",$this->input->post("receivers_ids"));
		$subject=$this->input->post("subject");
		$content=$this->input->post("content");

		if(($rt === "user") && $rids)
		{
			$props=array(
				"sender_id"=>$this->data['sender_user_id']
				,"receiver_ids"=>$rids
				,"subject"=>$subject
				,"content"=>$content
			);

			$this->message_manager_model->add_u2u_message($props);
		}

		$sender_department=$this->input->post("sender_department");
		if(($rt === "customer") && $rids)
			if($sender_department && isset($this->data['sender_departments'][$sender_department]))
			{
				$props=array(
					"verifier_id"=>$this->data['sender_user_id']
					,"sender_id"=>$sender_department
					,"receiver_ids"=>$rids
					,"subject"=>$subject
					,"content"=>$content
				);

				$this->message_manager_model->add_d2c_message($props);
			}

		set_message($this->lang->line("message_added_successfully"));

		return redirect(get_link("admin_message"));		
	}


	public function search_departments($name)
	{
		$max_count=5;

		$deps=$this->message_manager_model->get_departments();
		$results=array();
		$name=urldecode($name);
		$name=persian_normalize($name);
		if(!$name)
			$name=" ";
		$pattern="/.*".preg_replace("/\s+/", ".", trim($name)).".*/";

		foreach ($deps as $id => $name)
		{
			$dep_name=$this->lang->line("department_".$name);
			if(preg_match($pattern, $dep_name))
				$results[]=array(
					"id"=>$id
					,"name"=>$dep_name
				);

			if(sizeof($results)>=$max_count)
				break;
		}

		$this->output->set_content_type('application/json');
    	$this->output->set_output(json_encode($results));

    	return;
	}

	public function message($message_id)
	{
		$message_id=(int)$message_id;
				
		$ret=$this->message_manager_model->get_admin_message($message_id);

		//bprint_r($ret['access']['added_departments']);
		//bprint_r($ret['access']['added_users']);
		
		if($ret)
		{
			if($this->input->post("post_type") === "add_reply_comment")
				return $this->add_reply_comment($message_id,$ret);

			if($this->input->post("post_type") === "set_participants")
				return $this->set_participants($message_id);

			$this->data['access']=$ret['access'];
			$this->data['message_info']=$ret['message'];
			$this->data['threads']=$ret['threads'];

			if($this->data['message_info'])
				$message_id=$this->data['message_info']['mi_message_id'];
			
			$this->data['departments']=$this->message_manager_model->get_departments();

			$this->data['departments_search_url']=get_link("admin_message_search_departments");
			$this->data['users_search_url']=get_link("admin_user_search");
			
		}
		else
		{
			$this->data['message_info']=NULL;	
		}

		$this->data['message_id']=$message_id;
		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_message_info_link($message_id,TRUE));
		$this->data['header_title']=$this->lang->line("message")." ".$message_id;

		$this->send_admin_output("message_info");
	}

	private function set_participants($message_id)
	{
		if($this->input->post("departments"))
			$deps=explode(",", $this->input->post("departments"));
		else
			$deps=array();

		if($this->input->post("users"))
			$users=explode(",", $this->input->post("users"));
		else
			$users=array();

		$this->message_manager_model->set_participants($message_id,$deps,$users);

		set_message($this->lang->line("participants_saved_successfully"));

		return redirect(get_admin_message_info_link($message_id));
	}

	private function add_reply_comment($message_id,$mess)
	{
		if($this->input->post("response_type") === "comment")
		{
			$thread_props=array(
				"content"=>$this->input->post("content")
				,"user_id"=>$this->user_manager_model->get_user_info()->get_id()
			);

			$message_props=array(
				"complete"=>(int)$this->input->post("complete")
			);
			if($mess['access']['supervisor'])
				$message_props['active']=($this->input->post("active")==="on");

			$this->message_manager_model->add_comment($message_id,$message_props,$thread_props);

			set_message($this->lang->line("your_comment_added_successfully"));
		}
		
		if($this->input->post("response_type") === "reply")
		{
			$thread_props=array(
				"content"=>$this->input->post("content")
			);

			$user_id=$this->user_manager_model->get_user_info()->get_id();

			$st=$mess['message']['mi_sender_type'];
			$rt=$mess['message']['mi_receiver_type'];

			if( (($st==="customer") && ($rt==="department")) ||
				(($st==="department") && ($rt==="customer")) )
			{
				$thread_props['sender_type']="department";
				if($st==="department")
					$thread_props['sender_id']=$mess['message']['mi_sender_id'];
				else
					$thread_props['sender_id']=$mess['message']['mi_receiver_id'];

				$thread_props['verifier_id']=$user_id;
			}

			if(($st==="user") && ($rt==="user"))
			{
				$thread_props['sender_type']="user";
				$thread_props['sender_id']=$user_id;
			}

			if(($st==="customer") && ($rt==="customer"))
			{
				$thread_props['sender_type']="department";
				$thread_props['sender_id']=$this->message_manager_model->get_c2c_response_department_id();
				$thread_props['verifier_id']=$user_id;
			}			

			$message_props=array(
				"complete"=>(int)$this->input->post("complete")
			);

			if($mess['access']['supervisor'])
				$message_props['active']=($this->input->post("active")==="on");

			$this->message_manager_model->add_reply($message_id,$message_props,$thread_props);

			set_message($this->lang->line("your_reply_added_successfully"));
		}

		return redirect(get_admin_message_info_link($message_id));
	}

	public function index()
	{
		$this->data['op_access']=$this->message_manager_model->get_operations_access();

		if($this->data['op_access']['verifier'])
			if($this->input->post("post_type")==="verify_c2c_messages")
				return $this->verify_messages();

		$this->set_messages();

		$this->data['message']=get_message();
		$this->data['departments']=$this->message_manager_model->get_departments();
		$this->data['lang_pages']=get_lang_pages(get_link("admin_message",TRUE));
		$this->data['header_title']=$this->lang->line("messages");

		$this->send_admin_output("message_list");

		return;	 
	}

	private function verify_messages()
	{
		$user_id=$this->user_manager_model->get_user_info()->get_id();
		$v=explode(",",$this->input->post("verified_messages"));
		$nv=explode(",",$this->input->post("not_verified_messages"));

		$result=$this->message_manager_model->verify_c2c_messages($user_id,$v,$nv);

		set_message($this->lang->line("verifications_saved_successfully"));

		return redirect($this->input->post("redirect_link"));
	}

	private function set_messages()
	{

		$op_access=$this->data['op_access'];
		$departments=$this->message_manager_model->get_departments();
		$user_departments=array();
		foreach($departments as $id => $name)
			if($op_access['departments'][$name])
				$user_departments[]=$id;
		unset($departments);

		$access=array(
			"type"=>"user"
			,"id"=>$this->user_manager_model->get_user_info()->get_id()
			,"op_access"=>$op_access
			,"department_ids"=>$user_departments
		);

		$filters=array();

		$this->data['raw_page_url']=get_link("admin_message");
		
		$this->initialize_filters($filters,$access);
		
		$total=$this->message_manager_model->get_total_messages($filters,$access);
		if($total)
		{
			$per_page=20;
			$total_pages=ceil($total/$per_page);
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");
			if($page>$total_pages)
				$page=$total_pages;

			$start=($page-1)*$per_page;
			$filters['start']=$start;
			$filters['length']=$per_page;
			
			$this->data['messages']=$this->message_manager_model->get_messages($filters,$access);
			$this->process_messages_for_view();
			
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

	private function process_messages_for_view()
	{
		//bprint_r($this->data['messages']);
	}

	private function initialize_filters(&$filters,$access)
	{
		$fields=array(
			"start_date","end_date"
			,"status","verified","active"
			,"receiver_type","sender_type"
			,"sender_department","sender_user","sender_customer"
			,"receiver_department","receiver_user","receiver_customer"
		);

		foreach($fields as $field)
		{
			$filters[$field]=$this->input->get($field);
			$filters[$field]=persian_normalize($filters[$field]);		
		}
		
		$op_access=$access['op_access'];

		if(!$op_access['users'])
			$filters['active']="yes";

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
		
		if(($filters['sender_type']!=="user") &&			
			($filters['sender_type']!=="me") &&
			($filters['receiver_type']!=="user") && 
			($filters['receiver_type']!=="me") && 
			!(($filters['receiver_type']==="customer") && ($filters['sender_type']==="customer"))
			)
				$this->set_departments_message_types($op_access,$filters);

		//bprint_r($op_access);
		//bprint_r($filters['message_types']);

		return;
	}

	private function set_customer_message_types(&$op_access, &$filters)
	{
		$mess=array();
		$mess['mi_sender_type']="customer";
		$mess['mi_receiver_type']="customer";
		$this->set_mess_sr_type("sender","customer",$mess,$filters);
		$this->set_mess_sr_type("receiver","customer",$mess,$filters);
	
		$filters['message_types'][]=$mess;

		return;
	}

	private function set_departments_message_types(&$op_access, &$filters)
	{
		if(($filters['sender_type']!=="department") && ($filters['receiver_type']!=="customer"))
		{
			$mess=array();
			$mess['mi_sender_type']="customer";
			$mess['mi_receiver_type']="department";
			$this->set_mess_sr_type("sender","customer",$mess,$filters);
			$this->set_mess_sr_type("receiver","department",$mess,$filters);
			$filters['message_types'][]=$mess;
		}

		if(($filters['sender_type']!=="customer") && ($filters['receiver_type']!=="department"))
		{
			$mess=array();
			$mess['mi_sender_type']="department";
			$mess['mi_receiver_type']="customer";
			$this->set_mess_sr_type("sender","department",$mess,$filters);
			$this->set_mess_sr_type("receiver","customer",$mess,$filters);
			
			$filters['message_types'][]=$mess;
		}

		return;
	}

	private function set_user_message_types(&$op_access, &$filters)
	{
		$user_id=$this->user_manager_model->get_user_info()->get_id();

		$mess=array();
		$mess['mi_sender_type']="user";
		$mess['mi_receiver_type']="user";

		if($filters['sender_type']==="me")
			$mess['mi_sender_id']=$user_id;
		else
			$this->set_mess_sr_type("sender","user",$mess,$filters);
		
		if($filters['receiver_type']==="me")
			$mess['mi_receiver_id']=$user_id;
		else
			$this->set_mess_sr_type("receiver","user",$mess,$filters);

		$filters['message_types'][]=$mess;

		return;
	}

	private function set_mess_sr_type($sr,$type,&$mess,&$filters)
	{
		if($filters[$sr.'_'.$type])
		{
			if((int)$filters[$sr.'_'.$type])
			{
				if($type==="user")
					$mess[$sr.'_user.user_code']=(int)$filters[$sr.'_'.$type];
				else
					$mess['mi_'.$sr.'_id']=(int)$filters[$sr.'_'.$type];

			}
			else
				$mess[$sr."_".$type.'.'.$type.'_name']=$filters[$sr.'_'.$type];
		}

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