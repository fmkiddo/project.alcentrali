<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class AssetMoveOutRequestModel extends BaseModel {
	
	public const DOCCODE		= '03';
	
	protected $table			= 'omvr';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'docnum', 'docdate', 'omvo_refidx', 'olct_from', 'olct_to', 'status', 'created_by', 'updated_by', 'updated_date'
	];
}