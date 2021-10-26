<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class AssetMoveOutModel extends BaseModel {
	
	public const DOCCODE		= '01';
	
	protected $table			= 'omvo';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'docnum', 'olct_from', 'olct_to', 'ousr_applicant', 'approved_by', 'approval_date', 'sent_by', 'sent_date', 'received_by', 'received_date', 'status', 'created_by', 'updated_by'
	];
}