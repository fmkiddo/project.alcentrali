<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class SublocationModel extends BaseModel {
	
	protected $table			= 'osbl';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['olct_idx', 'code', 'name'];
	protected $colHeader		= ['Kode', 'Nama'];
	
	public function insertFromFile($line = array (), $ousr_idx, $timestamps=NULL): bool {
		$osbl = $this->join ('olct', 'osbl.olct_idx=olct.idx')->where ('olct.code', $line[0])->where ('osbl.code', $line[1])->find ();
		$olct = $this->from ('olct')->where ('olct.code', $line[0])->find ();
		$result = FALSE;
		
		if (count ($olct) > 0):
			$olct_idx = $olct[0]->idx;
		
			$dbparam = [
				'name'		=> $line[2]
			];
			
			if (count ($osbl) > 0) {
				$osbl_idx = $osbl[0]->idx;
				$result = $this->update ($osbl_idx, $dbparam);
			} else {
				$dbparam['olct_idx'] = $olct_idx;
				$dbparam['code'] = $line[1];
				
				$this->insert ($dbparam);
				$result = ($this->getInsertID() > 0);
			}
		endif;
		
		return $result;
	}
}