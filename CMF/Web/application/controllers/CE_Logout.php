<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Logout extends Burge_CMF_Controller {
	protected $hit_level=-1;

	function __construct()
	{
		parent::__construct();

	}

	public function index()
	{
		$this->load->model("customer_manager_model");
		$this->customer_manager_model->set_customer_logged_out();
		
		redirect(get_link("home_url"));
		return;		
	}
}