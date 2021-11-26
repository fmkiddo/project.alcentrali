<?php
namespace App\Models\Osam;


use App\Models\BaseModel;

class AssetItemModel extends BaseModel {
	
	protected $table			= 'oita';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['olct_idx', 'osbl_idx', 'oaci_idx', 'oast_idx', 'code', 'name', 'notes', 'po_number', 'acquisition_value', 'loan_time', 'qty'];
	protected $colHeader		= [
		'locationassets'	=> ['Kode', 'Nama', 'Sublokasi', 'Kategori', 'Notes', 'No. PO', 'Qty'],
		'mainassets'		=> ['Kode', 'Nama', 'Jumlah']
	];
	
	public function insertFromFile ($line = array (), $ousr_idx, $timestamps = NULL): bool {
		$result = FALSE;
		$olct	= $this->from ('olct')->where ('olct.code', $line[2])->find ();
		if (count ($olct) > 0) {
			$olct_idx	= $olct[0]->idx;
			$osbl		= $this->from ('osbl')->where ('osbl.code', $line[3])->find ();
			if (count ($osbl) > 0) {
				$osbl_idx	= $osbl[0]->idx;
				$oaci		= $this->from ('oaci')->where ('oaci.ci_name', $line[4])->find ();
				
				if (count ($oaci) > 0) {
					$oaci_idx	= $oaci[0]->idx;
					
					$oita		= $this->where ('code', $line[0])->where ('olct_idx', $olct_idx)->where ('osbl_idx', $osbl_idx)->find ();
					
					$dbParam	= [
						'oaci_idx'			=> $oaci_idx,
						'oast_idx'			=> 1,
						'name'				=> $line[1],
						'notes'				=> $line[6],
						'po_number'			=> $line[7],
						'acquisition_value'	=> $line[8],
						'loan_time'			=> $line[9],
						'qty'				=> $line[10]
					];
					
					if (count ($oita) > 0) {
						$oita_idx	= $oita[0]->idx;
						$result = $this->update ($oita_idx, $dbParam);
					} else {
						$dbParam['code']		= $line[0];
						$dbParam['olct_idx']	= $olct_idx;
						$dbParam['osbl_idx']	= $osbl_idx;
						
						$this->insert ($dbParam);
						$result = ($this->getInsertID() > 0);
					}
				}
			}
		}
		return $result;
	}
}