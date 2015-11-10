<?php
class Customer_manager_model extends CI_Model
{
	private $customer_table_name="customer";
	private $customer_types=array("regular","agent");
	private $customer_log_dir;
	private $customer_log_file_extension="txt";
	private $customer_log_types=array(
		"UNKOWN"						=>0
		,"CUSTOMER_ADD"			=>1001
		,"CUSTOMER_INFO_CHANGE"	=>1002
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
		
		$this->insert_province_and_citiy_tables_to_db();

		return;
	}

	public function uninstall()
	{
		
		return;
	}

	public function get_total_customers($filter=array())
	{
		$this->db->select("COUNT(*) as count");
		$this->db->from($this->customer_table_name);
		$this->set_search_where_clause($filter);

		$query=$this->db->get();

		$row=$query->row_array();

		return $row['count'];
	}

	public function get_customers($filter)
	{
		$this->db->select("*");
		$this->db->from($this->customer_table_name);
		$this->set_search_where_clause($filter);

		$query=$this->db->get();

		$results=$query->result_array();

		//there are some private data in results
		foreach ($results as &$res)
		{
			unset($res['customer_salt'],$res['customer_pass']);
		}
		return $results;
	}

	private function set_search_where_clause($filter)
	{
		if(isset($filter['name']))
		{
			$filter['name']=persian_normalize($filter['name']);
			$this->db->where("customer_name LIKE '%".str_replace(' ', '%', $filter['name'])."%'");
		}

		if(isset($filter['type']))
		{
			$this->db->where("customer_type",$filter['type']);
		}

		if(isset($filter['id']))
		{
			$this->db->where_in("customer_id",$filter['id']);
		}

		if(isset($filter['order_by']))
			$this->db->order_by($filter['order_by']);

		if(isset($filter['start']) && isset($filter['length']))
			$this->db->limit((int)$filter['length'],(int)$filter['start']);


		return;
	}

	public function get_customer_info($customer_id)
	{
		$results=$this->get_customers(array("id"=>array($customer_id)));

		if(isset($results[0]))
			return $results[0];

		return NULL;
	}

	public function get_dashbord_info()
	{
		$CI=& get_instance();
		$lang=$CI->language->get();
		$CI->lang->load('admin_customer',$lang);		
		
		$data['customers_count']=$this->get_total_customers();
		
		$CI->load->library('parser');
		$ret=$CI->parser->parse($CI->get_admin_view_file("customer_dashboard"),$data,TRUE);
		
		return $ret;		
	}

	public function get_customer_types()
	{
		return $this->customer_types;
	}

	public function add_customer($name,$type,$desc="")
	{	
		$name=persian_normalize_word($name);
		$desc=persian_normalize_word($desc);

		$this->db->insert($this->customer_table_name,array(
			"customer_name"=>$name
			,"customer_type"=>$type
		));
		$id=$this->db->insert_id();

		$this->log_manager_model->info("CUSTOMER_ADD",array(
			"customer_name"		=>	$name
			,"customer_id"			=>	$id
			,"customer_type"		=>	$type
			,"desc"					=>	$desc
		));

		$this->add_customer_log($id,'CUSTOMER_ADD',array(
			"cutomer_name"		=>	$name
			,"customer_type"	=>	$type
			,"desc"				=>	$desc
		));

		return TRUE;
	}

	public function set_customer_properties($customer_id, $args, $desc)
	{
		$allowed_props=array(
			"name","type","email","code","province","city","address","phone","mobile"
		);

		$props=array();
		foreach($allowed_props as $prop)
		{
			$index="customer_".$prop;
			if(isset($args[$index]))
				$props[$index]=$args[$index];
		}

		persian_normalize($props);

		$this->db->where("customer_id",(int)$customer_id);
		$this->db->update($this->customer_table_name,$props);

		$props['customer_id']=$customer_id;
		$props['desc']=$desc;

		$this->log_manager_model->info("CUSTOMER_INFO_CHANGE",$props);

		$this->add_customer_log($customer_id,'CUSTOMER_INFO_CHANGE',$props);

		return;
	}

	public function get_customer_log_types()
	{
		return $this->customer_log_types;
	}

