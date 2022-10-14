<?php
namespace App\Libraries;

class EmailTools {

	private $emailTools;

	private function __construct () {
		$this->emailTools = \Config\Services::email ();
	}
	
	public function emailNotifTools ($to, $subject, $message, $from='', $name='') {
		$this->emailTools->setFrom ($from, $name);
		$this->emailTools->setTo ($to);
		$this->emailTools->setSubject ($subject);
		$this->emailTools->setMessage ($message);
	}
	
	public function emailSend ($autoClear=TRUE) {
		$this->emailTools->send ($autoClear);
	}
	
	public static function init () {
		return new EmailTools ();
	}
}
