<?php
namespace App\Models;


class ClientModel extends BaseModel {
	
	protected $table			= 'ocac';
	protected $primaryKey		= 'clientcode';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['clientcode', 'clientpasscode', 'clientkey', 'clientapi', 'created_by', 'updated_by'];
}