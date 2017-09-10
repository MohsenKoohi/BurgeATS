<?php

class News_letter_manager_model extends CI_Model
{
	private $template_table_name="newsletter_template";
	private $email_table_name="newsletter_email";

	public function __construct()
	{
		parent::__construct();

		return;
	}

	public function install()
	{
		$tbl=$this->db->dbprefix($this->template_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`nlt_id` INT  NOT NULL AUTO_INCREMENT
				,`nlt_subject` VARCHAR(511)
				,`nlt_content` TEXT
				,`nlt_sent` BIT(1) DEFAULT 0
				,PRIMARY KEY (nlt_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$tbl=$this->db->dbprefix($this->email_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`nle_id` INT  NOT NULL AUTO_INCREMENT
				,`nle_email` VARCHAR(128)
				,PRIMARY KEY (nle_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("news_letter","news_letter_manager");
		$this->module_manager_model->add_module_names_from_lang_file("news_letter");

		return;
	}

	public function uninstall()
	{
		return;
	}

	public function get_email_subject_and_content($customer_id, $keyword)
	{
		list($type,$id)=explode("=", $keyword);

		if($type == 'order')
			return $this->get_order_invoice($id);

		if($type == 'order_status')
			return $this->get_email_status($id);
	}

	private function get_order_invoice($order_id, $order=NULL)
	{
		if(!$order)
		{
			$orders=$this->get_orders(array("order_id"=>$order_id));
			if(!$orders)
				return;
			$order=$orders[0];
		}

		$CI=& get_instance();

		$CI->load->model("cart_manager_model");
		$data=array();
		$data['order_id']=$order_id;
		$data['order_info']=$order;
		$data['cart_info']=$CI->cart_manager_model->get_order_cart($order_id, $CI->selected_lang);
		$data['styles_url']=get_link("styles_url");
		
		$CI->lang->load('ae_order',$CI->selected_lang);
		$CI->lang->load('ae_general',$CI->selected_lang);
		$CI->load->library('parser');
		$words=array(
			"order_number","name","date","total","status","currency","status","product_name"
			,"quantity","unit_price","total_price","invoice");
		foreach($this->order_statuses as $s)
			$words[]='order_status_'.$s;

		foreach($words as $w)
			$data[$w."_text"]=$CI->lang->line($w);
		
		$content=$CI->parser->parse($CI->get_admin_view_file("order_invoice"),$data,TRUE);

		$subject=$CI->lang->line("order")." ".$order_id;

		return array($subject, $content);
	}

	public function add_template()
	{
		$this->db->insert($this->template_table_name, array("nlt_subject"=>""));
		$nlt_id=$this->db->insert_id();

		$props=array("nlt_id"=>$nlt_id);

		$this->log_manager_model->info("NEWS_LETTER_TEMPLATE_ADD",$props);

		return $nlt_id;
	}

	public function get_template($nlt_id)
	{
		return $this->db
			->select("*")
			->from($this->template_table_name)
			->where("nlt_id",(int)$nlt_id)
			->get()
			->row_array();
	}

	private function update_es_status($es_id, $status)
	{

		$props=array(
			"es_status"				=> $status
			,"es_last_try_time"	=>	get_current_time()
		);

		$this->db
			->set("es_try_count", "es_try_count + 1", FALSE)
			->set($props)
			->where("es_id", $es_id)
			->update($this->es_table_name);

		$props['es_id']=$es_id;

		$this->log_manager_model->info("ES_UPDATE",$props);

		return;
	}

	public function get_news_letters($filter)
	{
		$this->db
			->from($this->template_table_name);

		$this->set_query_filters($filter);

		return $this->db
			->get()
			->result_array();
	}

	public function set_template($nlt_id, $props)
	{
		$this->db
			->set($props)
			->where("nlt_id",$nlt_id)
			->update($this->template_table_name);

		$props['nlt_id']=$nlt_id;
		$this->log_manager_model->info("NEWS_LETTER_TEMPLATE_EDIT",$props);

		return;
	}

	public function get_total_news_letters($filter)
	{
		$this->db
			->select("COUNT(*) as count")
			->from($this->template_table_name);
		
		$this->set_query_filters($filter);

		$row=$this->db
			->get()
			->row_array();

		return $row['count'];
	}

	private function set_query_filters($filter)
	{
		if(isset($filter['subject']))
		{
			$this->db->where("nlt_subject LIKE '%".str_replace(' ', '%', $filter['subject'])."%'");
		}

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);

		return;
	}

}