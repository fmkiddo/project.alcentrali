<?php
namespace App\Libraries;


interface ModuleInterface {
	
	function executeRequest ($trigger): array;
	
	function serverRequest ($trigger): array;
	
	function getModuleName (): string;
}