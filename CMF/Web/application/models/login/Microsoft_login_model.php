<?php

// Getting Your Client ID for Web Authentication 		
//									https://msdn.microsoft.com/en-us/library/bb676626.aspx
//									go.microsoft.com/fwlink/?LinkID=144070
//	Server-side scenarios	https://msdn.microsoft.com/en-us/library/hh243649.aspx
// OAuth 2.0					https://msdn.microsoft.com/en-us/library/hh243647.aspx
// Scopes and permissions 	https://msdn.microsoft.com/en-us/library/hh243646.aspx
// Add "email", "profile" to  Delegated Permissions    

class Microsoft_login_model extends CI_Model
{
	var $client_id = '';
	var $client_secret = '';

	public function __construct()
	{
		parent::__construct();
	}


	public function getAuthenticationUrl($redirect_uri)
	{
		return 'https://login.live.com/oauth20_authorize.srf?client_id='.$this->client_id.'&scope=wl.emails&response_type=code&redirect_uri='.$redirect_uri;
	}

	public function verifyUserAndGetInfo($redirect_uri)
	{
		$code=$this->input->get("code");
		if(!$code)
			return false;

		$auth_url='https://login.live.com/oauth20_token.srf';
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
		
		$url="https://apis.live.net/v5.0/me?access_token=".$access_token;
		$output=file_get_contents($url);
		if(!$output)
			return FALSE;

		$output=json_decode($output, TRUE);
		if(!isset($output['emails']))
			return FALSE;

		foreach($output['emails'] as $email)
			if($email)
				break;

		return array("email"=>$email, "name"=>$output['name']);
	}	

}