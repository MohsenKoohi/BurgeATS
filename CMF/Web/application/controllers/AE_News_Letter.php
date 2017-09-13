<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_News_Letter extends Burge_CMF_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_news_letter',$this->selected_lang);
		$this->load->model("news_letter_manager_model");

	}

	public function index()
	{
		if($this->input->post("post_type")==="add_template")
			return $this->add_template();

		$this->set_news_letters_info();

		$this->data['message']=get_message();

		$this->data['raw_page_url']=get_link("admin_news_letter");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_news_letter",TRUE));
		$this->data['header_title']=$this->lang->line("news_letter");

		$this->send_admin_output("news_letter");

		return;	 
	}	

	private function set_news_letters_info()
	{
		$filters=array();

		$this->initialize_filters($filters);

		$total=$this->news_letter_manager_model->get_total_news_letters($filters);
		if($total)
		{
			$per_page=20;
			$page=1;
			if($this->input->get("page"))
				$page=(int)$this->input->get("page");

			$start=($page-1)*$per_page;

			$filters['start']=$start;
			$filters['count']=$per_page;
			
			$this->data['news_letters']=$this->news_letter_manager_model->get_news_letters($filters);
			
			$end=$start+sizeof($this->data['news_letters'])-1;

			unset($filters['start']);
			unset($filters['count']);
			unset($filters['group_by']);

			$this->data['current_page']=$page;
			$this->data['total_pages']=ceil($total/$per_page);
			$this->data['total_results']=$total;
			$this->data['results_start']=$start+1;
			$this->data['results_end']=$end+1;		
		}
		else
		{
			$this->data['current_page']=0;
			$this->data['total_pages']=0;
			$this->data['total_results']=$total;
			$this->data['results_start']=0;
			$this->data['results_end']=0;
		}

		unset($filters['lang']);
			
		$this->data['filter']=$filters;

		return;
	}

	private function initialize_filters(&$filters)
	{
		$filters['lang']=$this->language->get();

		if($this->input->get("title"))
			$filters['title']=$this->input->get("title");

		persian_normalize($filters);

		return;
	}

	private function add_template()
	{
		$nl_id=$this->news_letter_manager_model->add_template();

		return redirect(get_admin_news_letter_template_link($nl_id));
	}

	public function template($nl_id)
	{
		if($this->input->post("post_type")==="edit_template")
			return $this->edit_template($nl_id);

		if($this->input->post("post_type")==="delete_template")
			return $this->delete_template($nl_id);

		if($this->input->post("post_type")==="send_news_letter")
			return $this->send_news_letter($nl_id);

		$this->data['nl_id']=$nl_id;
		$this->data['news_letter_id']=$nl_id;
		$this->data['news_letter_info']=$this->news_letter_manager_model->get_template($nl_id);

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_news_letter_template_link($nl_id,TRUE));
		$this->data['header_title']=$this->lang->line("news_letter_details")." ".$nl_id;

		$this->send_admin_output("news_letter_template");

		return;
	}

	private function delete_template($news_letter_id)
	{
		$this->news_letter_manager_model->delete_template($news_letter_id);

		set_message($this->lang->line('news_letter_deleted_successfully'));

		return redirect(get_link("admin_news_letter"));
	}

	private function edit_template($nl_id)
	{
		$props=array(
			"nlt_subject"		=> $this->input->post("subject")
			,"nlt_content"		=> $_POST["content"]
		);

		$this->news_letter_manager_model->set_template_props($nl_id, $props);
		
		set_message($this->lang->line("changes_saved_successfully"));

		redirect(get_admin_news_letter_template_link($nl_id));

		return;
	}

	private function send_news_letter($nl_id)
	{
		$this->news_letter_manager_model->send_news_letter($nl_id);
		
		set_message($this->lang->line("news_letter_sent_successfully"));

		redirect(get_admin_news_letter_template_link($nl_id));

		return;
	}

}