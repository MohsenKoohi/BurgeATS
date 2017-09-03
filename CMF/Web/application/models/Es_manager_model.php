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
				,`es_status` ENUM('sending','sent') DEFAULT 'sending'
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

	public function send_email_now($customer_id, $module_id, $keyword, $email, $subject, $content)
	{
		
	}

}
