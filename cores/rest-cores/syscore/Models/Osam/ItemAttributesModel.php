<?php

namespace App\Models\Osam;


use App\Models\BaseModel;


class ItemAttributesModel extends BaseModel {
	
	protected $table			= 'ita1';
	protected $primaryKey		= 'oita_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['oita_idx', 'octa_idx', 'attr_value'];
	
	public function insertFromFile ($line = array (), $ousr_idx, $timestamps = NULL): bool {
		$result = FALSE;
		
		$oita	= $this->from ('oita')->where ('oita.code', $line[0])->find ();
		if (count ($oita) > 0) {
			$oita_idx	= $oita[0]->idx;
			
			$octa	= $this->from ('octa')->where ('octa.attr_name', $line[1])->find ();
			
			if (count ($octa) > 0) {
				$octa_idx	= $octa[0]->idx;
				$ita1	= $this->where ('oita_idx', $oita_idx)->where ('octa_idx', $octa_idx)->find ();
				
				$dbParam	= [
					'attr_value'	=> $line[2]
				];
				if (count ($ita1) > 0) {
					$ita1_idx	= $ita[0]->idx;
					$result		= $this->update ($ita1_idx, $dbParam);
				} else {
					$dbParam['oita_idx']	= $oita_idx;
					$dbParam['octa_idx']	= $octa_idx;
					$this->insert ($dbParam);
					$result = ($this->getInsertID() > 0);
				}
			}
		}
		
		return $result;
	}
}