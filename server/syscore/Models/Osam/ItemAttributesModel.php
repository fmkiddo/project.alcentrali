<?php

namespace App\Models\Osam;


use App\Models\BaseModel;


class ItemAttributesModel extends BaseModel {
	
	protected $table			= 'ita1';
	protected $primaryKey		= 'oita_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['oita_idx', 'octa_idx', 'attr_value'];
}