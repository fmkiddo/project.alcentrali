<?php
namespace App\Models;


class TextResModel extends BaseModel {
	
	protected $table			= 'rsc1';
	protected $primaryKey		= 'code';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['rsc_id', 'code', 'content', 'created_by', 'updated_by'];
}