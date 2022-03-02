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
		$result		= FALSE;
		$olct		= $this->select ('oita.idx, olct.idx as `olctidx`')->join ('olct', 'oita.olct_idx=olct.idx', 'right')->where ('olct.code', $line[2])->find ();
		
		if ($olct != NULL) {
			$olct_idx	= $olct[0]->olctidx;
			
			$osbl	= $this->select ('oita.idx, osbl.idx as `osblidx`')->join ('osbl', 'oita.osbl_idx=osbl.idx', 'right')
							->where ('osbl.olct_idx', $olct_idx)->where ('osbl.code', $line[3])->find ();
			
			if ($osbl != NULL) {
				$osbl_idx	= $osbl[0]->osblidx;
				$oaci		= $this->select ('oita.idx, oaci.idx as `oaciidx`')->join ('oaci', 'oita.oaci_idx=oaci.idx', 'right')
									->where ('oaci.ci_name', $line[4])->find ();
				
				if ($oaci != NULL) {
					$oaci_idx		= $oaci[0]->oaciidx;
					
					$oast		= $this->select ('oita.idx, oast.idx as `oastidx`')->join ('oast', 'oita.oast_idx=oast.idx', 'right')->where ('oast.name', $line[5])->find ();
					$oast_idx	= ($oast == NULL) ? 1 : $oast[0]->oastidx;
					
					$oita		= $this->where ('oaci_idx', $oaci_idx)->where ('osbl_idx', $osbl_idx)->where ('olct_idx', $olct_idx)->where ('code', $line[0])->find ();
					
					if ($oita == NULL) {
						$insertParam	= [
							'olct_idx'			=> $olct_idx,
							'osbl_idx'			=> $osbl_idx,
							'oaci_idx'			=> $oaci_idx,
							'oast_idx'			=> $oast_idx,
							'code'				=> $line[0],
							'name'				=> $line[1],
							'notes'				=> $line[6],
							'po_number'			=> $line[7],
							'acquisition_value'	=> $line[8],
							'loan_time'			=> $line[9],
							'qty'				=> $line[10]
						];
						$this->insert($insertParam);
						$result	= $this->getInsertID() > 0;
					} else {
						$oita_idx		= $oita[0]->idx;
						$updateParam	= [
							'name'				=> $line[1],
							'notes'				=> $line[6],
							'po_number'			=> $line[7],
							'acquisition_value'	=> $line[8],
							'loan_time'			=> $line[9],
							'qty'				=> $line[10]
						];
						$result = $this->update($oita_idx, $updateParam);
					}
				}
			}
		}
		
		return $result;
	}
}