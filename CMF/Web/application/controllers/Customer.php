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
	
		$this->data['lang_pages']=get_lang_pages(get_link("admin_customer",TRUE));
		$this->data['header_title']=$this->lang->line("customers");
		$this->data['customer_types']=$this->customer_manager_model->get_customer_types();

		$this->send_admin_output("customer");

		return;	 
	}

	private function set_data_customers()
	{
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
		}

		if($this->input->get("type"))
		{
			$filter['type']=$this->input->get("type");
			$this->data['filter']['type']=$this->input->get("type");
		}

		$total=$this->customer_manager_model->get_total_customers($filter);
		$this->data['customers_total']=$total;
		$this->data['customers_total_pages']=ceil($total/$items_per_page);
		$this->data['customers_current_page']=$page;

		$start=($page-1)*$items_per_page;
		$filter['start']=$start;
		$filter['length']=$items_per_page;

		
		$end=$start+$items_per_page;
		if($end>$total)
			$end=$total-1;
		$this->data['customers_start']=$start+1;
		$this->data['customers_end']=$end+1;
		if(!$total)
		{
			$this->data['customers_start']=0;
			$this->data['customers_end']=0;
		}


		$filter['order_by']="customer_id DESC";

		$this->data['customers_info']=$this->customer_manager_model->get_customers($filter);

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

}