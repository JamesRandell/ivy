<?php
/**
 * Curl module for Ivy
 *
 * @author James Randell
 * @version 0.1
 * @package CURL
 */
class Ivy_Curl
{

/**
 * Holds a value to be used as a valid proxy user
 * 
 * @access	public
 * @var		string
 */
public $username = '';

/**
 * The password that goes with the usename used for the proxy
 * 
 * @access	public
 * @var		string
 */
public $password = '';

/**
 * The URI used for the CURL request
 * 
 * @access	public
 * @var		string
 */
public $uri = '';

/**
 * Default user agent
 * 
 * @access	public
 * @var		string
 */
public $userAgent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
	
/**
 * Authentication method
 * 
 * @access	public
 * @var		string
 */
public $authentication = CURLAUTH_ANY;

/**
 * Seconds to spend wiating for a response, 0 is infinate
 * 
 * @access	public
 * @var		int
 */
public $timeout = 0;

/**
 * Proxy URI to use
 * 
 * @access	public
 * @var		string
 */
public $proxy = '';
	

public $data;
	
/**
 * Empty constructor
 * 
 * @access	public
 * @return	void
 */
public function __construct ()	{}

/**
 * Queries a URI and retrieves the result.
 * 
 * The result could be Html, XML - anything that can be accessed fro ma valid
 * URI.
 * 
 * @access	public
 * @return	array|bool
 * @param	string	$uri	The URI to use. If null then it uses the default
 */
public function select ($uri = null)
{
	(object) $ch = curl_init(); 
	(string) $this->uri = (isset($uri) ? $uri : $this->uri);

       curl_setopt($ch, CURLOPT_URL, $this->uri); 
	curl_setopt($ch, CURLOPT_VERBOSE, 1);   
	curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
	
	/*
	 * set to TRUE so result is returned as stirng and not sent to browser
	 */
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if ($this->proxy) {
		curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
		curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
		curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->username.':'.$this->password);
	}
	
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	
	curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);		
	curl_setopt($ch, CURLOPT_HTTPAUTH, $this->authentication);
	curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);

	$result = curl_exec($ch);
	curl_close($ch);

	$this->data = $result;
	
	return $result;	
}

}
?>