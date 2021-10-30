<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class AssetMoveInDetailModel extends BaseModel {
	
	protected $table			= 'mvi1';
	protected $primaryKey		= 'omvi_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['omvi_idx', 'oita_fromidx', 'oita_idx', 'olct_idx', 'osbl_idx', 'qty', 'created_by', 'updated_by', 'updated_date'];
}