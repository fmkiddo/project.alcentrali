<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class AssetRequisitionNewModel extends BaseModel {
	
	protected $table			= 'rqn2';
	protected $primaryKey		= 'orqn_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'orqn_idx', 'name', 'dscript', 'est_value', 'qty', 'imgs', 'created_by', 'updated_by'
	];
}