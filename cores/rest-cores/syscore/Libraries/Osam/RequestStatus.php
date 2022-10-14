<?php

namespace App\Libraries\Osam;

use App\Libraries\Type;

class RequestStatus implements Type {

	private $requestStatus	= [
		0	=> [
			'id'	=> 'Ditolak',
			'id-id'	=> 'Ditolak',
			'en'	=> 'Declined',
			'en-us'	=> 'Declined'
		],
		1	=> [
			'id'	=> 'Menunggu Persetujuan',
			'id-id'	=> 'Menunggu Persetujuan',
			'en'	=> 'Approval Pending',
			'en-us'	=> 'Approval Pending'
		],
		2	=> [
			'id'	=> 'Disetujui',
			'id-id'	=> 'Disetujui',
			'en'	=> 'Approved',
			'en-us'	=> 'Approved'
		]
	];

	function getTypeText ($locale, $key): string {
		return $this->requestStatus[$key][$locale];
	}
}
