<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class AssetMoveInModel extends BaseModel {
	
	public const DOCCODE		= '02';
	
	protected $table		= 'omvi';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= [
		'docnum', 'docdate', 'omvo_refidx', 'omvo_ousridx', 'omvo_olctfrom', 'omvo_olctto', 'sent', 'sent_by', 'sent_date', 'received_by', 'received_date', 
		'distributed_by', 'distributed_date', 'created_by', 'updated_by', 'updated_date'
	];
}
