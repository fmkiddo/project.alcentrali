<?php
namespace App\Libraries;


class ClientDataGenerator {

	public function __construct() { }
	
	public function generateClientKey ($length = 32) {
		$randKey = openssl_random_pseudo_bytes($length);
		return base64_encode($randKey);
	}
	
	public function generateDbPrefix ($clientApi) {
		$prefix = substr($clientApi, 1, strlen($clientApi) - 1);
		return $prefix . '_';
	}

	public function generateClientCode ($clientName, $clientApi) {
		$clientCode = '';
		if (is_string($clientName)) 
			if (strlen($clientName) > 5 || $clientName != NULL) {
				$cnExplode = explode(' ', $clientName);
				$wordCount = str_word_count($clientName);
				if ($wordCount == 1) $append = strtolower(substr($cnExplode[0], 0, 5));
				else $append = strtolower(substr($cnExplode[0], 0, 3)) . strtolower(substr($cnExplode[1], 0, 2));
				$clientCode = uniqid($clientApi . $append . '_');
			}
		return $clientCode;
	}
	
	public function generateClientDBName ($clientName, $clientApi) {
		$clientDbname = '';
		if (is_string($clientName))
			if (strlen($clientName) > 5 || $clientName != NULL) {
				$cnExplode = explode(' ', $clientName);
				$wordCount = str_word_count($clientName);
				if ($wordCount == 1) $append = strtolower(substr($cnExplode[0], 0, 5));
				else $append = strtolower(substr($cnExplode[0], 0, 3)) . strtolower(substr($cnExplode[1], 0, 2));
				$clientDbname .= $clientApi . '_' . $append . $this->generateID();
			}
		return $clientDbname;
	}
	
	private function generateID ($length=4) {
		$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$ID = '';
		for ($i = 0; $i < $length; $i++) {
			$randomCharacter = $chars[mt_rand(0, strlen($chars) - 1)];
			$ID .= $randomCharacter;
		}
		return $ID;
	}
}