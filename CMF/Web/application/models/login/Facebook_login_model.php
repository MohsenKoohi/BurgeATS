<?php

// After creating in https://developers.facebook.com/apps/
// go to https://developers.facebook.com/tools/explorer
// Graph Api Explorer -> Select your app
// Get Token -> Select "Get Use Access Token"
// On the shown menu select "email" -> Click "Get Access Token"

class Facebook_login_model extends CI_Model
{
	var $client_id = '';
	var $client_secret = '';

	public function __construct()
	{
		parent::__construct();
	}


	public function getAuthenticationUrl($redirect_uri)
	{
		return 'https://www.facebook.com/dialog/oauth?client_id='.$this->client_id."&redirect_uri=".$redirect_uri;
	}

	public function verifyUserAndGetInfo($redirect_uri)
	{
		$auth_url='https://graph.facebook.com/v2.3/oauth/access_token?';
		$auth_url.='client_id='.$this->client_id;
		$auth_url.='&redirect_uri='.$redirect_uri;
		$auth_url.='&client_secret='.$this->client_secret;
		$auth_url.='&code='.$_GET['code'];
	
		$content=@file_get_contents($auth_url);
		if(!$content)
			return false;
		$json_content=json_decode($content);
		if(!isset($json_content->access_token))
			return false;
		$access_token=$json_content->access_token;
		
		$prop_url='https://graph.facebook.com/me?fields=email,name';
		$prop_url.='&access_token='.$access_token;
		
		$content=@file_get_contents($prop_url);
		if(!$content)
			return false;
		$json_prop=json_decode($content,TRUE);
		//bprint_r($json_prop);exit();
		
		if(!isset($json_prop['name']))
			return false;

		return array(
			"email"	=> urldecode($json_prop['email'])
			,"name"	=> $json_prop['name']
		);
	}	

}