<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class CategoryAttributeDataModel extends BaseModel {
	
	protected $table			= 'cta1';
	protected $primaryKey		= 'octa_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['octa_idx', 'octa_value'];
	
	public function insertFromFile($line = array (), $ousr_idx,  $timestamps = NULL): bool {
		$result = FALSE;
		$octa	= $this->db->table ('octa')->getWhere (['attr_name' => $line[0]])->getResult ();
		if (count ($octa) > 0) {
			$octa_idx	= $octa[0]->idx;
			$octa_type	= $octa[0]->attr_type;
			
			if ($octa_type === 'prepopulated-list') {
				$values = explode (',', str_replace ('"', '', $line[2]));
				$param = [
					'octa_idx'		=> $octa_idx,
					'octa_value'	=> ''
				];
				foreach ($values as $value) {
					$param['octa_value'] = $value;
					$this->insert ($param);
					
					$result = ($this->getInsertID() > 0);
				}
			}
		}
		return $result;
	}
}