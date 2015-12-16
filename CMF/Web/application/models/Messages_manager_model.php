<?php
class Messages_manager_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
		
		return;
	}

	public function install()
	{
		$module_table=$this->db->dbprefix('messages'); 
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS $module_table (
				`message_id` BIGINT AUTO_INCREMENT NOT NULL
				,`message_ref_id` CHAR(10)
				,`message_parent_id` BIGINT
				,`message_sender_type` enum('customer','user')
				,`message_sender_id` BIGINT
				,`message_time_stamp` DATETIME
				,`message_receiver_type` enum('customer','user','system')
				,`message_receiver_id` BIGINT
				,`message_subject` VARCHAR(100)
				,`message_body` TEXT
				,PRIMARY KEY (message_id)	
			) ENGINE=InnoDB DEFAULT CHARSET=utf8"
		);

		$this->add_module("message","message_manager");
		$this->add_module_names_from_lang_file("message");
		
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