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

	public function get_dashboard_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('ae_news_letter',$lang);		
		
		$data=array();
		
		$rows=$this->db
			->select("COUNT(*) as count, nlt_sent")
			->from($this->template_table_name)
			->group_by("nlt_sent")
			->get()
			->result_array();
		$nls=array();
		foreach($rows as $r)
		{
			$count=$r['count'];
			if($r['nlt_sent'])
				$nls[$CI->lang->line('sent')]=$count;
			else
				$nls[$CI->lang->line('not_sent')]=$count;
		}

		$data['nls']=$nls;
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("news_letter_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function delete_template($nlt_id)
	{
		$this->db
			->where("nlt_id",$nlt_id)
			->where("nlt_sent",0)
			->delete($this->template_table_name);

		$props['nlt_id']=$nlt_id;
		$this->log_manager_model->info("NEWS_LETTER_TEMPLATE_DELETE",$props);

		return;
	}

	public function add_template()
	{
		$this->db->insert($this->template_table_name, array("nlt_subject"=>""));
		$nlt_id=$this->db->insert_id();

		$props=array("nlt_id"=>$nlt_id);

		$this->log_manager_model->info("NEWS_LETTER_TEMPLATE_ADD",$props);

		return $nlt_id;
	}

	public function add_email($props)
	{
		$result=$this->db->get_where($this->email_table_name,array("nle_email"=>$props['email']))->row_array();
		if($result)
			return;

		$this->db->insert($this->email_table_name, array("nle_email"=>$props['email']));
		$props["nle_id"]=$this->db->insert_id();

		$this->log_manager_model->info("NEWS_LETTER_EMAIL_ADD",$props);

		return ;
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

	public function get_news_letters($filter)
	{
		$this->db
			->from($this->template_table_name);

		$this->set_query_filters($filter);

		return $this->db
			->get()
			->result_array();
	}

	public function set_template_props($nlt_id, $props)
	{
		$this->db
			->set($props)
			->where("nlt_id",$nlt_id)
			->update($this->template_table_name);

		$props['nlt_id']=$nlt_id;
		$this->log_manager_model->info("NEWS_LETTER_TEMPLATE_EDIT",$props);

		return;
	}

	public function get_email_address($nle_id)
	{
		$result=$this->db
			->where("nle_id",-$nle_id)
			->get($this->email_table_name)
			->row_array();

		if(!$result)
			return NULL;

		return $result['nle_email'];
	}

	public function get_email_subject_and_content($customer_id, $keyword)
	{
		$subject="";
		$content="";
		list($a,$nlt_id)=explode("=", $keyword);

		$template=$this->get_template($nlt_id);
		if($template)
		{
			$subject=$template['nlt_subject'];
			$content=$template['nlt_content'];
		}

		return array($subject, $content);
	}

	public function send_news_letter($nlt_id)
	{
		$this->load->model("es_manager_model");

		$emails=$this->db
			->get($this->email_table_name)
			->result_array();

		foreach($emails as $e)
			$this->es_manager_model->schedule_email(-$e['nle_id'], "news_letter", "nlt_id=".$nlt_id);	

		$this->db
			->set("nlt_sent",1)
			->where("nlt_id",$nlt_id)
			->update($this->template_table_name);

		$props['nlt_id']=$nlt_id;
		$this->log_manager_model->info("NEWS_LETTER_TEMPLATE_SEND",$props);

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