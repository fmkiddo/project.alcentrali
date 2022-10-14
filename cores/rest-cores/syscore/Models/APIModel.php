<?php
namespace App\Models;


class APIModel extends BaseModel {
	
	protected $table			= 'oapi';
	protected $primaryKey		= 'apicode';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['apicode', 'apiname', 'created_by', 'updated_by'];
}