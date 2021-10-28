<?php
namespace App\Models;


class ClientProfileModel extends BaseModel {
	
	protected $table			= 'cac2';
	protected $primaryKey		= 'cac_id';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['cac_id', 'clientname', 'address', 'taxno', 'picname', 'picemail', 'picphone', 'created_by', 'updated_by'];
}