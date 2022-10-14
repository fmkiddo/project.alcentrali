<?php
namespace App\Libraries\Osam;

use App\Libraries\Type;

class DocumentStatus implements Type {

	private $documentStatus	= [
	];
	
	function getStatusText ($locale, $key): string {
		return '';
	}
}
