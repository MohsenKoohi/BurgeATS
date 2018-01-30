<?php

//Follow https://developer.yahoo.com/oauth2/guide/flows_authcode/

class Yahoo_login_model extends CI_Model
{
	var $client_id = '';
	var $client_secret = '';

	public function __construct()
	{
		parent::__construct();
	}


	public function getAuthenticationUrl($redirect_uri)
	{
		return
			'https://api.login.yahoo.com/oauth2/request_auth?'
			.'client_id='.$this->client_id
			.'&response_type=code'
			.'&redirect_uri='.$redirect_uri;
	}

	public function verifyUserAndGetInfo($redirect_uri)
	{
		$code=$this->input->get("code");
		if(!$code)
			return false;

		$auth_url='https://api.login.yahoo.com/oauth2/get_token';
		$in_data=array(
			"client_id"		 	=> $this->client_id
			,"client_secret" 	=> $this->client_secret
			,"redirect_uri"	=> $redirect_uri
			,"code"				=> $code
			,"grant_type"		=>"authorization_code"
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$auth_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($in_data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec ($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);

		if(!$output)
			return FALSE;

		$output=json_decode($output, TRUE);
		if(!isset($output['access_token']))
			return FALSE;

		$access_token = $output['access_token'];
		$guid = $output['xoauth_yahoo_guid'];


		$url="https://social.yahooapis.com/v1/user/".$guid."/profile?format=json";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$access_token ));
		curl_setopt($ch, CURLOPT_POST, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$output = curl_exec ($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);

		if(!$output)
			return FALSE;

		$info=json_decode($output, TRUE);
		$info=$info['profile'];

		if(!isset($info['emails'][0]['handle']))
			return FALSE;

		return array(
			"email"	=> $info['emails'][0]['handle']
			,"name"	=> $info['nickname']
		);
	}	

}