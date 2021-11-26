<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class ModuleModel extends BaseModel {
	
	protected $table			= 'omdl';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
}