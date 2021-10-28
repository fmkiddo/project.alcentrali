<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class ItemAttributeModel extends BaseModel {
	
	protected $table			= 'aci1';
	protected $primaryKey		= 'oaci_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['oaci_idx', 'octa_idx', 'used'];
	
}