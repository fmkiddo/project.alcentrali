<?php
namespace App\Models\Osam;


use App\Models\BaseModel;

class CategoryAttributeModel extends BaseModel {
	
	protected $table			= 'octa';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['attr_name', 'attr_type'];
}