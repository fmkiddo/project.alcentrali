<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class ApplicationSettingsModel extends BaseModel {
	const RESET = [
		'weekly'	=> [
			'id'	=> 0,
			'text'	=> 'Weekly'
		],
		'monthly'	=> [
			'id'	=> 1,
			'text'	=> 'Monthly'
		],
		'yearly'	=> [
			'id'	=> 2,
			'text'	=> 'Yearly'
		]
	];
	
	protected $table		= 'oset';
	protected $primaryKey		= 'tag_name';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['tag_name', 'tag_value', 'created_by', 'updated_by'];
}
