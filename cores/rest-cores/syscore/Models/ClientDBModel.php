<?php
namespace App\Models;


class ClientDBModel extends BaseModel {
	
	protected $table			= 'cac1';
	protected $primaryKey		= 'cac_id';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['cac_id', 'dbname', 'dbuser', 'dbpswd', 'dbprefix', 'created_by', 'updated_by'];
}