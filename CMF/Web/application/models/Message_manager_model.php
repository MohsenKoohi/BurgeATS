<?php
class Message_manager_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		
		return;
	}

	public function install()
	{
		$module_table=$this->db->dbprefix('message'); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $module_table (
				`message_id` BIGINT AUTO_INCREMENT NOT NULL
				,`message_ref_id` CHAR(10)
				,`message_parent_id` BIGINT
				,`message_sender_type` enum('customer','user')
				,`message_sender_id` BIGINT
				,`message_time_stamp` DATETIME
				,`message_receiver_type` enum('customer','user')
				,`message_receiver_id` BIGINT
				,`message_subject` VARCHAR(100)
				,`message_body` TEXT
				,`message_verifier_id` INT DEFAULT 0
				,PRIMARY KEY (message_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$module_table=$this->db->dbprefix('message_user'); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $module_table (
				`mu_user_id` INT NOT NULL
				,`mu_verifier` TINYINT NOT NULL DEFAULT 0 
				,`mu_message_admin` TINYINT NOT NULL DEFAULT 0
				,PRIMARY KEY (mu_user_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->module_manager_model->add_module("message","message_manager");
		$this->module_manager_model->add_module_names_from_lang_file("message");

		$this->module_manager_model->add_module("message_access","");
		$this->module_manager_model->add_module_names_from_lang_file("message_access");
		
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
		$CI->lang->load('ae_module',$lang);		
		
		$data=array();
		$data['modules']=$this->get_all_modules_info($lang);
		$data['total_text']=$CI->lang->line("total");
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("module_dashboard"),$data,TRUE);
		
		return $ret;		
	}
}