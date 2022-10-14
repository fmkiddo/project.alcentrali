<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class AssetItemImageModel extends BaseModel {

	protected $table		= 'ita2';
	protected $primaryKey		= 'oita_idx';
	protected $returnType		= '\CodeIgniter\Entity';
	protected $allowedFields	= ['oita_idx', 'image'];
	
	public function insertFromFile ($line = array (), $ousr_idx, $timestamps = NULL): bool {
		$result		= FALSE;
		$barcode	= $line[0];
		$filename	= $line[1];
		
		$oita		= $this->select ('oita.idx')->join ('oita', 'ita2.oita_idx=oita.idx', 'right')->where ('code', $barcode)->find ();
		if ($oita != NULL) {
			$oita_idx	= $oita[0]->idx;
			$ita2		= $this->where ('oita_idx', $oita_idx)->where ('image', $filename)->find ();
			if ($ita2 == NULL) {
				$insertParam	= [
					'oita_idx'	=> $oita_idx,
					'image'		=> $filename
				];
				$this->insert ($insertParam);
				$result	= $this->getInsertID () > 0;
			}
		}
		
		return $result;
	}
}
