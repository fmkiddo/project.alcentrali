<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class SublocationModel extends BaseModel {
	
	protected $table			= 'osbl';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['code', 'name'];
	protected $colHeader		= ['Kode', 'Nama'];
}