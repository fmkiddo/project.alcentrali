<?php
namespace App\Libraries\Format;

/**
 * 
 * DataFormat abstract class was a base class for the additional file data format classes
 * addedd to the library, for templating format comparisons.
 * 
 * @author fmkiddo
 * @license GNU General Public License 3.0
 *
 */
abstract class DataFormat {
	
	protected $dataHeader = array ();
	
	private function isDataHeaderSet (): bool {
		return count ($this->dataHeader) > 0;
	}
	
	/**
	 * 
	 * @param array $inputHeader input reference from uploaded csv files
	 * @throws \ErrorException function throws exception if programmer not yet set internal data header variable
	 * @return bool return true if header partially or complete match
	 * 
	 */
	public function headerCompare ($inputHeader = array ()): bool {
		if (!$this->isDataHeaderSet())
			throw new \ErrorException ('Unset data header variables', 1000, NO_DATA_HEADER_SET);
		else {
			$isMatched = true;
			for ($id = 0; $id < count ($this->dataHeader); $id++) 
				if ($this->dataHeader[$id] !== $inputHeader[$id]) {
					$isMatched = false;
					break;
				}
			return $isMatched;
		}
	}
}