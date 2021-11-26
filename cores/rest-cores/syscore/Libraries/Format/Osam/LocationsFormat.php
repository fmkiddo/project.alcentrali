<?php
namespace App\Libraries\Format\Osam;

use App\Libraries\Format\DataFormat;

class LocationsFormat extends DataFormat {
	
	protected $dataHeader = [
		'kode', 'nama', 'telpon', 'alamat', 'pic', 'email', 'keterangan'
	];
}