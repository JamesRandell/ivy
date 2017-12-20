<?php
/**
 * MIME compatible email class
 *
 * @package    Ivy_Mail
 * @author James Randell <james.randell@hotmail.co.uk>
 * @version 1.0
 */
class Ivy_Mail {

/**
 * The PHP Mailer object
 *
 * @access private
 * @var array
 */
private $mail = object;

public $host = '';
public $port = 25;
public $smtpUser = '';
public $smtpPassword = '';
public $debug = 0;
public $from = '';
public $fromName = '';
public $subject = '';
public $body = '';
public $content = '';
private $to = array ();

public $stylesheet = '';

/**
 * Contains a copy of the Ivy_View object
 */
public $view;




/**
 * Ivy_Mail now uses PHP Mailer (https://github.com/PHPMailer/PHPMailer/blob/master/examples/smtp.phps) for sending SMTP mail
 * 
 * @datemodified	James Randell <jamesrandell@me.com>
 * @access			public
 * @return			void
 */
public function __construct ($result = null) {

	if (!$registry) {
		$registry = Ivy_Registry::getInstance();
	}

	$this->view = new Ivy_View();
	$this->view->output = false;
	
	/**
	 * Look for Mail config settings in the registry first and assign them if they exist
	 */
	if (isset($registry->system['keys']['mail'])) {
		$this->host = $registry->system['keys']['mail']['host'];
		$this->smtpUser = $registry->system['keys']['mail']['smtpUser'];
		$this->smtpPassword = $registry->system['keys']['mail']['smtpPassword'];
		$this->from = $registry->system['keys']['mail']['from'];
		$this->fromName = $registry->system['keys']['mail']['fromName'];
	}
	

	/**
	 * Load the PHP Mailer autoload class
	 */
	require 'core/system/PHPMailer/PHPMailerAutoload.php';

	$this->mail = new PHPMailer; 
	
	/**
	 * set up some defaults
	 */
	$this->mail->isSMTP(); 
	$this->mail->SMTPAuth = true; 

	// 0 = off (for production use) 
	// 1 = client messages 
	// 2 = client and server messages 
	$this->mail->SMTPDebug = 0; 
	$this->mail->Debugoutput = 'html'; 
}

public function subject ($subject) {
	$this->mail->Subject = $subject; 
}
public function content ($content) {
	return $this->body($content);
}

public function body ($body) {
	$this->mail->Body = $body;
	$this->mail->AltBody = $body;
}

public function sender ($email, $name) {
	return $this->from($email, $name);
}
public function from ($email, $name) {
	$this->from = $email;
	$this->fromName = $name;
	$this->mail->addReplyTo($email, $name);
}

public function reply ($email, $name) {
	$this->from = $email;
	$this->fromName = $name;
	
	$this->mail->setFrom($email, $name);
}

public function to ($email, $name) {
	return $this->recipient($email, $name);
}
public function recipient ($email, $name) {
	$this->to[] = array($email, $name);
	$this->mail->AddAddress(htmlspecialchars_decode($email), $name);
}

	
/**
 * sends the e-mail along with the header
 * 
 * @access	public
 * @return	void
 */
public function send () {

	// run checks to see if certain config values are present
	if ($this->_checkConfig() === false) {
		trigger_error('There was a problem!');
		die;
	}

	/**
	 * See if we have a template to use instead of body/content
	 */
	if (strlen($this->stylesheet) > 0) {
		$this->content = $this->mail->Body = $this->mail->AltBody = $this->view->build($this->stylesheet, $this->globalstylesheet);
	}

	//send the message, check for errors 
	if (!$this->mail->send()) { 
		echo "Mailer Error: " . $this->mail->ErrorInfo; 
		return false;
	}

	return;
}


/**
 * Checks for the existance of certain config values required to send emails
 * 
 * @access	private
 * @return	void
 */
private function _checkConfig () {

	(string) $error = '';

	if (strlen($this->host) == 0) {
		$error .= 'Ivy_Mail: No host<br />';
	} else {
		$this->mail->Host = $this->host; 
	}

	if (strlen($this->port) == 0) {
		$error .= 'Ivy_Mail: No port<br />';
	} else {
		$this->mail->Port = $this->port; 
	}

	if (strlen($this->smtpUser) == 0) {
		$error .= 'Ivy_Mail: No smtpUser<br />';
	} else {
		$this->mail->Username = $this->smtpUser;
	}

	if (strlen($this->smtpPassword) == 0) {
		$error .= 'Ivy_Mail: No smtpPassword<br />';
	} else {
		$this->mail->Password = $this->smtpPassword;
	}

	if (strlen($this->from) == 0) {
		$error .= 'Ivy_Mail: No From address<br />';
	} else {
		$this->mail->From = $this->from;
	}

	if (strlen($this->fromName) == 0) {
		$error .= 'Ivy_Mail: No From name<br />';
	} else {
		$this->mail->FromName = $this->fromName;
	}

	if (count($this->to) == 0) {
		$error .= 'Ivy_Mail: No recipients<br />';
		return false;
	}


	if (strlen($error) > 0) {
		echo $error;
		return false;
	}

	return true;
}

/**
 * changes the sender to show HTML friendly name on the email
 * 
 * @access	private
 * @return	void
 */
private function parseSender () {
	/**
	 * holds a local copy of the sender array once split up
	 */
	(array) $array = array ();
	
	/**
	 * email address of the sender
	 */
	(string) $mail = '';
	
	/**
	 * name of the sender
	 */
	(string) $name = '';
	
	
	$array = explode('<', $this->sender);
	
	if (!isset($array[1])) {
		$this->name = $array[0];
		return;
	}

	
	$this->name = trim(trim($array[0]), '"');
	echo $this->sender = "<" . $array[1];
}

}
?>