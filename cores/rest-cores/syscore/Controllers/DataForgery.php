<?php
namespace App\Controllers;


class DataForgery extends BaseRESTController {
	
	private $forger;
	private \App\Database\DBStructure $dataStructures;
	
	private function instatiateStructure ($structName) {
		$structureClass = 'App\Database\Structures\\' . ucfirst($structName);
		$this->dataStructures = new $structureClass ();
	}
	
	private function createDatabaseUser ($forger, $dataParams) {
		$hosts = [
			'localhost',
			'127.0.0.1',
			'::1'
		];
		
		$db = $forger->getConnection();
		$result = 0;
		foreach ($hosts as $host) {
			$newUserQuery	= "CREATE USER '{$dataParams['dbuser']}'@'{$host}' IDENTIFIED BY '{$dataParams['dbpswd']}'";
			$result = $db->simpleQuery($newUserQuery);
			
			$grantUserQuery	= "GRANT ALL PRIVILEGES ON {$dataParams['dbname']}.* TO '{$dataParams['dbuser']}'@'{$host}'";
			$db->simpleQuery($grantUserQuery);
		}
		return $result;
	}
	
	private function initiateForger ($dataParams) {
		$config = [
			'DSN'      => '',
			'hostname' => 'localhost',
			'username' => $dataParams['dbuser'],
			'password' => $dataParams['dbpswd'],
			'database' => $dataParams['dbname'],
			'DBDriver' => 'MySQLi',
			'DBPrefix' => $this->dataStructures->dbPrefix (),
			'pConnect' => false,
			'DBDebug'  => (ENVIRONMENT !== 'production'),
			'charset'  => 'utf8',
			'DBCollat' => 'utf8_bin',
			'swapPre'  => '',
			'encrypt'  => false,
			'compress' => false,
			'strictOn' => false,
			'failover' => [],
			'port'     => 3306
		];
		$this->forger = \Config\Database::forge($config);
	}
	
	private function initiateDatabaseForgery () {
		$tables = $this->dataStructures->getTables ();
		foreach ($tables as $table) {
			$tableStructures = $this->dataStructures->getTableStructures ($table);
			$pk			= $tableStructures['key'];
			$structure	= $tableStructures['struct'];
			$this->forger->addField($structure);
			$this->forger->addPrimaryKey($pk);
			$this->forger->createTable($table, TRUE);
		}
	}
	
	public function start_forgery () {
		$dataParams = $this->request->getJSON(TRUE);
		$this->instatiateStructure($dataParams['client-api']);
		$forger = \Config\Database::forge();
		$dbforged = $forger->createDatabase($dataParams['dbname'], TRUE);
		if (!$dbforged)
			$result 	= [
				'status'	=> 500,
				'message'	=> 'Create DB Error! Database forgery failed!'
			];
		else {
// 			$dbforged = $this->createDatabaseUser($forger, $dataParams);
// 			if (!$dbforged)
// 				$result		= [
// 					'status'	=> 500,
// 					'message'	=> 'Create DB User Error! Database forgery failed!'
// 				];
// 			else {
// 				if (array_key_exists('dbprefix', $dataParams)) $this->dataStructures->setDbPrefix ($dataParams['dbprefix']);
// 				$result		= [
// 					'status'	=> 500,
// 					'message'	=> 'Operation not available at this time'
// 				];
// 				$this->initiateForger($dataParams);
// 				$this->initiateDatabaseForgery();
// 			}
		}
		return $this->respond($result);
	}
}