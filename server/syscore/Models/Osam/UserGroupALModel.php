<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class UserGroupALModel extends BaseModel {
	
	protected $table			= 'ugr1';
	protected $primaryKey		= 'ougr_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['ougr_idx', 'privilege'];
}