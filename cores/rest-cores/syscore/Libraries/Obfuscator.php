<?php
namespace App\Libraries;


class Obfuscator {

	private $method;
	
	public function __construct($method='aes-256-cbc') {
		$this->method = $method;
	}
	
	public function setEncryptionMethod ($method) {
		$this->method = $method;
	}
	
	public function generate_iv ($cipherMethod='aes-256-cbc') {
		$ivlen = openssl_cipher_iv_length($cipherMethod);
		$iv = openssl_random_pseudo_bytes($ivlen);
		return $iv;
	}
	
	public function encrypt ($data, $key, $iv) {
		$ciphertext = openssl_encrypt($data, $this->method, base64_decode($key), $options=OPENSSL_RAW_DATA, $iv);
		return $ciphertext;
	}
	
	public function decrypt ($data, $key, $iv) {
		$plainText = openssl_decrypt($data, $this->method, base64_decode($key), $options=OPENSSL_RAW_DATA, $iv);
		return $plainText;
	}
}