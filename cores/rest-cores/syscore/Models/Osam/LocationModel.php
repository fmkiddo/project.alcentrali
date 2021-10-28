<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class LocationModel extends BaseModel {
	
	protected $table			= 'olct';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['code', 'name', 'phone', 'address', 'contact_person', 'email', 'notes', 'created_by', 'updated_by'];
	protected $colHeader		= ['Kode', 'Nama', 'Telepon', 'Alamat', 'PIC', 'Email', 'Catatan'];
}