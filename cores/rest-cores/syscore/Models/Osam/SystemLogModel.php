<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class SystemLogModel extends BaseModel {
	
	protected $table		= 'olog';
	protected $primaryKey		= 'lineid';
	protected $returnType		= '\CodeIgniter\Entity';
	protected $allowedFields	= ['ip_address', 'ousr_idx', 'activity'];
}
