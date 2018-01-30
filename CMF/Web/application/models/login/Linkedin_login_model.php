<?php

//1) Create application https://www.linkedin.com/secure/developer?newapp=
//2) Get client_id, client secret
// https://developer.linkedin.com/docs/oauth2
// https://developer.linkedin.com/docs/fields/basic-profile
//Select r_basicprofile, r_emailaddress for Default Application Permissions

class Linkedin_login_model extends CI_Model
{
	var $client_id = '';
	var $client_secret = '';

	public function __construct()
	{
		parent::__construct();
		$this->redirect_uri = get_link("customer_login_linkedin");
	}


	public function getAuthenticationUrl($redirect_uri)
	{
		return 'https://www.linkedin.com/oauth/v2/authorization'
			.'?client_id='.$this->client_id
			.'&scope=r_emailaddress,r_basicprofile'
			.'&response_type=code'
			.'&state='.date("YmdHis").time()
			.'&redirect_uri='.$redirect_uri;
	}

	public function verifyUserAndGetInfo($redirect_uri)
	{
		$code=$this->input->get("code");
		if(!$code)
			return false;

		$auth_url='https://www.linkedin.com/oauth/v2/accessToken';
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

		$access_token= $output['access_token'];
		
		$url="https://api.linkedin.com/v1/people/~:(email-address,formatted-name)?format=json";
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

		$output=json_decode($output, TRUE);
		if(!isset($output['emailAddress']) || !$output['emailAddress'])
			return FALSE;

		return array(
			"email" 		=> $output['emailAddress']
			,"name"		=> $output['formattedName']
		);
	}	

}