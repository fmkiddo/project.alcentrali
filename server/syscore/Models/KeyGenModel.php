<?php
namespace App\Models;


class KeyGenModel extends BaseModel {
	
	const ALPHANUMERIC	= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*:;,.<>-+';
	
	protected $table			= 'ogtk';
	protected $primaryKey		= 'key_generated';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['id', 'time_generated', 'key_generated', 'expired'];
	
	public function invalidateToken ($token) {
		$update = [
			'expired' => TRUE
		];
		$this->update($token, $update);
	}
	
	public function validateToken ($token, $expiry): int {
		$retCode;
		$ogtk = $this->find ($token);
		if ($ogtk == NULL) $retCode = 0;
		elseif (!$ogtk->expired) {
			$now = time();
			$time_generated = $ogtk->time_generated;
			$timeskip = $now - $time_generated;
			if ($timeskip <= $expiry) $retCode = 1;
			else {
				$retCode = -1;
				$updated = [
					'expired' => TRUE
				];
				$this->update($time_generated, $updated);
			}
		} else $retCode = -1;
		return $retCode;
	}
	
	public function generateToken ($length) {
		$randomBytes = openssl_random_pseudo_bytes($length);
		$token = bin2hex($randomBytes);
		$data = [
			'id'				=> uniqid(),
			'time_generated'	=> time(),
			'key_generated'		=> $token
		];
		$this->insert($data);
		return $token;
	}
	
	public function generateEncryptionKey ($length=32): string {
		$randKey = openssl_random_pseudo_bytes($length);
		return base64_encode($randKey);
	}
	
	public function generateRandomAppKey ($keyLength): string {
		$maxNumber = strlen(KeyGenModel::ALPHANUMERIC) - 1;
		$randomKey = '';
		for ($i=0; $i<$keyLength; $i++) {
			$randNum	= random_int(0, $maxNumber);
			$selected	= KeyGenModel::ALPHANUMERIC[$randNum];
			$randomKey .= $selected;
		}
		return $randomKey;
	}
}