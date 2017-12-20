<?php
/* Holds the MySQl database communication methods
* @param	$instance	object	holds the current and single instance of this object
* @param	$connection	resource	contains the open connection (if any) to a MySQL database
* @param	$queryCount	int		holds the current result set to be returned to the controlling object
* @method	getInstance			returns the current object
* @method	connect				attempts to connect to a MySQL database
* @method	query				attempts to perform a query on the connected database
* @method	disconnect			closes the open connection to the MySQL database
*
* @description		This class follows the Singleton pattarn and as such can't be instatiated directly
* Use method: getInstance to instantiate
*/
class ldap {

	private static $instance;
	private $connection;
	private $queryCount = 0;
	
	
	private $server = '';
	private $username = '';
	private $password = '';
	
	private function __construct ()	{}
	
	/*
	* Always returns the same instace of this object
	* @return	instance		instace of the parent
	*/
	public static function getInstance ()
	{
		if (empty(self::$instance)) {
			self::$instance = new ldap();
		}

		
		return self::$instance;
	}
	
	public function connect ($databaseSpec = array ())
	{
		if (isset($databaseSpec['server'])) {		
			$this->server = $databaseSpec['server'];
			$this->username = $databaseSpec['username'];
			$this->password = $databaseSpec['password'];
		}
		
	}
	public function disconnect () {}
	
	
	public function query ($query)
	{#echo $query . '<br>';
		(array) $result = array ();

		$Conn = new COM("ADODB.Connection");
		$Conn->Provider = "ADsDSOObject";
		$Conn->Open($this->server, $this->username, $this->password);
		
		$RS = new COM("ADODB.Recordset");		
		$Com = new COM("ADODB.Command"); 
		$Com->ActiveConnection = $Conn;

		$Com->CommandText = $query;
		




		try {
			$RS = $Com->Execute;
		} catch (Exception $e) {
			return false;
		}
		

		$i = (int) 0;
		while (!$RS->EOF) {
			foreach ($RS->fields as $key) {
				/* Here we check if an object is returned (in the format: 
				 * CN=xxxxx,CN=xxxxx) and loop over the values (CN=xxxxx) 
				 * then split the key => value pairs and return 
				 */
					
				if (is_object($key->Value)) {				
					foreach ($key->Value as $t) {					
						$y = explode(',', $t);
						$g = ltrim($y[0], 'CN=');
						$result[$i][$key->Name][] = $g;						
					}
				} else {
					$result[$i][$key->Name] = $key->Value;
				}
			}
			$i++;
			
			$RS->MoveNext();
		}

		return $result;
	}

public function pagination ($parameters)
{	
	return false;
}

private function parseServer ($ldapArray)
{
	$serverArray = explode(',', $ldapArray['server']);
	foreach ($serverArray as $serverId => $serverData) {
		$nameDomainArray = explode('/', $serverData);
		$nameArray = explode('.', $nameDomainArray[0]);
		
		$result[$serverId]['dc'] = array_shift($nameArray);
		echo $result[$serverId]['tld'] = implode('.', $nameArray);
		$result[$serverId]['domain'] = $nameDomainArray[1];
	}
	return $result;
}
	
}

?>