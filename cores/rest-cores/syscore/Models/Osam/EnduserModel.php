<?php
namespace App\Models\Osam;


use App\Models\BaseModel;


class EnduserModel extends BaseModel {
	
	protected $table			= 'ousr';
	protected $primaryKey		= 'idx';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['ougr_idx', 'username', 'email', 'password', 'created_by', 'updated_by', 'updated_date'];
	protected $colHeader		= ['Nama Pengguna', 'Alamat Email', 'Password', 'Level Akses', 'Akses Lokasi'];
	
}