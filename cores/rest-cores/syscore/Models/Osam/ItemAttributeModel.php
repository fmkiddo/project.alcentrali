<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class ItemAttributeModel extends BaseModel {
	
	protected $table			= 'aci1';
	protected $primaryKey		= 'oaci_idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['oaci_idx', 'octa_idx', 'used'];
	
	public function insertFromFile ($line = array (), $ousr_idx, $timestamps = NULL): bool {
		$result = FALSE;
		$ciname = $line[0];
		
		$oaci	= $this->from ('oaci')->where ('oaci.ci_name', $ciname)->find ();
		if (count ($oaci) > 0) {
			$oaci_idx = $oaci[0]->idx;
			
			$ctas	= explode (';', $line[2]);
			$dbParam = [
				'oaci_idx'	=> $oaci_idx,
				'octa_idx'	=> '',
				'used'		=> TRUE
			];
			foreach ($ctas as $cta) {
				$octa	= $this->from ('octa')->where ('octa.attr_name', $cta)->find ();
				if (count ($octa) > 0) {
					$octa_idx	= $octa[0]->idx;
					
					$cta1 = $this->where ('oaci_idx', $oaci_idx)->where ('octa_idx', $octa_idx)->find ();
					if (count ($cta1) == 0) {
						$dbParam['octa_idx'] = $octa_idx;
						$this->insert ($dbParam);
						
						$result = ($this->getInsertID() > 0);
					}
				}
			}
		}
		return $result;
	}
}