	public function add_customer_log($customer_id,$log_type,$desc)
	{
		if(isset($this->customer_log_types[$log_type]))
			$type_index=$this->customer_log_types[$log_type];
		else
			$type_index=0;

		$CI=&get_instance();
		if(isset($CI->in_admin_env) && $CI->in_admin_env)
		{
			$desc["active_user_id"]=$CI->user->get_id();
			$desc["active_user_email"]=$CI->user->get_email();
		}		
		
		$log_path=$this->get_customer_log_path($customer_id,$type_index);

		$string='{"log_type":"'.$log_type.'"';
		$string.=',"log_type_index":"'.$type_index.'"';

		foreach($desc as $index=>$val)
		{
			$index=trim($index);
			$index=preg_replace('/[\\\'\"]+/', "", $index);
			$index=preg_replace('/\s+/', "_", $index);

			$val=trim($val);
			$val=preg_replace('/[\\\'\"]+/', "", $val);
			$val=preg_replace('/\s+/', " ", $val);
			
			$string.=',"'.$index.'":"'.$val.'"';
		}
		$string.="}";

		file_put_contents($log_path, $string);
		
		return;
	}

	//it returns an array with two index, 'results' which specifies  logs
	//and total which indicates the total number of logs 
	public function get_customer_logs($customer_id,$filter=array())
	{
		$dir=$this->get_customer_directory($customer_id);
		$file_names=scandir($dir, SCANDIR_SORT_DESCENDING);

		$logs=array();
		$count=-1;
		$start=0;
		if(isset($filter['start']))
			$start=(int)$filter['start'];
		$length=sizeof($file_names);
		if(isset($filter['length']))
			$length=(int)$filter['length'];

		foreach($file_names as $fn)
		{
			if("." === $fn|| ".." === $fn)
				continue;

			$tmp=explode(".", $fn);
			list($date_time,$log_type)=explode("#",$tmp[0]);
			list($date,$time)=explode(",",$date_time);
			$time=str_replace("-", ":", $time);
			$date=str_replace("-", "/", $date);
			$date_time=$date." ".$time;

			//now we have timestamp and log_type of this log
			//and we can filter logs we don't want here;
			if(isset($filter['log_type']))
				if($log_type != $this->customer_log_types[$filter['log_type']])
					continue;

			$count++;
			if($count < $start)
				continue;
			if($count >= ($start+$length))
				continue;

			//reading log
			$log=json_decode(file_get_contents($dir."/".$fn));
			if($log)
				$log->timestamp=$date_time;
			$logs[]=$log;
		}

		$total=$count+1;

		return  array(
			"results"	=> $logs
			,"total"		=> $total
		);
	}

