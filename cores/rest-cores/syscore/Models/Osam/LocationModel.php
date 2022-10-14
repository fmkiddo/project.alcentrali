<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class LocationModel extends BaseModel {
	
	protected $table			= 'olct';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['code', 'name', 'phone', 'address', 'contact_person', 'email', 'notes', 'created_by', 'updated_by', 'updated_date'];
	protected $colHeader		= ['Kode', 'Nama', 'Telepon', 'Alamat', 'PIC', 'Email', 'Catatan'];
	
	public function insertFromFile($line = array (), $ousr_idx, $timestamps=NULL): bool {
		$olct = $this->where ('code', $line[0])->find ();
		$stamps = ($timestamps === NULL) ? $this->getNow() : $timestamps;
		$result = FALSE;
		
		$dbParam = [
			'name'				=> $line[1],
			'phone'				=> $line[2],
			'address'			=> $line[3],
			'contact_person'	=> $line[4],
			'email'				=> $line[5],
			'notes'				=> $line[6],
			'updated_by'		=> $ousr_idx,
			'updated_date'		=> $stamps
		];
		
		if (count ($olct) > 0) {
			$olct_idx = $olct[0]->idx;
			$result = $this->update ($olct_idx, $dbParam);
		} else {
			$dbParam['code']		= $line[0];
			$dbParam['created_by']	= $ousr_idx;
			
			$this->insert ($dbParam);
			$result = ($this->getInsertID() > 0);
		}
		return $result;
	}
}