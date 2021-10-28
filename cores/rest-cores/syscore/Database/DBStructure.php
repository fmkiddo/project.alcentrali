<?php
namespace App\Database;


class DBStructure {
	
	protected $prefix;
	protected $tables;
	protected $tableStructures;
	
	public function dbPrefix (): string {
		return $this->prefix;
	}
	
	public function getTables (): array {
		return $this->tables;
	}
	
	public function getTableStructures ($tableName): array {
		return $this->tableStructures[$tableName];
	}
	
	public function setDbPrefix ($newPrefix) {
		if (!ctype_space($newPrefix)) $this->prefix = $newPrefix;
	}
}