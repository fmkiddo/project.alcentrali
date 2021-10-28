<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class UserGroupsModel extends BaseModel {
	
	protected $table			= 'ougr';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['code', 'name'];
	protected $colHeader		= ['Kode', 'Nama'];
}