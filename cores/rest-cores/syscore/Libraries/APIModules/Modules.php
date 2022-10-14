<?php
namespace App\Libraries\APIModules;


use App\Libraries\ModuleInterface;


abstract class Modules implements ModuleInterface {
	
	private \CodeIgniter\Database\ConnectionInterface $db;
	protected \CodeIgniter\HTTP\RequestInterface $request;
	protected $moduleName = NULL;
	
	public function __construct (
			\CodeIgniter\Database\ConnectionInterface &$db,
			\CodeIgniter\HTTP\RequestInterface $request) {
		$this->db = $db;
		$this->request = $request;
		$this->init();
	}
	
	public function getModuleName(): string {
		return $this->moduleName;
	}
	
	protected function init () { }
	
	/**
	 * 
	 * @param string $formatName
	 * @throws \RuntimeException
	 * @return \App\Libraries\Format\DataFormat
	 */
	protected function initFormat ($formatName): \App\Libraries\Format\DataFormat {
		if ($this->moduleName === NULL) throw new \RuntimeException();
		$formatClass = 'App\Libraries\Format\\' . $this->getModuleName() . '\\' . $formatName . 'Format';
		return new $formatClass ();
	}
	
	/**
	 * 
	 * @param string $modelName
	 * @throws \RuntimeException
	 * @return \App\Models\BaseModel
	 */
	protected function initModel ($modelName) {
		if ($this->moduleName === NULL) throw new \RuntimeException();
		$modelClass = 'App\Models\\' . $this->getModuleName() . '\\' . $modelName;
		return new $modelClass ($this->db);
	}
	
	protected function getDataTransmit () {
		$method = $this->request->getMethod(TRUE);
		if ($method === 'PUT') return $this->request->getJSON (TRUE)['data-transmit'];
		return [];
	}
	
	protected function getClientCode () {
		$authsData	= $this->request->getHeader ('Authorization')->getValue ();
		$authsData	= str_replace ('Basic ', '', $authsData);
		$authsData	= base64_decode ($authsData);
		$authsData	= str_replace (':', '', $authsData);
		$authsData	= base64_decode ($authsData);
		$authsData	= str_replace ('"', '', $authsData);
		$auths		= explode ('#', $authsData);
		return $auths[0];
	}
}
