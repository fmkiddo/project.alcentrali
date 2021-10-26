<?php
namespace App\Libraries;

class Document {
	
	public const DEFNUMBERFORMAT	= 'YYMMCCXXXXXX';
// 	public const PERIODEWEEKLY		= 'weekly';
	public const PERIODEMONTHLY 	= 'monthly';
	public const PERIODEYEARLY		= 'yearly';
	
	private $appFormat;
	private $periode;
	private $docstatus;
	
	private const symyear		= 'Y';
	private const symmonth		= 'M';
	private const symdoctype	= 'C';
	private const symserial		= 'X';
	
	public function __construct(
			$defFormat='YYYYMMDDXXXXXX',
			$periode = Document::PERIODEMONTHLY,
			$statuses = [
				'Ditolak', 'Menunggu Persetujuan', 'Disetujui', 'Dikirim', 'Diterima'
			]) {
		$this->appFormat = $defFormat;
		$this->periode = $periode;
		$this->docstatus = $statuses;
	}
	
	public function generateDocnum ($docType, $lastId=NULL) {
		$numbering = '';
		$yearText	= date ('y');
		$monthText	= date ('m');
		
		$yearCount	= substr_count($this->appFormat, Document::symyear);
		$yearReplace = str_repeat(Document::symyear, $yearCount);
		$monthCount	= substr_count($this->appFormat, Document::symmonth);
		$monthReplace = str_repeat(Document::symmonth, $monthCount);
		$typeCount	= substr_count($this->appFormat, Document::symdoctype);
		$typeReplace = str_repeat(Document::symdoctype, $typeCount);
		$serialCount = substr_count ($this->appFormat, Document::symserial);
		$serialReplace = str_repeat (Document::symserial, $serialCount);
		
		if ($lastId === NULL) $serialText = str_repeat ('0', $serialCount - 1) . '1';
		else {
			$serialText = '';
			$serialPos	= strpos ($this->appFormat, Document::symserial);
			$lastSerial = substr ($lastId, $serialPos, $serialCount);
			$samePeriode = false;
			switch ($this->periode) {
				default:
					$monthPos	= strpos ($this->appFormat, Document::symmonth);
					$lastMonth	= substr ($lastId, $monthPos, $monthCount);
					if ($lastMonth === $monthText) $samePeriode = true;
					break;
				case Document::PERIODEYEARLY:
					$yearPos	= strpos ($this->appFormat, Document::symyear);
					$lastYear	= substr ($lastId, $yearPos, $yearCount);
					if ($lastYear === $yearText) $samePeriode = true;
					break;
			}
			
			if (!$samePeriode) $serialText = str_repeat ('0', $serialCount - 1) . '1';
			else {
				$newSerial = intval($lastSerial) + 1;
				$newSerialLength = strlen ($newSerial);
				$serialText = str_repeat('0', ($serialCount - $newSerialLength)) . $newSerial;
			}
		}
		
		$tdocnum	= str_replace ($yearReplace, $yearText, $this->appFormat);
		$tdocnum	= str_replace ($monthReplace, $monthText, $tdocnum);
		$tdocnum	= str_replace ($typeReplace, $docType, $tdocnum);
		$tdocnum	= str_replace ($serialReplace, $serialText, $tdocnum);
		$numbering	= $tdocnum;
		
		return $numbering;
	}
	
	public function getStatusText ($id = 1) {
		return $this->docstatus[$id];
	}
}