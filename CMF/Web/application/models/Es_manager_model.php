<?php

//Email And SMS Manager 
class ES_manager_model extends CI_Model
{
	private $es_table_name="es";

	private $emails_per_execution="auto"; //it can be an integer or 'auto' which sends email to consume all the remained time
	private $cron_exectution_period = 10; //in minutes
	
	public function __construct()
	{
		parent::__construct();

		return;
	}

	public function install()
	{
		$tbl=$this->db->dbprefix($this->es_table_name); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $tbl (
				`es_id` INT  NOT NULL AUTO_INCREMENT
				,`es_status` ENUM('waiting','sent','canceled') DEFAULT 'waiting'
				,`es_customer_id` INT
				,`es_module_id` VARCHAR(50)
				,`es_media` ENUM('email','sms') 
				,`es_sender_keyword` VARCHAR(50)
				,`es_submit_time` CHAR(19) DEFAULT NULL
				,`es_try_count` INT DEFAULT 0
				,`es_last_try_time` CHAR(19) DEFAULT NULL
				,PRIMARY KEY (es_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->load->model("module_manager_model");

		$this->module_manager_model->add_module("es","es_manager");
		$this->module_manager_model->add_module_names_from_lang_file("es");

		if(FALSE)
			$this->module_manager_model->set_cron("es", $this->cron_exectution_period, 1);
		
		return;
	}

	public function uninstall()
	{
		return;
	}

	public function cron($remaining_time)
	{
		$results=$this->db
			->select("es.*, customer_mobile, customer_email, model_name")
			->from($this->es_table_name)
			->join("customer","es_customer_id = customer_id","INNER")
			->join("module","es_module_id = module_id","INNER")
			->where("es_status",'waiting')
			->where("model_name != '' ")
			->order_by('es_id DESC')
			->limit(50)
			->get()
			->result_array();

		$success_ids=array();
		$failure_ids=array();
		$start_time=time();

		foreach($results as $es)
		{
			$res=FALSE;	

			$this->load->model($es['model_name']."_model");
			$model=$this->{$es['model_name']."_model"};

			$es_id=$es['es_id'];
			$keyword=$es['es_sender_keyword'];
			$customer_id=$es['es_customer_id'];
			$mobile=$es['customer_mobile'];
			$email=$es['customer_email'];

			if($es['es_media'] == 'sms' && $mobile)
			{
				if(method_exists($model, "get_sms_content"))
				{
					$content=$model->{"get_sms_content"}($customer_id, $keyword);
					if($content)
					{
						$result=$this->send_sms($mobile,$content);
						if($result)
							$res=TRUE;
					}
				}
			}

			if($es['es_media'] == 'email' && $email)
			{
				if(method_exists($model, "get_sms_subject_and_content"))
				{
					list($subject, $content)=$model->{"get_sms_subject_and_content"}($customer_id, $keyword);
					if($subject && $content )
					{
						$result=$this->send_email($email, $subject, $content);
						if($result)
							$res=TRUE;
					}
				}
			}

			if($res)
			{
				$success_ids[]=$es_id;
				$this->update_es_status($es_id, "sent");
			}
			else
			{
				$failure_ids[]=$es_id;
				$this->update_es_status($es_id, "canceled");
			}

			if(time()-$start_time > $remaining_time)
				break;
		}

		$this->log_manager_model->info("ES_CRON",array(
			"success_ids"	=> implode(",", $success_ids)
			,"failure_ids"	=> implode(",", $failure_ids)
		));

		return;
	}

	public function get_sms_content($customer_id,$keyword)
	{
		return "Asd";
	}

	public function get_sms_subject_and_content($customer_id,$keyword)
	{
		return array("subject","content ".$customer_id. " ".$keyword);
	}

	public function schedule_sms($customer_id, $module_id, $keyword)
	{
		$es_id = $this->add_es("waiting", $customer_id, $module_id, "sms", $keyword);

		return $es_id;
	}

	public function send_sms_now($customer_id, $module_id, $keyword, $number, $content)
	{
		$es_id = $this->add_es("waiting", $customer_id, $module_id, "sms", $keyword);

		$result=$this->send_sms($number, $content);

		if($result)
			$this->update_es_status($es_id, "sent");
		else
			$this->update_es_status($es_id, "waiting");

		return $result;
	}

	private function send_sms($number, $content)
	{
		$content=$content."\n".$this->lang->line("main_name");
		
		$result=burge_cmf_send_sms($number, $content);

		return $result;
	}

	public function schedule_email($customer_id, $module_id, $keyword)
	{
		$es_id = $this->add_es("waiting", $customer_id, $module_id, "email", $keyword);

		return $es_id;
	}

	public function send_email_now($customer_id, $module_id, $keyword, $email, $subject, $content)
	{
		$es_id = $this->add_es("waiting", $customer_id, $module_id, "email", $keyword);

		$result=$this->send_email($email, $subject, $content);

		if($result)
			$this->update_es_status($es_id, "sent");
		else
			$this->update_es_status($es_id, "waiting");

		return $result;
	}

	private function send_email($email, $subject, $content)
	{
		$this->lang->load('email_lang',$this->selected_lang);		
		$subject=$subject.$this->lang->line("header_separator").$this->lang->line("main_name");
		$message=str_replace(
			array('$content','$slogan','$response_to'),
			array($content,$this->lang->line("slogan"),"")
			,$this->lang->line("email_template")
		);

		$result=burge_cmf_send_mail($email,$subject,$message);

		return $result;
	}

	private function add_es($status, $customer_id, $module_id, $media, $keyword)
	{
		$props=array(
			"es_status"				=> $status
			,"es_customer_id"		=>	$customer_id
			,"es_module_id"		=> $module_id
			,"es_media"				=> $media
			,"es_sender_keyword"	=> $keyword
			,"es_submit_time"		=> get_current_time()
		);

		$this->db->insert($this->es_table_name, $props);
		$es_id=$this->db->insert_id();

		$props['es_id']=$es_id;

		$this->log_manager_model->info("ES_ADD",$props);

		return $es_id;
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

}
