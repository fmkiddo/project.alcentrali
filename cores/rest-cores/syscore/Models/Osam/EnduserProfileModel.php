<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class EnduserProfileModel extends BaseModel {
	
	protected $table			= 'usr3';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'idx', 'fname', 'mname', 'lname', 'address1', 'address2', 'phone', 'email', 'created_by', 'updated_by', 'updated_date'
	];
}