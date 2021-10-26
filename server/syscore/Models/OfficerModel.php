<?php
namespace App\Models;


class OfficerModel extends BaseModel {
	
	protected $table			= 'ousr';
	protected $primaryKey		= 'username';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['username', 'email', 'phone', 'password', 'created_by', 'updated_by'];
	
	public function findOfficer ($input) {
		$finding = $this->find($input);
		$finding = $finding == NULL ? $this->where ('email', $input)->find () : $finding;
		$finding = $finding == NULL ? $this->where ('phone', $input)->find () : $finding;
		
		$finding = ($finding != NULL && is_array($finding)) ? $finding[0] : $finding;
		
		return $finding;
	}
}