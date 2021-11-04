<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class AssetRemovalModel extends BaseModel{
	
	public const DOCCODE		= '10';
	
	protected $table			= 'oarv';
	protected $primaryKey		= 'docnum';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'docnum', 'docdate', 'ousr_applicant', 'olct_from', 'approved_by', 'approval_date', 'removed_by', 'removal_date', 'removal_method', 'status', 'comments', 'created_by', 'updated_by', 'updated_date'
	];
}