<?php

namespace App\Models;


class UserSessionModel extends BaseModel {
	
	protected $table			= 'ossn';
	protected $primaryKey		= 'id';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['id', 'ip_address', 'timestamp', 'data'];
	
	public function generateAuthToken ($length=64) {
		$inLen = $length > 64 ? 64 : $length;
		$randomByte = openssl_random_pseudo_bytes($inLen);
		return bin2hex($randomByte);
	}
}