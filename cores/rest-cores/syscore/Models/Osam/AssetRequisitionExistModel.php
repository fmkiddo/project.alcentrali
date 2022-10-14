<?php
namespace App\Models\Osam;


use  App\Models\BaseModel;


class AssetRequisitionExistModel extends BaseModel {
	
	protected $table			= 'rqn1';
	protected $primaryKey		= 'orqn_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'orqn_idx', 'code', 'qty', 'remarks', 'created_by', 'updated_by', 'updated_date'
	];
}
