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
		
		$oaci	= $this->join ('oaci', 'oaci.idx=aci1.oaci_idx', 'right')->where ('oaci.ci_name', $ciname)->find ();
		if ($oaci != null) {
			$oaci_idx = $oaci[0]->idx;
			
			$ci_attr = explode(';', $line[2]);
			foreach ($ci_attr as $attrib):
				$octa	= $this->join ('octa', 'octa.idx=aci1.octa_idx', 'right')->where ('octa.attr_name', $attrib)->find ();
				if ($octa != NULL):
					$octa_idx = $octa[0]->idx;
					
					$aci1 = $this->where ('oaci_idx', $oaci_idx)->where ('octa_idx', $octa_idx)->find ();
					
					if ($aci1 == NULL):
						$param	= [
							'oaci_idx'	=> $oaci_idx,
							'octa_idx'	=> $octa_idx,
							'used'		=> TRUE
						];
						$this->insert ($param);
						$result = $this->getInsertID() > 0;
					else:
						$aci1_idx = $aci1[0]->idx;
						$param	= [
							'oaci_idx'	=> $oaci_idx,
							'octa_idx'	=> $octa_idx,
							'used'		=> TRUE
						];
						$this->update($aci1_idx, $param);
					endif;
				endif;
			endforeach;
		}
		return $result;
	}
}