<?php
namespace App\Libraries\Format\Osam;

use App\Libraries\Format\DataFormat;

class AssetitemsFormat extends DataFormat {
	
	protected $dataHeader = [
		'kode', 'deskripsi', 'lokasi', 'sublokasi', 'kategori', 'status', 'catatan', 'nomor_po', 'perolehan', 'waktu', 'qty'
	];
}