<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class EnduserLocationModel extends BaseModel {
	
	protected $table			= 'usr1`';
	protected $primaryKey		= 'ousr_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['ousr_idx', 'olct_idx', 'status', 'created_by', 'updated_by', 'updated_date'];
}