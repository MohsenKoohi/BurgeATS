<?php
class Customer_manager_model extends CI_Model
{
	private $customer_table_name="customer";
	private $customer_types=array("regular","agent");
	private $customer_log_dir;
	private $customer_log_file_extension="txt";
	private $customer_log_types=array(
		"UNKOWN"					=>0
		,"ADDING_CUSTOMER"	=>1
	);
	
	public function __construct()
	{
		parent::__construct();

		$this->customer_log_dir=HOME_DIR."/application/logs/customer";
		
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
				,`customer_email` varchar(100) NOT NULL 
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
		$id=$this->db->insert_id();

		$this->logger->info("[add_customer] [name:".$name."] [id:".$id."] [result:1]");

		$log_desc=array(
			"cutomer_name"	=>	$name
			,"customer_type"	=>	$type
			,"desc"	=>	$desc
		);

		$this->add_customer_log($id,'ADDING_CUSTOMER',$log_desc);

		return TRUE;
	}


	public function add_customer_log($customer_id,$log_type,$desc)
	{
		$log_path=$this->get_customer_log_path($customer_id,$log_type);

		$string='{"log_type":"'.$log_type.'"';
		foreach($desc as $index=>$val)
			$string.=',"'.trim(preg_replace('/(\s)+/', "_", $index)).'":"'.trim(preg_replace('/(\s)+/', " ", $val)).'"';
		$string.="}";

		file_put_contents($log_path, $string);
		
		return;
	}


	private function get_customer_log_path($customer_id,$log_type)
	{
		$customer_dir=$this->get_customer_directory($customer_id);
		
		if(isset($this->customer_log_types[$log_type]))
			$type_index=$this->customer_log_types[$log_type];
		else
			$type_index=0;

		$dtf=DATE_FUNCTION;
		$dt=$dtf("Y-m-d,H-i-s");
		
		$ext=$this->customer_log_file_extension;

		$log_path=$customer_dir."/".sprintf("%02d",$type_index).",".$dt.".".$ext;
		
		return $log_path;
	}

	private function get_customer_directory($customer_id)
	{
		$dir1=(int)($customer_id/1000);
		$dir2=$customer_id % 1000;
		
		$path1=$this->customer_log_dir."/".$dir1;
		if(!file_exists($path1))
			mkdir($path1,0777);

		$path2=$this->customer_log_dir."/".$dir1."/".$dir2;
		if(!file_exists($path2))
			mkdir($path2,0777);

		return $path2;
	}
}