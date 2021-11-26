<?php
namespace App\Models\Osam;


use App\Models\BaseModel;

class ItemCategoryModel extends BaseModel {
	
	protected $table			= 'oaci';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['ci_name', 'ci_dscript'];
	protected $colHeader		= ['Nama', 'Deskripsi'];
	
	public function insertFromFile ($line = array (), $ousr_idx, $timestamps = NULL): bool {
		$result = FALSE;
		$oaci	= $this->where ('ci_name', $line[0])->find ();
		
		$dbParam = [
			'ci_dscript'	=> $line[1]
		];
		
		if (count ($oaci) > 0) {
			$oaci_idx = $oaci[0]->idx;
			$result = $this->update ($oaci_idx, $dbParam);
		} else {
			$dbParam['ci_name']	= $line[0];
			
			$this->insert ($dbParam);
			$result = ($this->getInsertID() > 0);
		}
		return $result;
	}
}