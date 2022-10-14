<?php
namespace App\Models;


class LocaleModel extends BaseModel {
	
	protected $table			= 'orsc';
	protected $primaryKey		= 'lang';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['lang', 'dscript', 'created_by', 'updated_by'];
}