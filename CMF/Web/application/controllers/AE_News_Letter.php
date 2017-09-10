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
		if($this->input->post("post_type")==="add_news_letter")
			return $this->add_news_letter();

		$this->set_news_letters_info();

		//we may have some messages that our post has been deleted successfully.
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

			$filters['group_by']="post_id";
			$filters['start']=$start;
			$filters['count']=$per_page;
			
			$this->data['news_letters']=$this->news_letters_manager_model->get_news_letters($filters);
			
			$end=$start+sizeof($this->data['posts_info'])-1;

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

	private function add_news_letter()
	{
		$nl_id=$this->news_letter_manager_model->add_news_letter();

		return redirect(get_admin_news_letter_details_link($nl_id));
	}

	public function details($post_id)
	{
		if($this->input->post("post_type")==="edit_post")
			return $this->edit_post($post_id);

		if($this->input->post("post_type")==="delete_post")
			return $this->delete_post($post_id);

		$this->data['post_id']=$post_id;
		$post_info=$this->post_manager_model->get_post($post_id);

		$this->data['langs']=$this->language->get_languages();

		$this->data['post_contents']=array();
		foreach($this->data['langs'] as $lang => $val)
			foreach($post_info as $pi)
				if($pi['pc_lang_id'] === $lang)
				{
					$this->data['post_contents'][$lang]=$pi;
					break;
				}

		if($post_info)
		{
			$this->data['post_info']=array(
				"post_date"=>str_replace("-","/",$post_info[0]['post_date'])
				,"post_allow_comment"=>$post_info[0]['post_allow_comment']
				,"post_active"=>$post_info[0]['post_active']
				,"user_name"=>$post_info[0]['user_name']
				,"user_id"=>$post_info[0]['user_id']
				,"categories"=>$post_info[0]['categories']
				,"post_title"=>$this->data['post_contents'][$this->selected_lang]['pc_title']
			);
			$this->data['customer_link']=get_customer_post_details_link($post_id,"",$post_info[0]['post_date']);
		}
		else
		{
			$this->data['post_info']=array();
			$this->data['customer_link']="";
		}
		
		$this->data['current_time']=get_current_time();
		$this->load->model("category_manager_model");
		$this->data['categories']=$this->category_manager_model->get_hierarchy("checkbox",$this->selected_lang);

		$this->data['message']=get_message();
		$this->data['lang_pages']=get_lang_pages(get_admin_post_details_link($post_id,TRUE));
		$this->data['header_title']=$this->lang->line("post_details")." ".$post_id;

		$this->send_admin_output("post_details");

		return;
	}

	private function delete_post($post_id)
	{
		$props=$this->post_manager_model->get_post($post_id);
		foreach($props as $p)
		{
			$gallery=$p['pc_gallery']['images'];
			if($gallery)
				foreach($gallery as $i)
					unlink(get_post_gallery_image_path($i['image']));
		}
		
		$this->post_manager_model->delete_post($post_id);

		set_message($this->lang->line('post_deleted_successfully'));

		return redirect(get_link("admin_post"));
	}

	private function edit_post($post_id)
	{
		$post_props=array();
		$post_props['categories']=$this->input->post("categories");

		$post_props['post_date']=$this->input->post('post_date');
		persian_normalize($post_props['post_date']);
		if( DATE_FUNCTION === 'jdate')
			validate_persian_date_time($post_props['post_date']);
		
		$post_props['post_active']=(int)($this->input->post('post_active') === "on");
		$post_props['post_allow_comment']=(int)($this->input->post('post_allow_comment') === "on");
		
		$post_content_props=array();
		foreach($this->language->get_languages() as $lang=>$name)
		{
			$post_content=$this->input->post($lang);
			$post_content['pc_content']=$_POST[$lang]['pc_content'];
			$post_content['pc_lang_id']=$lang;

			if(isset($post_content['pc_active']))
				$post_content['pc_active']=(int)($post_content['pc_active']=== "on");
			else
				$post_content['pc_active']=0;

			$post_content['pc_gallery']=$this->get_post_gallery($post_id,$lang);

			$post_content_props[$lang]=$post_content;
		}

		foreach($this->language->get_languages() as $lang=>$name)
		{
			$copy_from=$this->input->post($lang."[copy]");
			if(!$copy_from)
				continue;

			$post_content_props[$lang]=$post_content_props[$copy_from];
			$post_content_props[$lang]['pc_lang_id']=$lang;
		}


		$this->post_manager_model->set_post_props($post_id,$post_props,$post_content_props);
		
		set_message($this->lang->line("changes_saved_successfully"));

		redirect(get_admin_post_details_link($post_id));

		return;
	}

}