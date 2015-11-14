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
		
		$this->set_data_customers();
		$this->data['raw_page_url']=get_link("admin_customer");
		
		$page_raw_lang_url=get_link("admin_customer",TRUE);
		if($this->data['url_queries'])
			$page_raw_lang_url.="?".$this->data['url_queries'];
		$this->data['lang_pages']=get_lang_pages($page_raw_lang_url);

		$this->data['header_title']=$this->lang->line("customers");
		$this->data['customer_types']=$this->customer_manager_model->get_customer_types();

		$this->send_admin_output("customer");

		return;	 
	}

	private function set_data_customers()
	{
		$this->data['url_queries']="";
		$items_per_page=10;
		$page=1;
		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$filter=array();
		$this->data['filter']=array();

		if($this->input->get("name"))
		{
			$filter['name']=$this->input->get("name");
			$this->data['filter']['name']=$this->input->get("name");
			$this->data['url_queries'].="&name=".urlencode($filter['name']);
		}

		if($this->input->get("type"))
		{
			$filter['type']=$this->input->get("type");
			$this->data['filter']['type']=$this->input->get("type");
			$this->data['url_queries'].="&filter=".urlencode($filter['type']);
		}

		$total=$this->customer_manager_model->get_total_customers($filter);
		$this->data['customers_total']=$total;
		$this->data['customers_total_pages']=ceil($total/$items_per_page);
		if($total)
		{
			if($page > $this->data['customers_total_pages'])
				$page=$this->data['customers_total_pages'];
			$this->data['customers_current_page']=$page;
			if($page!=1)
				$this->data['url_queries'].="&page=".$page;

			$start=($page-1)*$items_per_page;
			$filter['start']=$start;
			$filter['length']=$items_per_page;

			$end=$start+$items_per_page-1;
			if($end>($total-1))
				$end=$total-1;
			$this->data['customers_start']=$start+1;
			$this->data['customers_end']=$end+1;		
	
			$filter['order_by']="customer_name ASC";

			$this->data['customers_info']=$this->customer_manager_model->get_customers($filter);

		}
		else
		{
			$this->data['customers_start']=0;
			$this->data['customers_end']=0;
			$this->data['customers_info']=array();
		}
		
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
			$res=$this->customer_manager_model->add_customer($customer_name,$customer_type,$desc);
			if($res)
				$this->data['message']=$this->lang->line("added_successfully");
		}

		return;
	}

	public function customer_details($customer_id,$task_id=0)
	{
		$customer_id=(int)$customer_id;

		$this->lang->load('admin_customer_details',$this->selected_lang);
		$this->data['message']=get_message();
		
		if($this->input->post())
		{
			$this->lang->load('error',$this->selected_lang);

			if("customer_properties" === $this->input->post("post_type"))
				$this->save_customer_new_properties();
		}

		$filter=array();

		if($this->input->get('log_type'))
		{
			$filter['log_type']=$this->input->get('log_type');
		}

		$this->data['customer_info']=$this->customer_manager_model->get_customer_info($customer_id);
		if(NULL == $this->data['customer_info'])
		{
			$this->data['message']=$this->lang->line("customer_not_found");
		}
		else
		{
			$logs_pp=10;
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");

			$start=($page-1)*$logs_pp;
			$filter['start']=$start;
			$filter['length']=$logs_pp;
			
			$log_res=$this->customer_manager_model->get_customer_logs($customer_id,$filter);
			unset($filter['start'],$filter['length']);
			
			$total=$log_res['total'];
			$this->data['customer_logs']=$log_res['results'];
			$end=$start+sizeof($this->data['customer_logs'])-1;

			$this->data['logs_current_page']=$page;
			$this->data['logs_total_pages']=ceil($total/$logs_pp);
			$this->data['logs_total']=$total;
			if($total)
			{
				$this->data['logs_start']=$start+1;
				$this->data['logs_end']=$end+1;		
			}
			else
			{
				$this->data['logs_start']=0;
				$this->data['logs_end']=0;
			}
		}

				
		$this->data['filter']=$filter;
		$this->data['raw_page_url']=get_admin_customer_details_link($customer_id);
		$this->data['lang_pages']=get_lang_pages(get_admin_customer_details_link($customer_id,TRUE));


		$this->data['customer_types']=$this->customer_manager_model->get_customer_types();		
		$this->data['log_types']=$this->customer_manager_model->get_customer_log_types();
		$this->data['provinces']=$this->customer_manager_model->get_provinces();
		$this->data['cities']=$this->customer_manager_model->get_cities();

		$this->data['header_title']=$this->lang->line("customer_details");

		$this->send_admin_output("customer_details");

		return;	 		
	}

	private function save_customer_new_properties()
	{
		$customer_id=$this->input->post("customer_id");

		$args=array(
			"customer_name"		=>$this->input->post("customer_name")
			,"customer_type"		=>$this->input->post("customer_type")
			,"customer_email"		=>$this->input->post("customer_email")
			,"customer_code"		=>$this->input->post("customer_code")
			,"customer_province"	=>$this->input->post("customer_province")
			,"customer_city"		=>$this->input->post("customer_city")
			,"customer_address"	=>$this->input->post("customer_address")
			,"customer_phone"		=>$this->input->post("customer_phone")
			,"customer_mobile"	=>$this->input->post("customer_mobile")
		);

		$desc=$this->input->post("desc");

		$result=$this->customer_manager_model->set_customer_properties($customer_id,$args,$desc);

		set_message($this->lang->line("saved_successfully"));

		redirect(get_admin_customer_details_link($customer_id));
	}

}