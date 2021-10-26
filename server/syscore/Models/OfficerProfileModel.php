<?php
namespace App\Models;


class OfficerProfileModel extends BaseModel {
	
	protected $table			= 'usr1';
	protected $primaryKey		= 'usr_id';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['usr_id', 'fullname', 'address1', 'address2', 'created_by', 'updated_by'];
}