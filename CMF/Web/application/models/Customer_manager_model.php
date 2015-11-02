<?php
class Customer_manager_model extends CI_Model
{
	private $customer_table_name="customer";
	private $customer_types=array("regular","agent");
	private $customer_log_dir;
	
	public function __construct()
	{
		parent::__construct();

		$this->customer_log_dir=HOME_DIR."/application/logs/customer";
		/*
		eval('$res= '.DATE_FUNCTION.'("Y m");');
		list($year,$month)=explode(' ', $res);
		$this->year=$year;
		$this->month=$month;
		*/
		return;
	}

	public function install()
	{
		$table=$this->db->dbprefix($this->customer_table_name); 
		$customer_types="'".implode("','", $this->customer_types)."'";
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $table (
				`customer_id` int AUTO_INCREMENT NOT NULL
				,`customer_type` enum($customer_types) 
				,`customer_email` varchar(100) NOT NULL UNIQUE
				,`customer_pass` char(32) DEFAULT NULL
				,`customer_salt` char(32) DEFAULT NULL
				,`customer_name` varchar(255) NOT NULL
				,`customer_code` char(10) DEFAULT NULL
				,`customer_province` varchar(255) DEFAULT NULL
				,`customer_city` varchar(255) DEFAULT NULL
				,`customer_address` varchar(1000) DEFAULT NULL
				,`customer_phone` varchar(32) DEFAULT NULL 
				,`customer_mobile` varchar(32) DEFAULT NULL 
				,PRIMARY KEY (customer_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		if(make_dir_and_check_permission($this->customer_log_dir)<0)
		{
			echo "Error: ".$this->customer_log_dir." cant be used, please check permissions, and try again";
			exit;
		}

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("customer","customer_manager");
		$this->module_manager_model->add_module_names_from_lang_file("customer");
		
		return;
	}

	public function uninstall()
	{
	
		return;
	}

	public function get_dashbord_info()
	{
		return "";
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('admin_hit_counter',$lang);		
		
		$data=array();
		$data['month_text']=$CI->lang->line("monthly_visit");
		$data['year_text']=$CI->lang->line("yearly_visit");
		$data['total_text']=$CI->lang->line("total_visit");

		$counts=$this->get_all_counts();
		$data['total_count']=$counts[0]['total_count'];
		$data['year_count']=$counts[0]['year_count'];
		$data['month_count']=$counts[0]['month_count'];
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("hit_counter_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function get_customer_types()
	{
		return $this->customer_types;
	}

	public function add_customer($name,$type,$desc="")
	{
		$this->db->insert("customer",array(
			"customer_name"=>$name
			,"customer_type"=>$type
		));

		$this->logger->info("[add_customer] [name:".$name."] [result:1]");
	}


}