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
		$result = FALSE;
		$olct_code	= $line[0];
		
		$olct	= $this->join ('olct', 'osbl.olct_idx=olct.idx', 'right')->where ('olct.code', $olct_code)->find ();
		if ($olct != NULL):
			$olct_idx	= $olct[0]->idx;
		
			$osbl	= $this->where ('olct_idx', $olct_idx)->where ('code', $line[1])->find ();
			if ($osbl == NULL):
				$param	= [
					'olct_idx'	=> $olct_idx,
					'code'		=> $line[1],
					'name'		=> $line[2]
				];
				$this->insert ($param);
				$result = $this->getInsertID() > 0;
			else:
				$osbl_idx = $osbl[0]->idx;
			
				$param	= [
					'name'		=> $line[2]
				];
				$this->update($osbl_idx, $param);
			endif;
		endif;
		
		return $result;
	}
}