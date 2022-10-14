<?php
namespace App\Models\Osam;

use App\Models\BaseModel;

class CategoryAttributeModel extends BaseModel {
	
	private $attrType = [
		'text'				=> ['teks', 'text'],
		'date'				=> ['tanggal', 'date'],
		'list'				=> ['daftar', 'list'],
		'prepopulated-list'	=> ['pra-daftar', 'daftar-pra-populasi', 'daftarprapopulasi', 'daftar-prapopulasi', 'prepopulated-list', 'prepopulatedlist', 'pre-populated-list']
	];
	
	protected $table			= 'octa';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['attr_name', 'attr_type'];
	
	private function getAttributeType ($text) {
		$attr_type = '';
		foreach ($this->attrType as $key => $textvalue) {
			foreach ($textvalue as $value) 
				if (strtolower($text) === $value) {
					$attr_type = $key;
					break;
				}
			if ($attr_type !== '') break;
		}
		return $attr_type;
	}
	
	public function insertFromFile ($line = array (), $ousr_idx, $timestamps = NULL): bool {
		$result = FALSE;
		$octa = $this->where ('attr_name', $line[0])->find ();
		$dbParam = [
			'attr_name'	=> $line[0],
			'attr_type'	=> $this->getAttributeType($line[1])
		];
		
		if (count ($octa) == 0 && $dbParam['attr_type'] !== ''	) {
			$this->insert ($dbParam);
			$result = ($this->getInsertID() > 0);
		}
		
		return $result;
	}
}