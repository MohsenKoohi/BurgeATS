<?php

//https://console.developers.google.com/
//https://developers.google.com/identity/protocols/OAuth2
//https://developers.google.com/identity/protocols/googlescopes

class Google_login_model extends CI_Model
{
	var $client_id = '';
	var $client_secret = '';

	public function __construct()
	{
		parent::__construct();

		require_once "google/autoload.php";
	}


	public function getAuthenticationUrl($redirect_uri)
	{
		$client = new Google_Client();
		$client->setClientId($this->client_id);
		$client->setClientSecret($this->client_secret);
		$client->setRedirectUri($redirect_uri);
		$client->addScope("openid email profile");
		$authUrl = $client->createAuthUrl();
		
		return $authUrl;
	}

	public function verifyUserAndGetInfo($redirect_uri)
	{
		$client = new Google_Client();
		$client->setClientId($this->client_id);
		$client->setClientSecret($this->client_secret);
		$client->setRedirectUri($redirect_uri);
		try
		{
			 $client->authenticate($_GET['code']);
			 $google_oauth =new Google_Service_Oauth2($client);
			 $info = $google_oauth->userinfo->get();
			 
			 $ret=array(
			 	"email"		=> $info ->email
			 	,"name"		=> $info ->name
			 );
			 
			 return $ret;
		}
		catch(Exception $e)
		{
		 	//echo $e->getMessage();
		}

		return false;
	}
	

}


