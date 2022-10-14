<?php 

namespace App\Libraries\Osam;

class EmailMessage {

	private $sbjc;
	
	private function __construct () {
		$this->sbjc	= [
			'ousr-00'	=> [
				'id'		=> 'Percobaan Login Gagal [%s]',
				'en'		=> 'Failed Login Attempt [%s]'
			],
			'ousr-01'	=> [
				'id'		=> 'Percobaan Login Berhasil [%s]',
				'en'		=> 'Success Login Attempt [%s]'
			],
			'move-00'	=> [
				'id'		=> 'Permintaan Perpindahan Aset Baru No. [%s]',
				'en'		=> 'New Request for Asset Transfer with No. [%s]'
			],
			'move-01'	=> [
				'id'		=> 'Permintaan Perpindahan Aset No. [%s] Ditolak',
				'en'		=> 'Asset Transfer Request No. [%s] Has Been Declined'
			],
			'move-02'	=> [
				'id'		=> 'Permintaan Perpindahan Aset No. [%s] Disetujui oleh %s',
				'en'		=> 'Asset Transfer Request No. [%s] Has Been Approved by %s'
			],
			'move-03'	=> [
				'id'		=> 'Permintaan Perpindahan Aset No. [%s] Telah Dikirim',
				'en'		=> 'Asset Transfer Request No. [%s] Has Been Sent',
			],
			'move-04'	=> [
				'id'		=> 'Dokumen Perpindahan Aset No. [%s] Telah Diterima oleh %s',
				'en'		=> 'Asset Transfer Request No. [%s] Has Been Received by %s'
			],
			'move-05'	=> [
				'id'		=> 'Dokumen Perpindahan Aset No. [%s] Telah Berhasil Didistribusikan oleh %s',
				'en'		=> 'Asset Transfer Document No. [%s] Has Been Distributed by %s'
			],
			'destroy-00'	=> [
				'id'		=> 'Permintaan Pemusnahan Aset Baru No. [%s]',
				'en'		=> 'New Request for Asset Disposal with No. [%s]'
			]
		];
	}
	
	public function getSubject ($key, $locale='id'): String {
		return $this->sbjc[$key][$locale];
	}
	
	public static function init () {
		return new EmailMessage ();
	}
}
