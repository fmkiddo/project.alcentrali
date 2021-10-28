<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class AssetMoveOutDetailModel extends BaseModel {
	
	protected $table			= 'mvo1';
	protected $primaryKey		= 'omvo_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['omvo_idx', 'oita_idx', 'olct_idx', 'osbl_idx', 'qty', 'created_by', 'updated_by'];
}