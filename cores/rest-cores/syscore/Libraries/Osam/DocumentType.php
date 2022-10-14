<?php 
namespace App\Libraries\Osam;

use App\Libraries\Type;

abstract class DocumentType implements Type {

	protected $documentType	= [];
	
	function getTypeText ($locale, $key): string {
		return $this->documentType[$key][$locale];
	}
}
