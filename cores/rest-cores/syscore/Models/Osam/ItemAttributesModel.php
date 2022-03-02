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
		
		$octa		= $this->select ('octa.idx as `octaidx`')->join ('octa', 'ita1.octa_idx=octa.idx', 'right')->where ('octa.attr_name', $line[1])->find ();
		if ($octa != NULL) {
			$oita		= $this->select ('oita.idx as `oitaidx`')->join ('oita', 'ita1.oita_idx=oita.idx', 'right')->where ('oita.code', $line[0])->find ();
			
			if ($oita != NULL) {
				$octa_idx	= $octa[0]->octaidx;
				
				foreach ($oita as $oita_line) {
					$oita_idx	= $oita_line->oitaidx;
					
					$ita1		= $this->where ('octa_idx', $octa_idx)->where ('oita_idx', $oita_idx)->find ();
					
					if ($ita1 == NULL) {
						$insertParam	= [
							'oita_idx'		=> $oita_idx,
							'octa_idx'		=> $octa_idx,
							'attr_value'	=> $line[2]
						];
						$this->insert($insertParam);
						$result = $this->getInsertID() > 0;
					} else {
						$ita1_idx		= $ita1[0]->idx;
						$updateParam	= [
							'attr_value'	=> $line[2]
						];
						$result = $this->update($ita1_idx, $updateParam);
					}
				}
			}
		}
		
		return $result;
	}
}