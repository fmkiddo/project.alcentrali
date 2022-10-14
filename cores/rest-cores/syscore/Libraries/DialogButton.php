<?php
namespace App\Libraries;

class DialogButton {

	private $buttonProperties	= [
		'ok'		=> [
			'class'	=> 'btn btn-info',
			'name'	=> 'dialog-btn-ok',
			'text'	=> [
				'id'	=> 'OK',
				'en'	=> 'OK'
			]
		],
		'yes'		=> [
			'class'	=> 'btn btn-success',
			'name'	=> 'dialog-btn-yes',
			'text'	=> [
				'id'	=> 'Ya',
				'en'	=> 'Yes'
			]
		],
		'no'		=> [
			'class'	=> 'btn btn-warning',
			'name'	=> 'dialog-btn-no',
			'text'	=> [
				'id'	=> 'Tidak',
				'en'	=> 'No'
			]
		],
		'cancel'	=> [
			'class'	=> 'btn btn-danger',
			'name'	=> 'dialog-btn-cancel',
			'text'	=> [
				'id'	=> 'Batal',
				'en'	=> 'Cancel'
			]
		]		
	];
	private $types	= [
		0	=> ['ok'],
		1	=> ['yes', 'no'],
		2	=> ['yes', 'no', 'cancel'],
		3	=> ['ok', 'cancel']
	];
	
	public function getButtonProperty ($locale, $type=0) {
		$returnData	= array ();
		$dialogType	= $this->types[$type];
		foreach ($dialogType as $theType) {
			$btnProp = $this->buttonProperties[$theType];
			$returnData[$btnProp['name']]	= [
				'text'	=> $btnProp['text'][$locale],
				'class'	=> $btnProp['class']
			];
		}
		return $returnData;
	}
}
