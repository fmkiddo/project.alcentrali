<?php
namespace App\Models\Osam;


use App\Models\BaseModel;

class AssetItemModel extends BaseModel {
	
	protected $table			= 'oita';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['olct_idx', 'osbl_idx', 'oaci_idx', 'oast_idx', 'code', 'name', 'notes', 'po_number', 'acquisition_value', 'loan_time', 'qty'];
	protected $colHeader		= [
		'locationassets'	=> ['Kode', 'Nama', 'Sublokasi', 'Kategori', 'Notes', 'No. PO', 'Qty'],
		'mainassets'		=> ['Kode', 'Nama', 'Jumlah']
	];
}