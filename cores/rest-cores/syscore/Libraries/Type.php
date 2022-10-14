<?php 

namespace App\Libraries;

interface Type {

	function getTypeText ($locale, $key): string;
}
