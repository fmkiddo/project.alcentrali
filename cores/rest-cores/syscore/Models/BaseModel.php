<?php
namespace App\Models;


use CodeIgniter\Model;


abstract class BaseModel extends Model {
	
	protected $createdField		= 'created_at';
	protected $updatedField		= 'updated_at';
	protected $colHeader		= [];
	
	protected function initModelComponents () { }
	
	private function isColHeaderMulti () {
		$isMulti = false;
		foreach ($this->colHeader as $key => $value) {
			if (is_array($value)) {
				$isMulti = true;
				break;
			} else break;
		}
		return $isMulti;
	}
	
	protected function getNow () {
		return date ('Y-m-d H:i:s');
	}
	
	/**
	 * {@inheritDoc}
	 * @see \CodeIgniter\Model::__construct()
	 */
	public function __construct(
			\CodeIgniter\Database\ConnectionInterface &$db = null,
			\CodeIgniter\Validation\ValidationInterface $validation = null) {
		parent::__construct($db, $validation);
		$this->initModelComponents();
	}
	
	public function getColumnHeader ($name='') {
		$ch = $this->colHeader;
		if ($this->isColHeaderMulti()) 
			$ch = array_key_exists($name, $this->colHeader) ? $this->colHeader[$name] : [];
		$colHeader = [];
		foreach ($ch as $th)
			array_push($colHeader, $th);
		return $colHeader;
	}
	
	public function find ($id=null, $singleResult=true) {
		$builder = $this->builder();
		
		if ($this->tempUseSoftDeletes === true) {
			$builder->where($this->table . '.' . $this->deletedField, null);
		}
		
		if (is_array($id)) {
			$row = $builder->whereIn($this->table . '.' . $this->primaryKey, $id)
							->get();
			$row = $row->getResult($this->tempReturnType);
		} elseif (is_numeric($id) || is_string($id)) {
			$row = $builder->where($this->table . '.' . $this->primaryKey, $id)
							->get();
			
			if ($singleResult) $row = $row->getFirstRow($this->tempReturnType);
			else $row = $row->getResult($this->tempReturnType);
		} else {
			$row = $builder->get();
			$row = $row->getResult($this->tempReturnType);
		}
		
		$eventData = $this->trigger('afterFind', ['id' => $id, 'data' => $row]);
		
		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;
		
		return $eventData['data'];
	}

	/**
	 * 
	 * @param array $line line of data from delimited files sent for insertion
	 * @param int $ousr_idx user id data whom transfer the data
	 * @param string string of date
	 * @throws \Exception Exception thrown when the method is not modified on the subclassess
	 * @return boolean return true if a line write is success/false if the write failed
	 */
	public function insertFromFile ($line = array (), $ousr_idx, $timestamps = NULL): bool {
		throw new \Exception('Not supported!');
	}
}