<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class AssetRequisitionModel extends BaseModel {
	
	public const DOCCODE		= '04';
	public const REQTYPENEW		= 1;
	public const REQTYPEEXT		= 2;
	
	protected $table			= 'orqn';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'docnum', 'docdate', 'requisition_type', 'olct_idx', 'ousr_applicant', 'approved_by', 'approval_date', 'status', 'comments', 'created_by', 'updated_by'
	];
}