<?php
/**
 * SVN FILE: $Id: Ivy_Ldap.php 18 2008-10-01 11:01:03Z shadowpaktu $
 *
 * Project Name : Project Description
 *
 * @package className
 * @subpackage subclassName
 * @author $Author: shadowpaktu $
 * @copyright $Copyright$
 * @version $Revision: 18 $
 * @lastrevision $Date: 2008-10-01 12:01:03 +0100 (Wed, 01 Oct 2008) $
 * @modifiedby $LastChangedBy: shadowpaktu $
 * @lastmodified $LastChangedDate: 2008-10-01 12:01:03 +0100 (Wed, 01 Oct 2008) $
 * @license $License$
 * @filesource $URL: https://ivy.svn.sourceforge.net/svnroot/ivy/Ivy_Ldap.php $
 */
class Ivy_Ldap
{
	/**
	 * Options array wth server name and domains etc.
	 *
	 * @access private
	 * @var array
	 */
	private $options = array ();
	
	/**
	 * file data.
	 *
	 * @access private
	 * @var array
	 */
	public $fileData = array ();

	/**
	 * Chooses a method appropriate for the file type being parsed.
	 *
	 * @param	string		$file	name of the file to be parsed.
	 */
	public function __construct ($ldapArray)
	{
		
		$this->options = $this->parseServer($ldapArray);
		$this->options['username'] = $ldapArray['username'];
		$this->options['password'] = $ldapArray['password'];
		
	}
	
	
	
	public function getUser ($user = NULL)
	{
		if (!$user) {
			$userArray = explode('\\', $_SERVER['AUTH_USER']);
			$userId = $userArray[1];
			$user['id'] = $userId;
			if (isset($userId[6])) {				
				$user['id'] = rtrim($userId, $userId[6]);
			}			
			$user['domain'] = $userArray[0];
		}
		
		return $this->search($user);
	}
	
	private function search ($user = array ())
	{
	
		foreach ($this->options as $serverId => $server){
			if ($server['domain'] == $user['domain']) {
				$Conn = new COM("ADODB.Connection");
				$Conn->Provider = "ADsDSOObject";
				$Conn->Open($server['dc'], $this->options['username'], $this->options['password']);
				
				$RS = new COM("ADODB.Recordset");		
				$Com = new COM("ADODB.Command"); 
				$Com->ActiveConnection = $Conn;
				$Com->CommandText = "Select givenname,displayname,samaccountname,sn,mail from 'LDAP://" . $server['dc'] . "." . $server['tld'] . "' where SAMAccountName='".$user['id']."'";
				$RS = $Com->Execute; 
				
				while (!$RS->EOF) {
					$displaynameArray = explode(", ", $RS['displayname']);
					$mailArray = explode("@ ", $RS['mail']);
					$result['firstName'] = $displaynameArray[1];
					$result['fullName'] = $displaynameArray[1].' '.(string) $RS['sn'];
					$result['email'] = $mailArray[0];
					$result['lastName'] = (string)$RS['sn']; // convert object to string			
					$result['collar'] = (string) $RS['samaccountname']; // convert object to string			
					$result['adname'] = (string) $RS['displayname'];
					return $result;
				}
			}
		}
	}
	
	private function parseServer ($ldapArray)
	{
		$serverArray = explode(',', $ldapArray['server']);
		foreach ($serverArray as $serverId => $serverData) {
			$nameDomainArray = explode('/', $serverData);
			$nameArray = explode('.', $nameDomainArray[0]);
			
			$result[$serverId]['dc'] = array_shift($nameArray);
			$result[$serverId]['tld'] = implode('.', $nameArray);
			$result[$serverId]['domain'] = $nameDomainArray[1];
		}
		return $result;
	}

}
?>