	private function get_customer_log_path($customer_id,$type_index)
	{
		$customer_dir=$this->get_customer_directory($customer_id);
		
		$dtf=DATE_FUNCTION;	
		$dt=$dtf("Y-m-d,H-i-s");	
		
		$ext=$this->customer_log_file_extension;
		$tp=sprintf("%02d",$type_index);

		$log_path=$customer_dir."/".$dt."#".$tp.".".$ext;
		
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

	public function get_provinces()
	{
		$this->db->select("*");
		$this->db->from("province");
		$this->db->order_by("province_name ASC");
		$query=$this->db->get();
		return $query->result_array();
	}

	public function get_cities()
	{
		$this->db->from("city");
		$this->db->join("province","city_province_id = province_id","left");
		$this->db->order_by("province_id asc, city_id asc");
		$query=$this->db->get();	

		$ret=array();
		foreach($query->result_array() as $row)
			$ret[$row['province_name']][]=$row['city_name'];

		return $ret;		
	}

	private function insert_province_and_citiy_tables_to_db()
	{
		$result=$this->db->query("show tables like '%city' ");
		if(sizeof($result->result_array()))
			return;


		$table=$this->db->dbprefix("province"); 
		$this->db->query("
			CREATE TABLE IF NOT EXISTS $table (
				`province_id` int(11) unsigned NOT NULL AUTO_INCREMENT
  				,`province_name` varchar(100) NOT NULL  				
				,PRIMARY KEY (`province_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");

		$this->db->query("
			INSERT INTO $table (`province_id`, `province_name`) VALUES
				(1, 'آذربایجان شرقی'),
				(2, 'آذربایجان غربی'),
				(3, 'اردبیل'),
				(4, 'اصفهان'),
				(5, 'البرز'),
				(6, 'ایلام'),
				(7, 'بوشهر'),
				(8, 'تهران'),
				(9, 'چهارمحال بختیاری'),
				(10, 'خراسان جنوبی'),
				(11, 'خراسان رضوی'),
				(12, 'خراسان شمالی'),
				(13, 'خوزستان'),
				(14, 'زنجان'),
				(15, 'سمنان'),
				(16, 'سیستان و بلوچستان'),
				(17, 'فارس'),
				(18, 'قزوین'),
				(19, 'قم'),
				(20, 'کردستان'),
				(21, 'کرمان'),
				(22, 'کرمانشاه'),
				(23, 'کهکیلویه و بویراحمد'),
				(24, 'گلستان'),
				(25, 'گیلان'),
				(26, 'لرستان'),
				(27, 'مازندران'),
				(28, 'مرکزی'),
				(29, 'هرمزگان'),
				(30, 'همدان'),
				(31, 'یزد');
		");

		$table=$this->db->dbprefix("city"); 
		$this->db->query("
			CREATE TABLE IF NOT EXISTS $table (				
				`city_id` int(11) unsigned NOT NULL AUTO_INCREMENT
				,`city_province_id` int(11) unsigned NOT NULL
				,`city_name` varchar(200) NOT NULL
				,PRIMARY KEY (`city_id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");

		$this->db->query("
			INSERT INTO $table (`city_id`, `city_province_id`, `city_name`) VALUES
				(1, 1, 'آذر شهر'),
				(2, 1, 'اسکو'),
				(3, 1, 'اهر'),
				(4, 1, 'بستان آباد'),
				(5, 1, 'بناب'),
				(6, 1, 'بندر شرفخانه'),
				(7, 1, 'تبریز'),
				(8, 1, 'تسوج'),
				(9, 1, 'جلفا'),
				(10, 1, 'سراب'),
				(11, 1, 'شبستر'),
				(12, 1, 'صوفیان'),
				(13, 1, 'عجبشیر'),
				(14, 1, 'قره آغاج'),
				(15, 1, 'کلیبر'),
				(16, 1, 'کندوان'),
				(17, 1, 'مراغه'),
				(18, 1, 'مرند'),
				(19, 1, 'ملکان'),
				(20, 1, 'ممقان'),
				(21, 1, 'میانه'),
				(22, 1, 'هادیشهر'),
				(23, 1, 'هریس'),
				(24, 1, 'هشترود'),
				(25, 1, 'ورزقان'),
				(26, 2, 'ارومیه'),
				(27, 2, 'اشنویه'),
				(28, 2, 'بازرگان'),
				(29, 2, 'بوکان'),
				(30, 2, 'پلدشت'),
				(31, 2, 'پیرانشهر'),
				(32, 2, 'تکاب'),
				(33, 2, 'خوی'),
				(34, 2, 'سردشت'),
				(35, 2, 'سلماس'),
				(36, 2, 'سیه چشمه- چالدران'),
				(37, 2, 'سیمینه'),
				(38, 2, 'شاهین دژ'),
				(39, 2, 'شوط'),
				(40, 2, 'ماکو'),
				(41, 2, 'مهاباد'),
				(42, 2, 'میاندوآب'),
				(43, 2, 'نقده'),
				(44, 3, 'اردبیل'),
				(45, 3, 'بیله سوار'),
				(46, 3, 'پارس آباد'),
				(47, 3, 'خلخال'),
				(48, 3, 'سرعین'),
				(49, 3, 'کیوی (کوثر)'),
				(50, 3, 'گرمی (مغان)'),
				(51, 3, 'مشگین شهر'),
				(52, 3, 'مغان (سمنان)'),
				(53, 3, 'نمین'),
				(54, 3, 'نیر'),
				(55, 4, 'آران و بیدگل'),
				(56, 4, 'اردستان'),
				(57, 4, 'اصفهان'),
				(58, 4, 'باغ بهادران'),
				(59, 4, 'تیران'),
				(60, 4, 'خمینی شهر'),
				(61, 4, 'خوانسار'),
				(62, 4, 'دهاقان'),
				(63, 4, 'دولت آباد-اصفهان'),
				(64, 4, 'زرین شهر'),
				(65, 4, 'زیباشهر (محمدیه)'),
				(66, 4, 'سمیرم'),
				(67, 4, 'شاهین شهر'),
				(68, 4, 'شهرضا'),
				(69, 4, 'فریدن'),
				(70, 4, 'فریدون شهر'),
				(71, 4, 'فلاورجان'),
				(72, 4, 'فولاد شهر'),
				(73, 4, 'قهدریجان'),
				(74, 4, 'کاشان'),
				(75, 4, 'گلپایگان'),
				(76, 4, 'گلدشت اصفهان'),
				(77, 4, 'گلدشت مرکزی'),
				(78, 4, 'مبارکه اصفهان'),
				(79, 4, 'مهاباد-اصفهان'),
				(80, 4, 'نایین'),
				(81, 4, 'نجف آباد'),
				(82, 4, 'نطنز'),
				(83, 4, 'هرند'),
				(84, 5, 'آسارا'),
				(85, 5, 'اشتهارد'),
				(86, 5, 'شهر جدید هشتگرد'),
				(87, 5, 'طالقان'),
				(88, 5, 'کرج'),
				(89, 5, 'گلستان تهران'),
				(90, 5, 'نظرآباد'),
				(91, 5, 'هشتگرد'),
				(92, 6, 'آبدانان'),
				(93, 6, 'ایلام'),
				(94, 6, 'ایوان'),
				(95, 6, 'دره شهر'),
				(96, 6, 'دهلران'),
				(97, 6, 'سرابله'),
				(98, 6, 'شیروان چرداول'),
				(99, 6, 'مهران'),
				(100, 7, 'آبپخش'),
				(101, 7, 'اهرم'),
				(102, 7, 'برازجان'),
				(103, 7, 'بندر دیر'),
				(104, 7, 'بندر دیلم'),
				(105, 7, 'بندر کنگان'),
				(106, 7, 'بندر گناوه'),
				(107, 7, 'بوشهر'),
				(108, 7, 'تنگستان'),
				(109, 7, 'جزیره خارک'),
				(110, 7, 'جم (ولایت)'),
				(111, 7, 'خورموج'),
				(112, 7, 'دشتستان - شبانکاره'),
				(113, 7, 'دلوار'),
				(114, 7, 'عسلویه'),
				(115, 8, 'اسلامشهر'),
				(116, 8, 'بومهن'),
				(117, 8, 'پاکدشت'),
				(118, 8, 'تهران'),
				(119, 8, 'چهاردانگه'),
				(120, 8, 'دماوند'),
				(121, 8, 'رودهن'),
				(122, 8, 'ری'),
				(123, 8, 'شریف آباد'),
				(124, 8, 'شهر رباط کریم'),
				(125, 8, 'شهر شهریار'),
				(126, 8, 'فشم'),
				(127, 8, 'فیروزکوه'),
				(128, 8, 'قدس'),
				(129, 8, 'کهریزک'),
				(130, 8, 'لواسان بزرگ'),
				(131, 8, 'ملارد'),
				(132, 8, 'ورامین'),
				(133, 9, 'اردل'),
				(134, 9, 'بروجن'),
				(135, 9, 'چلگرد (کوهرنگ)'),
				(136, 9, 'سامان'),
				(137, 9, 'شهرکرد'),
				(138, 9, 'فارسان'),
				(139, 9, 'لردگان'),
				(140, 10, 'بشرویه'),
				(141, 10, 'بیرجند'),
				(142, 10, 'خضری'),
				(143, 10, 'خوسف'),
				(144, 10, 'سرایان'),
				(145, 10, 'سربیشه'),
				(146, 10, 'طبس'),
				(147, 10, 'فردوس'),
				(148, 10, 'قائن'),
				(149, 10, 'نهبندان'),
				(150, 11, 'بجستان'),
				(151, 11, 'بردسکن'),
				(152, 11, 'تایباد'),
				(153, 11, 'تربت جام'),
				(154, 11, 'تربت حیدریه'),
				(155, 11, 'جغتای'),
				(156, 11, 'جوین'),
				(157, 11, 'چناران'),
				(158, 11, 'خلیل آباد'),
				(159, 11, 'خواف'),
				(160, 11, 'درگز'),
				(161, 11, 'رشتخوار'),
				(162, 11, 'سبزوار'),
				(163, 11, 'سرخس'),
				(164, 11, 'طبس'),
				(165, 11, 'طرقبه'),
				(166, 11, 'فریمان'),
				(167, 11, 'قوچان'),
				(168, 11, 'کاشمر'),
				(169, 11, 'کلات'),
				(170, 11, 'گناباد'),
				(171, 11, 'مشهد'),
				(172, 11, 'نیشابور'),
				(173, 12, 'آشخانه، مانه و سمرقان'),
				(174, 12, 'اسفراین'),
				(175, 12, 'بجنورد'),
				(176, 12, 'جاجرم'),
				(177, 12, 'شیروان'),
				(178, 12, 'فاروج'),
				(179, 13, 'آبادان'),
				(180, 13, 'امیدیه'),
				(181, 13, 'اندیمشک'),
				(182, 13, 'اهواز'),
				(183, 13, 'ایذه'),
				(184, 13, 'باغ ملک'),
				(185, 13, 'بستان'),
				(186, 13, 'بندر ماهشهر'),
				(187, 13, 'بندرامام خمینی'),
				(188, 13, 'بهبهان'),
				(189, 13, 'خرمشهر'),
				(190, 13, 'دزفول'),
				(191, 13, 'رامشیر'),
				(192, 13, 'رامهرمز'),
				(193, 13, 'سوسنگرد'),
				(194, 13, 'شادگان'),
				(195, 13, 'شوش'),
				(196, 13, 'شوشتر'),
				(197, 13, 'لالی'),
				(198, 13, 'مسجد سلیمان'),
				(199, 13, 'هندیجان'),
				(200, 13, 'هویزه'),
				(201, 14, 'آب بر (طارم)'),
				(202, 14, 'ابهر'),
				(203, 14, 'خرمدره'),
				(204, 14, 'زرین آباد (ایجرود)'),
				(205, 14, 'زنجان'),
				(206, 14, 'قیدار (خدا بنده)'),
				(207, 14, 'ماهنشان'),
				(208, 15, 'ایوانکی'),
				(209, 15, 'بسطام'),
				(210, 15, 'دامغان'),
				(211, 15, 'سرخه'),
				(212, 15, 'سمنان'),
				(213, 15, 'شاهرود'),
				(214, 15, 'شهمیرزاد'),
				(215, 15, 'گرمسار'),
				(216, 15, 'مهدیشهر'),
				(217, 16, 'ایرانشهر'),
				(218, 16, 'چابهار'),
				(219, 16, 'خاش'),
				(220, 16, 'راسک'),
				(221, 16, 'زابل'),
				(222, 16, 'زاهدان'),
				(223, 16, 'سراوان'),
				(224, 16, 'سرباز'),
				(225, 16, 'میرجاوه'),
				(226, 16, 'نیکشهر'),
				(227, 17, 'آباده'),
				(228, 17, 'آباده طشک'),
				(229, 17, 'اردکان'),
				(230, 17, 'ارسنجان'),
				(231, 17, 'استهبان'),
				(232, 17, 'اشکنان'),
				(233, 17, 'اقلید'),
				(234, 17, 'اوز'),
				(235, 17, 'ایج'),
				(236, 17, 'ایزد خواست'),
				(237, 17, 'باب انار'),
				(238, 17, 'بالاده'),
				(239, 17, 'بنارویه'),
				(240, 17, 'بهمن'),
				(241, 17, 'بوانات'),
				(242, 17, 'بیرم'),
				(243, 17, 'بیضا'),
				(244, 17, 'جنت شهر'),
				(245, 17, 'جهرم'),
				(246, 17, 'حاجی آباد-زرین دشت'),
				(247, 17, 'خاوران'),
				(248, 17, 'خرامه'),
				(249, 17, 'خشت'),
				(250, 17, 'خفر'),
				(251, 17, 'خنج'),
				(252, 17, 'خور'),
				(253, 17, 'داراب'),
				(254, 17, 'رونیز علیا'),
				(255, 17, 'زاهدشهر'),
				(256, 17, 'زرقان'),
				(257, 17, 'سده'),
				(258, 17, 'سروستان'),
				(259, 17, 'سعادت شهر'),
				(260, 17, 'سورمق'),
				(261, 17, 'ششده'),
				(262, 17, 'شیراز'),
				(263, 17, 'صغاد'),
				(264, 17, 'صفاشهر'),
				(265, 17, 'علاء مرودشت'),
				(266, 17, 'عنبر'),
				(267, 17, 'فراشبند'),
				(268, 17, 'فسا'),
				(269, 17, 'فیروز آباد'),
				(270, 17, 'قائمیه'),
				(271, 17, 'قادر آباد'),
				(272, 17, 'قطب آباد'),
				(273, 17, 'قیر'),
				(274, 17, 'کازرون'),
				(275, 17, 'کنار تخته'),
				(276, 17, 'گراش'),
				(277, 17, 'لار'),
				(278, 17, 'لامرد'),
				(279, 17, 'لپوئی'),
				(280, 17, 'لطیفی'),
				(281, 17, 'مبارک آباد دیز'),
				(282, 17, 'مرودشت'),
				(283, 17, 'مشکان'),
				(284, 17, 'مصیر'),
				(285, 17, 'مهر فارس(گله دار)'),
				(286, 17, 'میمند'),
				(287, 17, 'نوبندگان'),
				(288, 17, 'نودان'),
				(289, 17, 'نورآباد'),
				(290, 17, 'نی ریز'),
				(291, 17, 'کوار'),
				(292, 18, 'آبیک'),
				(293, 18, 'البرز'),
				(294, 18, 'بوئین زهرا'),
				(295, 18, 'تاکستان'),
				(296, 18, 'قزوین'),
				(297, 18, 'محمود آباد نمونه'),
				(298, 19, 'قم'),
				(299, 20, 'بانه'),
				(300, 20, 'بیجار'),
				(301, 20, 'دهگلان'),
				(302, 20, 'دیواندره'),
				(303, 20, 'سقز'),
				(304, 20, 'سنندج'),
				(305, 20, 'قروه'),
				(306, 20, 'کامیاران'),
				(307, 20, 'مریوان'),
				(308, 21, 'بابک'),
				(309, 21, 'بافت'),
				(310, 21, 'بردسیر'),
				(311, 21, 'بم'),
				(312, 21, 'جیرفت'),
				(313, 21, 'راور'),
				(314, 21, 'رفسنجان'),
				(315, 21, 'زرند'),
				(316, 21, 'سیرجان'),
				(317, 21, 'کرمان'),
				(318, 21, 'کهنوج'),
				(319, 21, 'منوجان'),
				(320, 22, 'اسلام آباد غرب'),
				(321, 22, 'پاوه'),
				(322, 22, 'تازه آباد- ثلاث باباجانی'),
				(323, 22, 'جوانرود'),
				(324, 22, 'سر پل ذهاب'),
				(325, 22, 'سنقر کلیائی'),
				(326, 22, 'صحنه'),
				(327, 22, 'قصر شیرین'),
				(328, 22, 'کرمانشاه'),
				(329, 22, 'کنگاور'),
				(330, 22, 'گیلان غرب'),
				(331, 22, 'هرسین'),
				(332, 23, 'دهدشت'),
				(333, 23, 'دوگنبدان'),
				(334, 23, 'سی سخت- دنا'),
				(335, 23, 'گچساران'),
				(336, 23, 'یاسوج'),
				(337, 24, 'آزاد شهر'),
				(338, 24, 'آق قلا'),
				(339, 24, 'انبارآلوم'),
				(340, 24, 'اینچه برون'),
				(341, 24, 'بندر گز'),
				(342, 24, 'ترکمن'),
				(343, 24, 'جلین'),
				(344, 24, 'خان ببین'),
				(345, 24, 'رامیان'),
				(346, 24, 'سرخس کلاته'),
				(347, 24, 'سیمین شهر'),
				(348, 24, 'علی آباد کتول'),
				(349, 24, 'فاضل آباد'),
				(350, 24, 'کردکوی'),
				(351, 24, 'کلاله'),
				(352, 24, 'گالیکش'),
				(353, 24, 'گرگان'),
				(354, 24, 'گمیش تپه'),
				(355, 24, 'گنبد کاووس'),
				(356, 24, 'مراوه تپه'),
				(357, 24, 'مینو دشت'),
				(358, 24, 'نگین شهر'),
				(359, 24, 'نوده خاندوز'),
				(360, 24, 'نوکنده'),
				(361, 25, 'آستارا'),
				(362, 25, 'آستانه اشرفیه'),
				(363, 25, 'املش'),
				(364, 25, 'بندرانزلی'),
				(365, 25, 'خمام'),
				(366, 25, 'رشت'),
				(367, 25, 'رضوان شهر'),
				(368, 25, 'رود سر'),
				(369, 25, 'رودبار'),
				(370, 25, 'سیاهکل'),
				(371, 25, 'شفت'),
				(372, 25, 'صومعه سرا'),
				(373, 25, 'فومن'),
				(374, 25, 'کلاچای'),
				(375, 25, 'لاهیجان'),
				(376, 25, 'لنگرود'),
				(377, 25, 'لوشان'),
				(378, 25, 'ماسال'),
				(379, 25, 'ماسوله'),
				(380, 25, 'منجیل'),
				(381, 25, 'هشتپر'),
				(382, 26, 'ازنا'),
				(383, 26, 'الشتر'),
				(384, 26, 'الیگودرز'),
				(385, 26, 'بروجرد'),
				(386, 26, 'پلدختر'),
				(387, 26, 'خرم آباد'),
				(388, 26, 'دورود'),
				(389, 26, 'سپید دشت'),
				(390, 26, 'کوهدشت'),
				(391, 26, 'نورآباد (خوزستان)'),
				(392, 27, 'آمل'),
				(393, 27, 'بابل'),
				(394, 27, 'بابلسر'),
				(395, 27, 'بلده'),
				(396, 27, 'بهشهر'),
				(397, 27, 'پل سفید'),
				(398, 27, 'تنکابن'),
				(399, 27, 'جویبار'),
				(400, 27, 'چالوس'),
				(401, 27, 'خرم آباد'),
				(402, 27, 'رامسر'),
				(403, 27, 'رستم کلا'),
				(404, 27, 'ساری'),
				(405, 27, 'سلمانشهر'),
				(406, 27, 'سواد کوه'),
				(407, 27, 'فریدون کنار'),
				(408, 27, 'قائم شهر'),
				(409, 27, 'گلوگاه'),
				(410, 27, 'محمودآباد'),
				(411, 27, 'مرزن آباد'),
				(412, 27, 'نکا'),
				(413, 27, 'نور'),
				(414, 27, 'نوشهر'),
				(415, 28, 'آشتیان'),
				(416, 28, 'اراک'),
				(417, 28, 'تفرش'),
				(418, 28, 'خمین'),
				(419, 28, 'دلیجان'),
				(420, 28, 'ساوه'),
				(421, 28, 'شازند'),
				(422, 28, 'محلات'),
				(423, 28, 'کمیجان'),
				(424, 29, 'ابوموسی'),
				(425, 29, 'انگهران'),
				(426, 29, 'بستک'),
				(427, 29, 'بندر جاسک'),
				(428, 29, 'بندر لنگه'),
				(429, 29, 'بندرعباس'),
				(430, 29, 'پارسیان'),
				(431, 29, 'حاجی آباد'),
				(432, 29, 'دشتی'),
				(433, 29, 'دهبارز (رودان)'),
				(434, 29, 'قشم'),
				(435, 29, 'کیش'),
				(436, 29, 'میناب'),
				(437, 30, 'اسدآباد'),
				(438, 30, 'بهار'),
				(439, 30, 'تویسرکان'),
				(440, 30, 'رزن'),
				(441, 30, 'کبودر اهنگ'),
				(442, 30, 'ملایر'),
				(443, 30, 'نهاوند'),
				(444, 30, 'همدان'),
				(445, 31, 'ابرکوه'),
				(446, 31, 'اردکان'),
				(447, 31, 'اشکذر'),
				(448, 31, 'بافق'),
				(449, 31, 'تفت'),
				(450, 31, 'مهریز'),
				(451, 31, 'میبد'),
				(452, 31, 'هرات'),
				(453, 31, 'یزد');

		");

		return;
	}

}