<?php
namespace App\Models;


class ButtonResModel extends BaseModel {
	
	
	protected $table			= 'rsc2';
	protected $primaryKey		= 'locale_id';
	protected $returnType		= 'CodeIgniter\Entity';
	protected $allowedFields	= ['locale_id', 'oktext', 'yestext', 'notext', 'canceltext'];
}