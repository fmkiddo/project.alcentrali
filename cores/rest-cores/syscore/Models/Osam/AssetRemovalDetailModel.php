<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class AssetRemovalDetailModel extends BaseModel {
	
	protected $table			= 'arv1';
	protected $primaryKey		= 'oarv_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'oarv_idx', 'oita_idx', 'osbl_idx', 'removal_qty', 'created_by', 'updated_by', 'updated_date'
	];
}