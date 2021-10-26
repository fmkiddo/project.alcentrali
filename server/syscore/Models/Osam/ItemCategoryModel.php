<?php
namespace App\Models\Osam;


use App\Models\BaseModel;

class ItemCategoryModel extends BaseModel {
	
	protected $table			= 'oaci';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['ci_name', 'ci_dscript'];
	protected $colHeader		= ['Nama', 'Deskripsi'];
}