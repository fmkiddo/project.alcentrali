<?php
namespace App\Libraries\APIModules;

use App\Models\Osam\AssetMoveOutModel;
use App\Models\Osam\AssetMoveInModel;
use App\Models\Osam\AssetMoveOutRequestModel;
use App\Models\Osam\AssetRequisitionModel;
use App\Models\Osam\AssetRemovalModel;
use App\Libraries\EmailTools;
use App\Libraries\Document;
use App\Libraries\Osam\EmailMessage;
use App\Libraries\Osam\RequestStatus;
use App\Libraries\Osam\RequestDocumentType;

class OsamModule extends Modules {

	private const EMAIL_FROM	= 'do-not-reply@jodamoexchange.com';
	private const EMAIL_NAME_FROM	= 'System Notification';
	
	private const IMAGEWRITEPATH	= 'assets/images/osam/%s';
	private const ASMIMGWRITEPATH	= 'assets/images/osam/%s/request';
	
	private $docstats	= [
		0	=> [
				'id'	=> 'Ditolak',
				'en'	=> 'Declined'
			],
		1	=> [
				'id'	=> 'Menunggu Tanggapan',
				'en'	=> 'Waiting Response'
			],
		2	=> [
				'id'	=> 'Disetujui',
				'en'	=> 'Approved'
			],
		3	=> [
				'id'	=> 'Dikirim',
				'en'	=> 'Sent'
			],
		4	=> [
				'id'	=> 'Diterima',
				'en'	=> 'Received'
			],
		5	=> [
				'id'	=> 'Didistribusikan',
				'en'	=> 'Distributed'
			],
		6	=> [
				'id'	=> 'Dihancurkan/Dihapus',
				'en'	=> 'Disposed/Removed'
			]
	];
	
	private $doctypes	= [
		'01'	=> [
				'id'	=> 'Pemindahan',
				'en'	=> 'Transfer'
			],
		'02'	=> [
				'id'	=> 'Penerimaan',
				'en'	=> 'Reception'
			],
		'03'	=> [
				'id'	=> 'Penghapusan',
				'en'	=> 'Disposal'
			]
	];
	
	protected $moduleName = 'Osam';
	
	private $attrtypes = [
		'text'			=> 'Teks',
		'date'			=> 'Tanggal',
		'list'			=> 'Daftar',
		'prepopulated-list'	=> 'Daftar Berisi'
	];
	
	private function getLoggerName (): string {
		$username	= '';
		$loggerId	= $this->getDataTransmit()['data-loggedousr'];
		
		$model		= $this->initModel ('EnduserModel');
		$ousr		= $model->where ('idx', $loggerId)->find ();
		$username	= $ousr[0]->username;
		
		$model		= $this->initModel ('EnduserProfileModel');
		$usr3		= $model->where ('idx', $loggerId)->find ();
		$username	= (strlen ($usr3[0]->fname) > 0) ? $usr3[0]->fname : $username;
		
		return $username;
	}
	
	private function getLoggerType (): bool {
		$loggerType	= FALSE;
		
		$loggerId	= $this->getDataTransmit()['data-loggedousr'];
		
		$model		= $this->initModel ('UserGroupsModel');
		$ougr		= $model->where ('code', 'admin')->find ();
		$openType	= $ougr[0]->idx;
		
		$model		= $this->initModel ('EndUserModel');
		$ousr		= $model->where ('idx', $loggerId)->find ();
		$loggerType	= $ousr[0]->ougr_idx == $openType;
		return $loggerType;
	}
	
	private function csvDataProcessing ($type, $ousr_idx, $data) {
		$failedImport = 0;
		$now = date ('Y-m-d H:i:s');
		$model = NULL;
		$addmodelnames = array ();
		$addmodels = array ();
		
		switch ($type) {
			default:
				break;
			case 'locations':
				$model = $this->initModel ('LocationModel');
				break;
			case 'sublocations':
				$model = $this->initModel ('SublocationModel');
				break;
			case 'catattributes':
				$model = $this->initModel ('CategoryAttributeModel');
				$addmodelnames = [
					'CategoryAttributeDataModel'
				];
				break;
			case 'categories':
				$model = $this->initModel ('ItemCategoryModel');
				$addmodelnames = [
					'ItemAttributeModel'
				];
				break;
			case 'assetitems':
				$model = $this->initModel ('AssetItemModel');
				break;
			case 'assetitemdetails':
				$model = $this->initModel ('ItemAttributesModel');
				break;
			case 'assetitemimage':
				$model = $this->initModel ('AssetItemImageModel');
				break;
		}
		
		if (count ($addmodelnames) > 0) 
			foreach ($addmodelnames as $addmodelname) array_push ($addmodels, $this->initModel ($addmodelname));
		
		if ($model !== NULL):
			foreach ($data as $line) {
				$status = $model->insertFromFile($line, $ousr_idx, $now);
				if (!$status) $failedImport++;
				if (count ($addmodels) > 0)
					foreach ($addmodels as $amodel) $amodel->insertFromFile ($line, $ousr_idx, $now);
			}
		endif;
		
		return $failedImport;
	}
	
	private function getAdministrators (): array {
		$admins	= array ();
		$model	= $this->initModel ('EnduserModel');
		$admins	= $model->where ('idx', 1)->orWhere ('idx', 3)->find ();
		// $ousrs	= $model->join ('usr1', 'ousr.idx=usr1.ousr_idx')->where ('usr1.olct_idx', 0)->find ();
		return $admins;
	}
	
	private function getTargetLocationManagers ($targetOlctIdx): array {
		$managers = array ();
		$model	= $this->initModel ('EnduserModel');
		$managers	= $model->join ('usr1', 'ousr.idx=usr1.ousr_idx')->join ('ougr', 'ougr.idx=ousr.ougr_idx')
					->where ('usr1.olct_idx', $targetOlctIdx)->where ('ougr.can_approve', 1)->find ();
		return $managers;
	}
	
	public function executeRequest($trigger): array {
		$requestResponse;
		
		/**
		 * 
		 * @var \App\Models\BaseModel $model;
		 */
		$model;
		$returnData = [];
		$now = date ('Y-m-d H:i:s');
		
		$emailTools	= EmailTools::init ();
		$emailMsgs	= EmailMessage::init ();
		$administrators	= [1, 2, 3];
		
		switch ($trigger) {
			default:
				$requestResponse = [
					'status'	=> 400,
					'message'	=> 'Bad Directives!'
				];
				if (strpos ($trigger, 'dataupload-md') !== FALSE) {
					$dataTransmit	= $this->getDataTransmit();
					$whatToUpload	= str_replace('dataupload-md-', '', $trigger);
					$dataFormat		= $this->initFormat(ucfirst($whatToUpload));
					$ousr_idx		= $dataTransmit['data-loggedousr'];
					if (!$dataFormat->headerCompare($dataTransmit['data-header'])) {
						$requestResponse['status']	= 500;
						$requestResponse['message']	= 'Error! File format not matched!';
					} else {
						$dataBody = $dataTransmit['data-body'];
						$importFailed = $this->csvDataProcessing($whatToUpload, $ousr_idx, $dataBody);
						$returnData = [
							'data-importfailed' => $importFailed
						];
						$requestResponse['status'] = 200;
					}
				}
				break;
			case 'headdata': 
				$dataTransmit = $this->getDataTransmit ();
				$ousr_idx = $dataTransmit['data-loggedousr'];
				$model	= $this->initModel('EnduserModel');
				$ousrs	= $model->select ('ugr1.privilege')->join ('ugr1', 'ousr.ougr_idx=ugr1.ougr_idx')->where ('ousr.idx', $ousr_idx)->find ();
				if (count ($ousrs) == 0) {
					$requestResponse['status']	= 500;
					$requestResponse['message']	= 'Error! Cannot find user group data';
				} else {
					$prives = explode(';', $ousrs[0]->privilege);
					$model	= $this->initModel('ModuleModel');
					$structures = [];
					
					$omdls	= $model->orderBy('code', 'ASC')->find ();
					foreach ($omdls as $omdl) {
						if (in_array ($omdl->idx, $prives)) 
							if ($omdl->parent_idx == 0) 
								$structures[$omdl->idx] = [
									'id'		=> $omdl->style_id,
									'smarty'	=> $omdl->smarty,
									'target'	=> $omdl->targeturl,
									'icon'		=> $omdl->icon,
									'subs'		=> []
								];
							else
								if (array_key_exists($omdl->parent_idx, $structures)) {
									$subs = $structures[$omdl->parent_idx]['subs'];
									$child = [
										'id'		=> $omdl->style_id,
										'smarty'	=> $omdl->smarty,
										'target'	=> $omdl->targeturl,
										'icon'		=> $omdl->icon,
										'subs'		=> []
									];
									if (!array_key_exists($omdl->segment, $subs)) {
										$subs[$omdl->segment]	= [
											'title'		=> $omdl->title,
											'child'		=> []
										];
									}
									
									array_push($subs[$omdl->segment]['child'], $child);
									$structures[$omdl->parent_idx]['subs'] = $subs;
								}
					}
					$returnData = [
						'data-logger'		=> $this->getLoggerName (),
						'data-loggertype'	=> $this->getLoggerType (),
						'data-menustructure'	=> $structures,
						'data-messages'		=> [],
						'data-notifications'	=> []
					];
					$requestResponse['status']	= 200;
				}
				break;
			case 'get-dialogbutton':
				$dataTransmit	= $this->getDataTransmit ();
				$dialogButton	= new \App\Libraries\DialogButton ();
				$returnData	= [
					'status'	=> 200,
					'returndata'	=> $dialogButton->getButtonProperty ($dataTransmit['data-locale'], $dataTransmit['data-type'])
				];
				$requestResponse['status'] = 200;
				break;
			case 'dashboard-summary':
				$dataTransmit	= $this->getDataTransmit ();
				$loggedOusrID	= $dataTransmit['data-loggedousr'];
				$model		= $this->initModel ('EnduserModel');
				$ousr		= $model->where ('idx', $loggedOusrID)->find ();
				
				if (count ($ousr) == 0) {
					$resturnData	= array ();
					$requestResponse['status'] = 500;
				} else {
					$model	= $this->initModel ('EnduserProfileModel');
					$profiles		= $model->where ('idx', $loggedOusrID)->find ();
					if (count ($profiles) > 0 && $profiles[0]->fname !== '') $uname = $profiles[0]->fname;
					else $uname = $ousr[0]->username;
					
					$model		= $this->initModel ('AssetItemModel');
					$totalQty	= $model->select ('sum(qty) as `totalassets`')->where ('qty >', 0)->find ();
					$totalQty	= ($totalQty[0]->totalassets !== NULL) ? $totalQty[0]->totalassets : 0;
					$totalLine	= $model->selectCount ('code')->where ('qty>', 0)->groupBy ('code')->find ();
					$totalLine	= count ($totalLine);
					
					$totalRequest	= 0;
					$model		= $this->initModel ('AssetMoveOutModel');
					$totalMvo	= $model->select ('count(status) as `mvocount`')->whereNotIn ('status', [0, 5])->find ();
					$totalRequest	+= $totalMvo[0]->mvocount;
					
					$omvo		= $model->select ('SUM(asm_mvo1.qty) AS `qty_intransit`')->join ('mvo1', 'mvo1.omvo_idx=omvo.idx')->where ('omvo.status', 3)->find ()[0];
					$totalAssetIntransit = ($omvo->qty_intransit == NULL) ? 0 : $omvo->qty_intransit;
					
					$intransits	= $model->select ('oita.code AS `oita_code`, oita.name AS `oita_name`, olct.code AS `olct_code`, olct.name AS `olct_name`, ' . 
									'osbl.name AS `osbl_name`, mvo1.qty, omvo.docnum AS `omvo_docnum`, omvi.docnum AS `omvi_docnum`, omvo.status')
								->join ('mvo1', 'mvo1.omvo_idx=omvo.idx', 'left')->join ('omvi', 'omvi.omvo_refidx=omvo.idx', 'left')
								->join ('oita', 'oita.idx=mvo1.oita_idx', 'left')->join ('olct', 'olct.idx=mvo1.olct_idx', 'left')
								->join ('osbl', 'osbl.idx=mvo1.osbl_idx', 'left')->where ('omvo.status', 3)->find ();
					
					$onProgs	= array ();
					$newReqs	= array ();
					$results	= $model->select ('omvo.docnum, omvo.docdate, "01" AS `type`')->where ('status', 1)->find ();
					
					foreach ($results as $result) array_push ($newReqs, $result);
					
					$results	= $model->select ('omvo.docnum, omvo.docdate, "01" AS `type`, omvo.status')->where ('status>', 1)->where ('status<', 5)->find ();
					
					foreach ($results as $result) array_push ($onProgs, $result);
					
					$model		= $this->initModel ('AssetRemovalModel');
					$results	= $model->select ('oarv.docnum, oarv.docdate, "10" AS `type`')->where ('status', 1)->find ();
					
					foreach ($results as $result) array_push ($newReqs, $result);
					
					$results	= $model->select ('oarv.docnum, oarv.docdate, "10" AS `type`, oarv.status')->where ('status', 2)->find ();
					
					foreach ($results as $result) array_push ($onProgs, $result);
					
					$returnData	= [
						'data-infos'	=> [
							'assets-qty'		=> $totalQty,
							'assets-types'		=> $totalLine,
							'pend-request'		=> $totalRequest,
							'assets-intransit'	=> $totalAssetIntransit,
							'intransits'		=> $intransits,
							'newrequests'		=> $newReqs,
							'progressing'		=> $onProgs
						],
						'docStats'	=> $this->docstats,
						'docTypes'	=> $this->doctypes
					];
					$requestResponse['status'] = 200;
				}
				break;
			case 'categories':
				$model = $this->initModel('ItemCategoryModel');
				$returnData = [
					'categories'	=> $model->find (),
					'header'		=> $model->getColumnHeader ()
				];
				$requestResponse['status'] = 200;
				break;
			case 'categoryitem':
				$dataTransmit = $this->getDataTransmit();
				$model = $this->initModel('ItemCategoryModel');
				$oaci = $model->find ($dataTransmit['oaci-idx']);
				
				$octaData =	[];
				$model = $this->initModel('ItemAttributeModel');
				$octas = $model->join ('octa', 'aci1.octa_idx=octa.idx')->where ('oaci_idx', $dataTransmit['oaci-idx'])->find ();
				$hidden = '';
				$texts	= '';
				
				if (count ($octas) > 0) {
					$hidden = '[';
					$texts = '[';
				}
				
				foreach ($octas as $octa):
					if ($octa->used):
						$octaRow = [
							'octa_idx' 	=> $octa->octa_idx,
							'octa_name'	=> $octa->attr_name,
							'octa_used'	=> $octa->used
						];
						array_push($octaData, $octaRow);
						$hidden .= $octa->octa_idx . ', ';
						$texts .= $octa->attr_name . ', ';
					endif;
				endforeach;
				
				if (count ($octas) > 0) {
					$hidden = substr($hidden, 0, strlen($hidden) - 2) . ']';
					$texts = substr($texts, 0, strlen($texts) - 2) .']';
				}
				
				$returnData = [
					'cidata' => [
						'oaci_idx' 		=> $oaci->idx,
						'oaci_name'		=> $oaci->ci_name,
						'oaci_dscript'	=> $oaci->ci_dscript
					],
					'ciattribs'	=> $octaData,
					'ciattribsinput' => [
						'hidden'	=> $hidden,
						'texts'		=> $texts
					]
				];
				$requestResponse['status'] = 200;
				break;
			case 'ciattributes':
				$model = $this->initModel('CategoryAttributeModel');
				$returnData = [
					'cta' => $model->find ()
				];
				$requestResponse['status'] = 200;
				break;
			case 'ciattributtype':
				$returnData = [
					'attribTypes' => $this->attrtypes
				];
				$requestResponse['status'] = 200;
				break;
			case 'new-oaci':
				newOaci:
				$dataTransmit = $this->getDataTransmit();
				if ($dataTransmit['oaciidx'] > 0) goto updateOaci;
				$attribs = $dataTransmit['attribs'];
				if (strlen ($attribs) == 0) 
					$returnData = [
						'returncode' => 200,
						'message' => 'Error! Kategori item membutuhkan minimal 1 buah attribut'
					];
				else {
					$newOaci = [
						'ci_name'		=> $dataTransmit['name'],
						'ci_dscript'	=> $dataTransmit['dscript']
					];
					$model = $this->initModel('ItemCategoryModel');
					$model->insert ($newOaci);
					$new_oaciidx = $model->insertID ();
					
					$attribs = $dataTransmit['attribs'];
					$attribs = str_replace(']', '', str_replace('[', '', $attribs));
					$arrayAttribs = explode(',', $attribs);
					
					$model = $this->initModel('ItemAttributeModel');
					foreach ($arrayAttribs as $attrib) {
						$aci1 = [
							'oaci_idx' => $new_oaciidx,
							'octa_idx' => $attrib,
							'used' => TRUE
						];
						$model->insert ($aci1);
					}
					
					$returnData = [
						'returncode' => 0,
						'message' => ''
					];
				}
				
				goto returnOaciExec;
			case 'update-oaci':
				updateOaci:
				$dataTransmit = $this->getDataTransmit();
				if ($dataTransmit['oaciidx'] == 0) goto newOaci;
				
				returnOaciExec:
				
				$requestResponse['status'] = 200;
				break;
			case 'location':
				$model = $this->initModel('LocationModel');
				$locations = $model->select ('idx, code, name, phone, address, contact_person, email, notes')->find ();
				$returnData = [
					'locations'	=> $locations,
					'header'	=> $model->getColumnHeader ()
				];
				$requestResponse['status'] = 200;
				break;
			case 'locationprofile':
				$dataTransmit = $this->getDataTransmit();
				$model = $this->initModel('LocationModel');
				$returnData = [
					'location'	=> $model->find ($dataTransmit['olctid'])
				];
				$requestResponse['status'] = 200;
				break;
			case 'update-locationprofile':
				$dataTransmit = $this->getDataTransmit();
				$ousrIdx = $dataTransmit['data-loggedousr'];
				$dataForm = $dataTransmit['data-form'];
				$olct_idx = $dataForm['idx'];
				$model = $this->initModel('LocationModel');
				if ($olct_idx == 0) {
					$olct = $model->where ('code', $dataForm['location-code'])->find ();
					if (count ($olct) > 0) {
						$requestResponse['status']	= 500;
						$requestResponse['message']	= 'Location code already exists!';
					} else {
						$insertParam = [
							'code'				=> $dataForm['location-code'],
							'name'				=> $dataForm['location-name'],
							'phone'				=> $dataForm['location-phone'],
							'address'			=> $dataForm['location-address'],
							'contact_person'	=> $dataForm['location-pic'],
							'email'				=> $dataForm['location-email'],
							'notes'				=> $dataForm['location-notes'],
							'created_by'		=> $ousrIdx,
							'updated_by'		=> $ousrIdx,
							'updated_date'		=> date ('Y-m-d H:i:s')
						];
						$model->insert ($insertParam);
						if ($model->getInsertID () > 0) $requestResponse['status'] = 200;
						else {
							$requestResponse['status'] = 500;
							$requestResponse['message'] = 'Error! Location insertion failed!';
						}
					}
				} else {
					$updateParam = [
						'name'				=> $dataForm['location-name'],
						'phone'				=> $dataForm['location-phone'],
						'address'			=> $dataForm['location-address'],
						'contact_person'	=> $dataForm['location-pic'],
						'email'				=> $dataForm['location-email'],
						'notes'				=> $dataForm['location-notes'],
						'updated_by'		=> $ousrIdx,
						'updated_date'		=> date ('Y-m-d H:i:s')
					];
					$model->update ($olct_idx, $updateParam);
					if ($model->affectedRows () > 0) $requestResponse['status'] = 200;
					else {
						$requestResponse['status']	= 500;
						$requestResponse['message']	= 'Error! Location profile update failed!';
					}
				}
				break;
			case 'assets-main-list':
				$model = $this->initModel('AssetItemModel');
				$assets = $model->select ('code, name, SUM(qty) AS `total`')->where ('qty >', 0)->groupBy ('code')
								->orderBy ('code', 'ASC')->get ()->getResultArray ();
				$returnData = [
					'assets-data' 	=> $assets,
					'assets-header'	=> $model->getColumnHeader ('mainassets')
				];
				$requestResponse['status'] = 200;
				break;
			case 'asset-details':
				$model = $this->initModel('AssetItemModel');
				$dataTransmit = $this->getDataTransmit();
				$assetCode = $dataTransmit['assetcode'];
				$assets = $model->select ('oita.idx, code, name, oaci_idx, ci_name, ci_dscript, loan_time, sum(qty) as totalqty')->join ('oaci', 'oita.oaci_idx=oaci.idx')
								->where ('code', $assetCode)->where ('qty >', 0)->groupby ('code')->find ()[0];
				$oita_idx = $assets->idx;
				$detail = [
					'Kode'			=> $assets->code,
					'Nama'			=> $assets->name,
					'Kategori'		=> $assets->ci_name,
					'Deskripsi'		=> $assets->ci_dscript,
					'Waktu Guna (jam)'	=> $assets->loan_time,
					'Total Aset'		=> $assets->totalqty
				];
				
				$locations = $model->select ('olct_idx, olct.code, olct.name')->join ('olct', 'olct.idx=oita.olct_idx', 'LEFT')
								->where ('oita.code', $assetCode)->where ('qty >', 0)->groupby ('olct_idx')->find ();
				
				$condition = [
					'oita.code' => $assetCode,
					'qty >' => 0
				];
				$sbl1 = $model->select ('olct.code, osbl.name, oita.qty')->join ('olct', 'oita.olct_idx=olct.idx')->join ('osbl', 'oita.osbl_idx=osbl.idx')
							->where ($condition)->where ('qty >', 0)->orderby ('olct.code')->find ();
				$sublocations = [
					'header'	=> ['Sublokasi', 'Qty'],
					'data'		=> []
				];
				
				foreach ($sbl1 as $sbl) {
					$data = $sublocations['data'];
					$code = $sbl->code;
					$sublocation = [
						'name' => $sbl->name,
						'qty' => $sbl->qty
					];
					
					if (array_key_exists($code, $data)) 
						array_push($data[$code], $sublocation);
					else 
						$data[$code] = [
							$sublocation
						];
					
					$sublocations['data'] = $data;
				}
				
				$model = $this->initModel('ItemAttributesModel');
				$ita1 = $model->select ('attr_name as `Nama`, attr_value as `Nilai`')->join ('octa', 'ita1.octa_idx=octa.idx')->where ('oita_idx', $oita_idx)->find ();
				
				$attrdetail = [];				
				foreach ($ita1 as $ita) {
					$attrrow = [
						'Nama' => $ita->Nama,
						'Nilai' => $ita->Nilai
					];
					array_push($attrdetail, $attrrow);
				}
				
				$images	= array ();
				$model	= $this->initModel ('AssetItemImageModel');
				$ita2	= $model->where ('oita_idx', $oita_idx)->find ();
				
				if (count ($ita2) > 0) {
					$clientCode	= $this->getClientCode ();
					$index		= 0;
					foreach ($ita2 as $ita) {
						$imagePath	= sprintf (OsamModule::IMAGEWRITEPATH, $clientCode);
						$filePath	= $imagePath . '/' . $ita->image;
						$file		= new \CodeIgniter\Files\File ($filePath);
						$fileContent	= base64_encode (file_get_contents ($filePath));
						
						$images[$index]	= [
							'filename'	=> $ita->image,
							'mime'		=> $file->getMimeType (),
							'size'		=> $file->getSize (),
							'contents'	=> $fileContent
						];
						$index++;
					}
				}
				
				$returnData = [
					'details'	=> $detail,
					'attrdetail'	=> $attrdetail,
					'locations'	=> $locations,
					'sublocations'	=> $sublocations,
					'images'	=> $images
				];
				
				$requestResponse['status'] = 200;
				break;
			case 'get-assetlist':
				$dataTransmit = $this->getDataTransmit ();
				if ($dataTransmit['output-type'] === 'perlocation') {
					$model = $this->initModel('AssetItemModel');
					$items = [];
					$items = $model->select ('oita.idx, oita.code, osbl.name as `sublocname`, oita.name, oita.qty')->join ('osbl', 'osbl.idx=oita.osbl_idx')
									->where ('oita.qty >', 0)->where ('oita.olct_idx', $dataTransmit['from-location'])
									->groupStart ()
										->like ('oita.code', strtoupper ($dataTransmit['barcode-search']), 'both')
										->orLike ('oita.name', strtoupper ($dataTransmit['barcode-search']), 'both')
									->groupEnd ()
									->orderBy ('oita.idx', 'ASC')->find ();
					$returnData = [
						'good'			=> true,
						'assetitems'	=> $items
					];
				} else {
					$model = $this->initModel ('SublocationModel');
					$sublocs = $model->where ('olct_idx', $dataTransmit['from-location'])->find ();
					
					$model = $this->initModel ('AssetItemModel');
					$assetitems = $model->where ('olct_idx', $dataTransmit['from-location'])->orderBy ('osbl_idx', 'ASC')->find ();
					
					$returnData = [
						'good'		=> true,
						'sublocs'	=> $sublocs,
						'assetitems'	=> $assetitems
					];
				}
				$returnData['tabpanehead'] = [
					'<i class="fas fa-check-square fa-fw"></i>',
					'Kode Aset',
					'Nama Aset',
					'Sublokasi',
					'Qty',
					'Input Qty'
				];
				$requestResponse['status'] = 200;
				break;
			case 'muser-retrieve':
				$model = $this->initModel('EnduserModel');
				$ousrs = $model->select ('ousr.idx, username, email, ougr.name, usr1.olct_idx')->join ('ougr', 'ousr.ougr_idx=ougr.idx', 'LEFT')->
								join ('usr1', 'ousr.idx=usr1.ousr_idx', 'LEFT')->findAll ();
				$muser = [];
				
				$muserHeader = $model->getColumnHeader ();
				$model = $this->initModel('LocationModel');
				
				foreach ($ousrs as $ousr) {
					$accessLocation = ($ousr->olct_idx == 0) ? 'Akses Lengkap' : $model->find ($ousr->olct_idx)->name;
					
					$usr = [
						$ousr->idx,
						$ousr->username,
						$ousr->email,
						str_repeat('*', 8),
						$ousr->name,
						$accessLocation
					];
					
					array_push($muser, $usr);
				}
				
				$returnData = [
					'muser-listdata'	=> $muser,
					'muser-heading'		=> $muserHeader
				];
				$requestResponse['status'] = 200;
				break;
			case 'user-retrieve':
				$dataTransmit = $this->getDataTransmit();
				$ousrIdx = $dataTransmit['data-loggedousr'];
				$model = $this->initModel('EnduserModel');
				$userData = $model->join ('usr1', 'ousr.idx=usr1.ousr_idx')->where ('ousr.idx', $ousrIdx)->find ();
				$returnData = [
					'userdata' => $userData[0]
				];
				$requestResponse['status'] = 200;
				break;
			case 'newassets':
				$model = $this->initModel('ItemCategoryModel');
				$categories = $model->find ();
				$model = $this->initModel('LocationModel');
				$locations = $model->find ();
				$returnData = [
					'data-categories'	=> $categories,
					'data-locations'	=> $locations
				];
				$requestResponse['status'] = 200;
				break;
			case 'asset-category':
				$dataTransmit = $this->getDataTransmit();
				$model = $this->initModel('ItemAttributeModel');
				$attributes = $model->where ('oaci_idx', $dataTransmit['category'])->find ();
				$model = $this->initModel('CategoryAttributeModel');
				$forms = [];
				foreach ($attributes as $attribute) {
					$catAttr = $model->find ($attribute->octa_idx);
					$form = [];
					switch ($catAttr->attr_type) {
						default:
							$form = [
								'name'	=> 'attrid-' . $catAttr->idx,
								'label'	=> $catAttr->attr_name,
								'type'	=> 'text'
							];
							break;
						case 'date':
							break;
						case 'list':
							break;
						case 'prepopulated-list':
							break;
					}
					
					array_push($forms, $form);
				}
				$returnData = [
					'data-form'	=> $forms
				];
				$requestResponse['status'] = 200;
				break;
			case 'asset-location':
				$dataTransmit = $this->getDataTransmit();
				$model = $this->initModel('SublocationModel');
				$sublocations = $model->where ('olct_idx', $dataTransmit['location'])->find ();
				$returnData = [
					'sublocations'	=> $sublocations
				];
				$requestResponse['status'] = 200;
				break;
			case 'detailedlocation':
				$returnData = [];
				$dataTransmit = $this->getDataTransmit();
				$model	= $this->initModel('LocationModel');
				$locationCode = $dataTransmit['data-locationcode'];
				$olct	= $model->select ('idx, code, name, phone, address, contact_person, email, notes')->where ('code', $locationCode)->find ();
				if (count ($olct) > 0) {
					$olct_idx = $olct[0]->idx;
					$returnData['location'] = $olct[0];
					$model = $this->initModel('SublocationModel');
					$sublocations = $model->where ('olct_idx', $olct_idx)->find ();
					$returnData['sublocations'] = $sublocations;
					$model = $this->initModel('AssetItemModel');
					$locationassets = $model->select ('osbl_idx, code, name, ci_name, notes, po_number, qty')->join ('oaci', 'oaci.idx = oita.oaci_idx')->where ('olct_idx', $olct_idx)->find ();
					$locationassets_raw = [];
					
					$total_qty = 0;
					foreach ($locationassets as $locationasset) {
						$attribute = $locationasset->toArray ();
						$osbl_id = $locationasset->osbl_idx;
						foreach ($sublocations as $sublocation) 
							if ($sublocation->idx == $osbl_id) {
								$attribute['sublocation'] = $sublocation->name;
								break;
							}
						
						$total_qty += $locationasset->qty;
						$assetEntity = new \CodeIgniter\Entity ();
						$assetEntity->fill ($attribute);
						array_push($locationassets_raw, $assetEntity);
					}
					
					$returnData['totalassets'] = $total_qty;
					$returnData['locationassets'] = $locationassets_raw;
					
					$requestResponse['status'] = 200;
				} else {
					$requestResponse['status']	= 404;
					$requestResponse['message']	= 'Location not found';
				}
				break;
			case 'load-sublocation':
				$dataTransmit	= $this->getDataTransmit();
				$model	= $this->initModel ('LocationModel');
				$olct	= $model->where ('code', $dataTransmit['data-locationcode'])->find ();
				if (count ($olct) > 0) {
					$olct_idx = $olct[0]->idx;
					$sublocationCode = $dataTransmit['data-sublocationcode'];
					
					if ($sublocationCode === 0) 
						$returnData = [
							'data-type'		=> 'new',
							'data-pages'	=> [
								'data-location'		=> $olct[0],
								'data-sublocation'	=> NULL
							]
						];
					else {
						$model	= $this->initModel('SublocationModel');
						$osbl	= $model->where ('olct_idx', $olct_idx)->where ('code', $sublocationCode)->find ();
						if (count ($osbl) == 0) {
							$requestResponse['status']	= 404;
							$requestResponse['message']	=' Error! Data could not be found!';
						} else {
							$returnData	= [
								'data-type'		=> 'edit',
								'data-pages'	=> [
									'data-location'		=> $olct[0],
									'data-sublocation'	=> $osbl[0]
								]
							];
						}
					}
					$requestResponse['status']	= 200;
				} else {
					$requestResponse['status']	= 404;
					$requestResponse['message']	= 'Error! Data location not found!';
				}
				break;
			case 'sublocation-addupdate':
				$dataTransmit		= $this->getDataTransmit ();
				$locationCode		= $dataTransmit['data-locationcode'];
				
				$model				= $this->initModel ('LocationModel');
				$olct				= $model->where ('code', $locationCode)->find ();
				if (count ($olct) > 0) {
					$olct_idx			= $olct[0]->idx;
					$sublocationCode	= $dataTransmit['data-sublocationcode'];
					$model				= $this->initModel ('SublocationModel');
					$osbl				= $model->where ('olct_idx', $olct_idx)->where ('code', $sublocationCode)->find ();
					
					$dbParam			= [
						'name'				=> $dataTransmit['data-description']
					];
					
					$good = FALSE;
					if (count ($osbl) > 0) {
						$osbl_idx			= $osbl[0]->idx;
						$good = $model->update ($osbl_idx, $dbParam);
					} else {
						$dbParam['olct_idx']	= $olct_idx;
						$dbParam['code']		= $sublocationCode;
						
						$model->insert ($dbParam);
						$good = ($model->getInsertID() > 0);
					}
					
					$returnData = [
						'good'	=> $good
					];
					
					if ($good) $requestResponse['status']	= 200;
					else {
						$requestResponse['status']	= 500;
						$requestResponse['message']	= 'Updating sublocation data failed!';
					}
				}
				break;
			case 'mgroup-retrieve':
				$model = $this->initModel('UserGroupsModel');
				$returnData = [
					'mgroups' => $model->findAll (),
					'mgroups-header' => $model->getColumnHeader ()
				];
				$requestResponse['status'] = 200;
				break;
			case 'userform-data':
				$model = $this->initModel('UserGroupsModel');
				$mgroups = $model->findAll ();
				$model = $this->initModel('LocationModel');
				$mlocations = $model->findAll ();
				$returnData = [
					'mgroups' => $mgroups,
					'mlocs' => $mlocations
				];
				$requestResponse['status'] = 200;
				break;
			case 'userupdate':
				$dataTransmit = $this->getDataTransmit();
				if ($dataTransmit == NULL) $requestResponse['status'] = 500;
				else {
					$userIdx		= $dataTransmit['data-loggedousr'];
					$model			= $this->initModel('EnduserModel');
					$inputUserId		= $dataTransmit['userid'];
					$dataParams		= $dataTransmit['param'];
					if ($inputUserId == 0) { // new user
						$newUsername	= $dataParams['new-username'];
						$newUseremail	= $dataParams['new-email'];
						$ousr			= $model->where ('username', $newUsername)->orWhere ('email', $newUseremail)->find ();
						if (count ($ousr) > 0) {
							$requestResponse['status'] = 500;
							$requestResponse['message'] = 'User ' . $newUsername . ' or ' . $newUseremail . ' already taken!';
						} else {
							$insertParam = [
								'ougr_idx'		=> $dataParams['new-usergroup'],
								'username'		=> $newUsername,
								'email'			=> $newUseremail,
								'password'		=> password_hash($dataParams['new-password'], PASSWORD_BCRYPT),
								'created_by'	=> $userIdx,
								'updated_by'	=> $userIdx,
								'updated_date'	=> date ('Y-m-d H:i:s')
							];
							$model->insert ($insertParam);
							$insertUserID = $model->getInsertID ();
							
							$model = $this->initModel('EnduserLocationModel');
							$insertParam = [
								'ousr_idx'		=> $insertUserID,
								'olct_idx'		=> $dataParams['new-accesslocation'],
								'status'		=> 'assigned',
								'created_by'	=> $userIdx,
								'updated_by'	=> $userIdx,
								'updated_date'	=> date ('Y-m-d H:i:s')
							];
							$model->insert ($insertParam);
							
							$model = $this->initModel('EnduserProfileModel');
							$insertParam = [
								'idx'			=> $insertUserID,
								'fname'			=> '',
								'mname'			=> '',
								'lname'			=> '',
								'address1'		=> '',
								'address2'		=> '',
								'email'			=> $newUseremail,
								'created_by'	=> $userIdx,
								'updated_by'	=> $userIdx,
								'updated_date'	=> date ('Y-m-d H:i:s')
							];
							$model->insert ($insertParam);
							
							$returnData = [
								'message'	=> 'User Created!'
							];
							$requestResponse['status']	= 200;
						}
					} else {
						$updateParams	= [
							'email'		=> $dataParams['update-email'],
							'password'	=> password_hash($dataParams['update-password'], PASSWORD_BCRYPT),
							'updated_by'	=> $userIdx
						];
						$model->update ($inputUserId, $updateParams);
						$returnData	= [
							'message'	=> 'User Updated!'
						];
					}
					$requestResponse['status'] = 200;
				}
				break;
			case 'movereq-documents':
				$dataTransmit	= $this->getDataTransmit();
				$ousrIdx		= $dataTransmit['data-loggedousr'];
				$model			= $this->initModel('EnduserModel');
				$ousr			= $model->select ('ousr.username, usr1.olct_idx')->join ('usr1', 'ousr.idx=usr1.ousr_idx')->find ($ousrIdx);
				$username		= $ousr->username;
				$ousrOlct		= $ousr->olct_idx;
				$model	= $this->initModel('LocationModel');
				$locations = [];
				$olcts	= $model->find ();
				foreach ($olcts as $olct) $locations[$olct->idx] = ['code' => $olct->code, 'name' => $olct->name];
				
				$model			= $this->initModel ('AssetItemModel');
				$oitas			= $model->select ('code, name')->groupBy ('code')->find ();
				$assets			= [];
				foreach ($oitas as $oita) $assets[$oita->code] = $oita->name;
				
				$allCount		= 0;
				
				$model			= $this->initModel('AssetMoveOutRequestModel');
				if ($ousrOlct > 0) {
					$model			= $this->initModel ('AssetMoveOutRequestModel');
					$omvrs			= $model->select ('omvr.docnum, omvr.docdate, \'' . AssetMoveOutRequestModel::DOCCODE . '\' as `type`, ousr.username, olct.name as `location_name`, omvr.status')
										->join ('omvo', 'omvr.omvo_refidx=omvo.idx')->join ('ousr', 'omvo.ousr_applicant=ousr.idx')->join ('olct', 'omvr.olct_to=olct.idx')
										->where ('omvr.olct_to', $ousrOlct)->find ();
					
					$model			= $this->initModel ('AssetRequisitionModel');
					$orqns			= $model->select ('orqn.docnum, orqn.docdate, \'' . AssetRequisitionModel::DOCCODE . '\' as `type`, ousr.username, olct.name as `location_name`, orqn.status')
										->join ('ousr', 'orqn.ousr_applicant=ousr.idx')->join ('olct', 'orqn.olct_idx=olct.idx')
										->where ('orqn.olct_idx', $ousrOlct)->find ();
										
					$model			= $this->initModel ('AssetRemovalModel');
					$oarvs			= $model->select ('oarv.docnum, oarv.docdate, \'' . AssetRemovalModel::DOCCODE . '\' as `type`, ousr.username, olct.name as `location_name`, oarv.status')
										->join ('ousr', 'oarv.ousr_applicant=ousr.idx')->join ('olct', 'oarv.olct_from=olct.idx')
										->where ('oarv.olct_from', $ousrOlct)->find ();
				} else {
					$model			= $this->initModel ('AssetMoveOutRequestModel');
					$omvrs			= $model->select ('omvr.docnum, omvr.docdate, \'' . AssetMoveOutRequestModel::DOCCODE . '\' as `type`, ousr.username, olct.name as `location_name`, omvr.status')
										->join ('omvo', 'omvr.omvo_refidx=omvo.idx')->join ('ousr', 'omvo.ousr_applicant=ousr.idx')->join ('olct', 'omvr.olct_to=olct.idx')->find ();
					
					$model			= $this->initModel ('AssetRequisitionModel');
					$orqns			= $model->select ('orqn.docnum, orqn.docdate, \'' . AssetRequisitionModel::DOCCODE . '\' as `type`, ousr.username, olct.name as `location_name`, orqn.status')
										->join ('ousr', 'orqn.ousr_applicant=ousr.idx')->join ('olct', 'orqn.olct_idx=olct.idx')->find ();
										
					$model			= $this->initModel ('AssetRemovalModel');
					$oarvs			= $model->select ('oarv.docnum, oarv.docdate, \'' . AssetRemovalModel::DOCCODE . '\' as `type`, ousr.username, olct.name as `location_name`, oarv.status')
										->join ('ousr', 'oarv.ousr_applicant=ousr.idx')->join ('olct', 'oarv.olct_from=olct.idx')->find ();
				}
				
				$allCount		= count ($oarvs) + count ($omvrs) + count ($orqns);
				$omvrsCount		= count ($omvrs);
				$orqnsCount		= count ($orqns);
				$oarvsCount		= count ($oarvs);
				
				$requestDocuments = [
					'mvorequest'	=> [],
					'requisition'	=> [],
					'removal'		=> []
				];
				
				foreach ($omvrs as $omvr) array_push ($requestDocuments['mvorequest'], $omvr);
				foreach ($orqns as $orqn) array_push ($requestDocuments['requisition'], $orqn);
				foreach ($oarvs as $oarv) array_push ($requestDocuments['removal'], $oarv);
				
				$returnData = [
					'summaries'		=> [
						[
							'id'		=> 'request-total',
							'title'		=> '{5}',
							'content'	=> $allCount,
							'style'		=> 'text-white bg-primary'
						],
						[
							'id'		=> 'request-new',
							'title'		=> '{6}',
							'content'	=> $orqnsCount,
							'style'		=> 'text-white bg-info'
						],
						[
							'id'		=> 'request-move`',
							'title'		=> '{7}',
							'content'	=> $omvrsCount,
							'style'		=> 'text-white bg-success'
						],
						[
							'id'		=> 'request-destroy',
							'title'		=> '{8}',
							'content'	=> $oarvsCount,
							'style'		=> 'text-white bg-secondary'
						]
					],
					'dataTransmit'	=> [
						'data-requests'		=> $requestDocuments,
						'data-username'		=> $username,
						'data-userlocation'	=> $ousrOlct,
						'data-locations'	=> $locations,
						'data-assets'		=> $assets
					],
					'docStats'	=> $this->docstats,
					'docTypes'	=> [
						'01'		=> 'Aset Keluar',
						'02'		=> 'Aset Masuk',
						'03'		=> 'Permintaan Pindah',
						'04'		=> 'Permintaan Baru',
						'10'		=> 'Permintaan Pemusnahan'
					]
				];
				$requestResponse['status']	= 200;
				break;
			case 'newassetreq-imageupload':
				$file = $this->request->getFile ('data-file');
				$secureName = $file->getRandomName ();
				$path = $file->store (OsamModule::IMAGEWRITEPATH, $secureName);
				if (strlen($path) == 0) $returnData = [
					'good'	=> FALSE
				];
				else $returnData = [
					'good'	=> TRUE,
					'data-filename'	=> $secureName
				];
				$requestResponse['status'] = 200;
				break;
			case 'newassetreq-requisition':
				$dataTransmit		= $this->getDataTransmit();
				$ousrIdx		= $dataTransmit['data-loggedousr'];
				$formData		= $dataTransmit['data-formnewasset'];
				$model			= $this->initModel('ApplicationSettingsModel');
				$numberingFormat	= $model->find ('numbering')->tag_value;
				$numberingReset		= $model->find ('numbering-periode')->tag_value;
				$document		= new Document ($numberingFormat, $numberingReset);
				$model			= $this->initModel('AssetRequisitionModel');
				$orqns			= $model->orderBy ('idx', 'DESC')->find ();
				$lastDocnum		= count ($orqns) > 0 ? $orqns[0]->docnum : NULL;
				$insertParam		= [
					'docnum'			=> $document->generateDocnum(AssetRequisitionModel::DOCCODE, $lastDocnum),
					'docdate'			=> date ('Y-m-d H:i:s'),
					'requisition_type'	=> AssetRequisitionModel::REQTYPENEW,
					'olct_idx'			=> $formData['requisition-location'],
					'ousr_applicant'	=> $ousrIdx,
					'approved_by'		=> 0,
					'approval_date'		=> NULL,
					'status'			=> 1,
					'comments'			=> '',
					'created_by'		=> $ousrIdx,
					'updated_by'		=> $ousrIdx,
					'updated_date'		=> date ('Y-m-d H:i:s')
				];
				
				$model->insert ($insertParam);
				$insertID = $model->getInsertID ();
				$good = ($insertID > 0);
				
				if (!$good) {
					$returnData	= [
						'good'		=> $good,
						'message'	=> 'Error! Database Insertion New Document Error!'
					];
					$requestResponse['status'] = 500;
				} else {
					$rqn2	= [
						'orqn_idx'	=> $insertID,
						'name'		=> $formData['name'],
						'dscript'	=> $formData['description'],
						'est_value'	=> $formData['valueestimation'],
						'qty'		=> $formData['requestqty'],
						'remarks'	=> $formData['remarks'],
						'imgs'		=> $formData['imagenames'],
						'created_by'	=> $ousrIdx,
						'updated_by'	=> $ousrIdx,
						'updated_date'	=> date ('Y-m-d H:i:s')
					];
					$model	= $this->initModel('AssetRequisitionNewModel');
					$model->insert ($rqn2);
					
					$good	= ($model->getInsertID () > 0);
					if (!$good) {
						$returnData	= [
							'good'		=> $good,
							'message'	=> 'Error! Database Insertion Document Detail Error!'
						];
						$requestResponse['status'] = 500;
					} else {
						$returnData = [
							'good'		=> $good,
							'message'	=> 'New Asset Requisition Request Created!',
							'data'		=> $insertID
						];
						$requestResponse['status'] = 200;
					}
				}
				break;
			case 'existing-requisition':
				$dataTransmit		= $this->getDataTransmit();
				$ousrIdx		= $dataTransmit['data-loggedousr'];
				$formData		= $dataTransmit['data-formrequest'];
				$model			= $this->initModel('ApplicationSettingsModel');
				$numberingFormat	= $model->find ('numbering')->tag_value;
				$numberingReset		= $model->find ('numbering-periode')->tag_value;
				$document		= new Document ($numberingFormat, $numberingReset);
				
				$model			= $this->initModel('AssetRequisitionModel');
				$orqns			= $model->orderBy ('idx', 'DESC')->find ();
				$lastDocnum		= count ($orqns) > 0 ? $orqns[0]->docnum : NULL;
				$insertParam		= [
					'docnum'		=> $document->generateDocnum(AssetRequisitionModel::DOCCODE, $lastDocnum),
					'docdate'		=> date ('Y-m-d H:i:s'),
					'requisition_type'	=> AssetRequisitionModel::REQTYPEEXT, 
					'olct_idx'		=> $formData['data-locationidx'],
					'ousr_applicant'	=> $ousrIdx,
					'approved_by'		=> 0,
					'approval_date'		=> NULL,
					'status'		=> 1,
					'comments'		=> '',	
					'created_by'		=> $ousrIdx,
					'updated_by'		=> $ousrIdx,
					'updated_date'		=> date ('Y-m-d H:i:s')
				];
				
				$rqn1s	=	[];
				$additionData	= $formData['data-additions'];
				$idx = 0;
				foreach ($additionData as $lineData) {
					$rqn1	= [
						'orqn_idx'	=> 0,
						'code'		=> $lineData['code'],
						'qty'		=> $lineData['qty'],
						'remarks'	=> $lineData['remarks'],
						'created_by'	=> $ousrIdx,
						'updated_by'	=> $ousrIdx,
						'updated_date'	=> date ('Y-m-d H:i:s')
					];
					$rqn1s[$idx] = $rqn1;
					$idx++;
				}
				
				$model->insert ($insertParam);
				$docidx = $model->getInsertID ();
				$good = ($docidx > 0);
				
				if (!$good) {
					$returnData = [
						'message'	=> 'Error! Data insertion cancelled!'
					];
					$requestResponse['status'] = 500;
				} else {
					$model	= $this->initModel('AssetRequisitionExistModel');
					$good 	= false;
					foreach ($rqn1s as $rqn1) {
						$rqn1['orqn_idx'] = $docidx;
						$model->insert ($rqn1);
						$good = ($model->getInsertID () > 0);
					}
				}
				
				if (!$good) {
					$returnData = [
						'message'	=> 'Error! Data insertion cancelled!'
					];
					$requestResponse['status'] = 500;
				} else {
					$returnData = [
						'good' => $good
					];
					$requestResponse['status']	= 200;
				}
				break;
			case 'moveout-request':
				$dataTransmit		= $this->getDataTransmit();
				$ousrIdx		= $dataTransmit['data-loggedousr'];
				$model			= $this->initModel('ApplicationSettingsModel');
				$numberingFormat	= $model->find ('numbering')->tag_value;
				$numberingReset		= $model->find ('numbering-periode')->tag_value;
				$document		= new Document ($numberingFormat, $numberingReset);
				
				$model			= $this->initModel('AssetMoveOutModel');
				$omvos			= $model->orderBy ('idx', 'DESC')->find ();
				$lastDocnum		= count ($omvos) > 0 ? $omvos[0]->docnum : NULL;
				$insertParam		= [
					'docnum'		=> $document->generateDocnum(AssetMoveOutModel::DOCCODE, $lastDocnum),
					'docdate'		=> date ('Y-m-d H:i:s'),
					'olct_from'		=> 0,
					'olct_to'		=> 0,
					'ousr_applicant'	=> $ousrIdx,
					'approved_by'		=> 0,
					'approval_date'		=> NULL,
					'sent_by'		=> 0,
					'sent_date'		=> NULL,
					'received_by'		=> 0,
					'received_date'		=> NULL,
					'status'		=> 1,
					'status_comment'	=> '',
					'created_by'		=> $ousrIdx,
					'updated_by'		=> $ousrIdx,
					'updated_date'		=> date ('Y-m-d H:i:s')
				];
				
				$mvo1Param = [];
				
				foreach ($dataTransmit['data-formrequest'] as $formdata) {
					$paramName = $formdata['name'];
					switch ($paramName) {
						default:
							$strPos = strpos($paramName, 'itemmove-qty-');
							if (!is_bool($strPos)) {
								$itemId = str_replace('itemmove-qty-', '', $paramName);
								$paramLine = [
									'omvo_idx'	=> 0,
									'oita_idx'	=> intval($itemId),
									'olct_idx'	=> 0,
									'osbl_idx'	=> 0,
									'qty'		=> intval ($formdata['value']),
									'created_by'	=> $ousrIdx,
									'updated_by'	=> $ousrIdx,
									'updated_date'	=> date ('Y-m-d H:i:s')
								];
								array_push($mvo1Param, $paramLine);
							}
							break;
						case 'moveout-fromlocation-hidden':
							$insertParam['olct_from'] = $formdata['value'];
							break;
						case 'moveout-tolocation-hidden':
							$insertParam['olct_to'] = $formdata['value'];
							break;
					}
				}
				
				$model->insert ($insertParam);
				$insertId = $model->getInsertID();
				
				$model		= $this->initModel('AssetItemModel');
				for ($id = 0; $id < count ($mvo1Param); $id++) {
					$oita	= $model->find ($mvo1Param[$id]['oita_idx']);
					$mvo1Param[$id]['omvo_idx']	= $insertId;
					$mvo1Param[$id]['olct_idx']	= $insertParam['olct_from'];
					$mvo1Param[$id]['osbl_idx']	= $oita->osbl_idx;
				}
				
				$model		= $this->initModel('AssetMoveOutDetailModel');
				foreach ($mvo1Param as $param) $model->insert ($param);
				
				$model		= $this->initModel('AssetMoveOutRequestModel');
				$omvrs		= $model->orderBy ('idx', 'DESC')->find ();
				$lastOmvrDocNum = count ($omvrs) > 0 ? $omvrs[0]->docnum : NULL;
				
				$mvrInsertParam	= [
					'docnum'	=> $document->generateDocnum(AssetMoveOutRequestModel::DOCCODE, $lastOmvrDocNum),
					'docdate'	=> $insertParam['docdate'],
					'omvo_refidx'	=> $insertId,
					'olct_from'	=> $insertParam['olct_from'],
					'olct_to'	=> $insertParam['olct_to'],
					'status'	=> $insertParam['status'],
					'created_by'	=> $insertParam['created_by'],
					'updated_by'	=> $insertParam['updated_by'],
					'updated_date'	=> date ('Y-m-d H:i:s')
				];
				
				$model->insert ($mvrInsertParam);
				$omvr_idx	= $model->getInsertID ();
				$documents	= $model->select ('omvr.docnum, omvo.docnum as `docnum_transfer`, omvo.docdate, olct.name as `docfrom`, ousr.username, usr3.fname as `front_name`')
								->join ('omvo', 'omvo.idx=omvr.omvo_refidx')->join ('olct', 'olct.idx=omvo.olct_from')
								->join ('ousr', 'ousr.idx=omvo.ousr_applicant')->join ('usr3', 'usr3.idx=omvo.ousr_applicant')
								->where ('omvr.idx', $omvr_idx)->find ();
				
				$good = ($omvr_idx > 0 && count ($documents) > 0);
				
				if ($good) {
					$document	= $documents[0];
					$msgType	= 'move-00';
					$msgParam	= [
						'document'	=> [
							'docnum'		=> $document->docnum,
							'docnum_transfer'	=> $document->docnum_transfer,
							'docdate'		=> $document->docdate,
							'docfrom'		=> $document->docfrom,
							'docapp'		=> ($document->fname == '') ? $document->username : $document->fname
						]
					];
					
					$subject	= sprintf ($emailMsgs->getSubject ($msgType), $document->docnum);
					$template	= view ('emails/osam/' . $msgType, $msgParam);
					
					$emails		= array ();
					
					$model		= $this->initModel ('EnduserModel');
					$ousr_admins	= $model->whereIn ('idx', [1,2,3])->find ();
					
					foreach ($ousr_admins as $ousr_admin) array_push ($emails, $ousr_admin->email);
					$ousr_managers	= $model->join ('usr1', 'usr1.ousr_idx=ousr.idx')->join ('ougr', 'ougr.idx=ousr.ougr_idx')
								->where ('usr1.olct_idx', $insertParam['olct_from'])->where ('ougr.can_approve', TRUE)->find ();
								
					foreach ($ousr_managers as $ousr_manager) array_push ($emails, $ousr_manager->email);
					
					$emailTools->emailNotifTools ($emails, $subject, $template, OsamModule::EMAIL_FROM, OsamModule::EMAIL_NAME_FROM);
					$emailTools->emailSend ();
				}
				
				$returnData = [
					'good'		=> $good
				];
				$requestResponse['status']	= 200;
				break;
			case 'movein-documents':
				$dataTransmit = $this->getDataTransmit();
				$ousrIdx = $dataTransmit['data-loggedousr'];
				$model = $this->initModel('EnduserModel');
				$ousr = $model->join ('ougr', 'ousr.ougr_idx=ougr.idx')->join ('usr1', 'ousr.idx=usr1.ousr_idx')->find ($ousrIdx);
				$ousrOlct = $ousr->olct_idx;
				
				$model = $this->initModel('AssetMoveInModel');
				if ($ousrOlct > 0) {
					$allCount = count ($model->where ('omvo_olctto', $ousrOlct)->find ());
					$pendingCount = count ($model->where ('omvo_olctto', $ousrOlct)->where ('sent', FALSE)->find ());
					$sentCount = count ($model->where ('omvo_olctto', $ousrOlct)->where ('sent', TRUE)->find ());
					$doneCount = count ($model->where ('omvo_olctto', $ousrOlct)->where ('sent', TRUE)->where ('received_date <>', NULL)->find ());
					$mvis = $model->select ('omvi.idx, omvi.docnum, omvi.docdate, omvi.omvo_refidx, omvi.omvo_olctfrom, omvi.omvo_olctto, ousr.username, omvo.status')
								->join ('ousr', 'omvi.omvo_ousridx=ousr.idx')->join ('omvo', 'omvi.omvo_refidx=omvo.idx')
								->where ('omvi.omvo_olctto', $ousrOlct)->find ();
				} else {
					$allCount = count ($model->find ());
					$pendingCount = count ($model->where ('sent', FALSE)->find ());
					$sentCount = count ($model->where ('sent', TRUE)->find ());
					$doneCount = count ($model->where ('sent', TRUE)->where ('received_date <>', NULL)->find ());
					$mvis = $model->select ('omvi.idx, omvi.docnum, omvi.docdate, omvi.omvo_refidx, omvi.omvo_olctfrom, omvi.omvo_olctto, ousr.username, omvo.status')
								->join ('ousr', 'omvi.omvo_ousridx=ousr.idx')->join ('omvo', 'omvi.omvo_refidx=omvo.idx')->find ();
				}
				
				$model = $this->initModel('AssetMoveOutDetailModel');
				$mvisDetails = [];
				foreach ($mvis as $idx => $mvi) {
					$omvo_idx		= $mvi->omvo_refidx;
					$omvo_details	= $model->select ('mvo1.line_idx, oita.idx as `item_idx`, oita.code, oita.name, osbl.name as `osbl_name`, mvo1.qty')
											->join ('oita', 'mvo1.oita_idx=oita.idx')->join ('osbl', 'mvo1.osbl_idx=osbl.idx')
											->where ('mvo1.omvo_idx', $omvo_idx)->find ();
					$detailRow = [
						'dataOmvoIdx'		=> $omvo_idx,
						'dataOmvoDetail'	=> []
					];
					foreach ($omvo_details as $omvo_detail) array_push($detailRow['dataOmvoDetail'], $omvo_detail->toArray ());
					$mvisDetails[$idx] = $detailRow;
				}
				
				$model = $this->initModel('LocationModel');
				$olcts = $model->select ('idx, name')->find ();
				$locations = [];
				$sublocations = [];
				$model = $this->initModel('SublocationModel');
				foreach ($olcts as $olct) {
					$locations[$olct->idx] = $olct->name;
					$sublocationData = [
						$olct->idx	=> []
					];
					$osblsData = $model->select ('idx, code, name')->where ('olct_idx', $olct->idx)->find ();
					foreach ($osblsData as $osblData) {
						$sublocation = [
							'osbl_idx'	=> $osblData->idx,
							'info'		=> [
								'code'		=> $osblData->code,
								'name'		=> $osblData->name
							]
						];
						array_push($sublocationData[$olct->idx], $sublocation);
					}
					array_push($sublocations, $sublocationData);
				}
				
				$returnData = [
					'mvisSum'	=> [
						[
							'id'		=> 'movein-count',
							'title'		=> '{3}',
							'content'	=> $allCount,
							'style'		=> 'text-white bg-danger'
						],
						[
							'id'		=> 'movein-waitsend',
							'title'		=> '{4}',
							'content'	=> $pendingCount,
							'style'		=> 'text-white bg-info'
						],
						[
							'id'		=> 'movein-sent',
							'title'		=> '{5}',
							'content'	=> $sentCount,
							'style'		=> 'text-white bg-secondary'
						],
						[
							'id'		=> 'movein-received',
							'title'		=> '{6}',
							'content'	=> $doneCount,
							'style'		=> 'text-white bg-success'
						]
					],
					'locations'		=> $locations,
					'mvisList'		=> $mvis,
					'mvisDetails'	=> $mvisDetails,
					'mvisRcvs'		=> [],
					'docStats'		=> $this->docstats
				];
				
				$requestResponse['status'] = 200;
				break;
			case 'movein-documentdetailed':
				$dataTransmit = $this->getDataTransmit();
				if (!array_key_exists('data-docnum', $dataTransmit)) ;
				else {
					$ousrIdx	= $dataTransmit['data-loggedousr'];
					$docnum		= $dataTransmit['data-docnum'];
					
					$model		= $this->initModel('AssetMoveInModel');
					$omvi		= $model->select ('omvi.idx, omvi.docnum, omvi.sent, omvi.docdate, omvi.received_by, omvi.received_date, ' .
									'omvi.distributed_by, omvi.distributed_date, omvi.omvo_refidx, omvo.docnum as `ref_docnum`, ' .
									'omvo.docdate as `ref_docdate`, ousr.username, omvi.omvo_olctfrom, omvi.omvo_olctto, omvi.sent_by, omvi.sent_date')
									->join ('omvo', 'omvi.omvo_refidx=omvo.idx')->join ('ousr', 'omvi.omvo_ousridx=ousr.idx')
									->where ('omvi.docnum', $docnum)->find ();
					$document		= $omvi[0];
					$omviIdx		= $document->idx;
					$refDocIdx		= $document->omvo_refidx;
					$refOlctTo		= $document->omvo_olctto;
					$moveinSent		= ($document->sent == 1);
					$moveinReceived 	= ($document->received_by > 0);
					$moveinDistribute	= ($document->distributed_by > 0);
					
					if ($moveinReceived) $omviThs = [
						'id'	=> ['#', 'Barcode', 'Deskripsi', 'Sublokasi Asal', 'Sublokasi Tujuan', 'Qty'],
						'en'	=> ['#', 'Barcode', 'Description', 'Origin Sublocation', 'Destination Sublocation', 'Qty']
					];
					else $omviThs = [
						'id'	=> ['#', 'Barcode', 'Deskripsi', 'Sublokasi Asal', 'Qty'],
						'en'	=> ['#', 'Barcode', 'Description', 'Origin Sublocation', 'Qty']
					];
					
					$model		= $this->initModel ('AssetMoveOutModel');
					$omvo		= $model->where ('idx', $refDocIdx)->find ();
					
					$moveinDetailed = [];
					if (!$moveinDistribute) {
						$model	= $this->initModel ('AssetMoveOutDetailModel');
						
						if (!$moveinReceived) $mvi1s	= $model->select ('mvo1.oita_idx, oita.code, oita.name, osbl.name as `osbl_src`, mvo1.qty');
						else $model->select ('mvo1.oita_idx, oita.code, oita.name, osbl.name as `osbl_src`, "NULL" as `osbl_dest`, mvo1.qty');
						$mvi1s = $model->join ('oita', 'mvo1.oita_idx=oita.idx')->join ('osbl', 'mvo1.osbl_idx=osbl.idx')
								->where ('mvo1.omvo_idx', $refDocIdx)->find ();
					} else {
						$model	= $this->initModel ('AssetMoveInDetailModel');
						$mvi1s = $model->select ('mvi1.oita_idx, oita.code, oita.name, oita.osbl_idx as `osbl_src`, osbl.name as `osbl_dest`, mvi1.qty')
								->join ('oita', 'oita.idx=mvi1.oita_fromidx')->join ('osbl', 'osbl.idx=mvi1.osbl_idx')
								->where ('mvi1.omvi_idx', $omviIdx)->groupBy ('mvi1.line_idx')->find ();
					}
					
					foreach ($mvi1s	as $key => $mvi1) 
					
						if (!$moveinReceived) 
							$moveinDetailed[$key]	= [
								'oita_idx'		=> $mvi1->oita_idx,
								'code'			=> $mvi1->code,
								'name'			=> $mvi1->name,
								'sublocation_src'	=> $mvi1->osbl_src,
								'qty'			=> $mvi1->qty
							];
						else 
							$moveinDetailed[$key]	= [
								'oita_idx'		=> $mvi1->oita_idx,
								'code'			=> $mvi1->code,
								'name'			=> $mvi1->name,
								'sublocation_src'	=> $mvi1->osbl_src,
								'sublocation_dest'	=> $mvi1->osbl_dest,
								'qty'			=> $mvi1->qty
							];
									
					$model		= $this->initModel('EnduserModel');
					$ousr		= $model->join ('usr1', 'ousr.idx=usr1.ousr_idx')->find ($ousrIdx);
					$ousrOlct	= $ousr->olct_idx;
					$ousrs		= $model->select ('idx, username')->find ();
					$users		= [];
					foreach ($ousrs as $ousr) $users[$ousr->idx] = $ousr->username;
					
					$model		= $this->initModel('LocationModel');
					$olcts		= $model->select ('idx, name')->find ();
					$locations	= [];
					foreach ($olcts as $olct) $locations[$olct->idx] = $olct->name;
					
					$sublocations = [];
					if ($moveinDistribute) {
						$model		= $this->initModel ('SublocationModel');
						for ($index = 0; $index < count ($moveinDetailed); $index++) {
							$osbl_src	= $moveinDetailed[$index]['sublocation_src'];
							$osbl		= $model->find ($osbl_src);
							$moveinDetailed[$index]['sublocation_src'] = $osbl->name;
						}
					}
					
					$returnData = [
						'good'		=> TRUE,
						'dataTransmit'	=> [
							'data-locationfrom' 	=> $locations[$document->omvo_olctfrom],
							'data-locationto'	=> $locations[$document->omvo_olctto],
							'data-usersent'		=> ($document->sent_by == 0 ? 'Belum Dikirim' : $users[$document->sent_by]),
							'data-userreceived'	=> ($document->sent_by == 0 ? 'Belum Dikirim' : ($document->received_by == 0 ? 'Belum Diterima' : $users[$document->received_by])),
							'data-userdistribute'	=> ($document->distributed_by == 0 ? 'Belum Didistribusikan' : $users[$document->distributed_by]),
							'data-movein'		=> $document,
							'data-moveinheads'	=> $omviThs,
							'data-moveindetail'	=> $moveinDetailed,
						],
						'docnum'	=> $docnum,
						'isSent'	=> $moveinSent,
						'isReceived'	=> $moveinReceived,
						'isDistributed'	=> $moveinDistribute,
						'btnClose'	=> 'Tutup',
						'btnReceived'	=> 'Diterima',
						'statusText'	=> [
							'ns'		=> [
								'id'		=> 'Belum Dikirim',
								'en'		=> 'Not Sent'
							],
							'nr'		=> [
								'id'		=> 'Belum Diterima',
								'en'		=> 'Not Received'
							],
							'nd'		=> [
								'id'		=> 'Belum Didistribusikan',
								'en'		=> 'Not Distributed'
							]
						],
						'titles'		=> [
							'doctitle'		=> [
								'id'			=> 'Dokumen Kedatangan Aset',
								'en'			=> 'Incoming Asset Document'
							],
							'refdoctitle'		=> [
								'id'			=> 'Referensi Dokumen Perpindahan Aset',
								'en'			=> 'Asset Transfer Document Reference'
							],
							'refdocdetail'		=> [
								'id'			=> 'Detil Referensi Dokumen Perpindahan Aset',
								'en'			=> 'Asset Transfer Document Reference Details'
							]
						],
						'labels'		=> [
							'docnum'		=> [
								'id'			=> 'No. Dokumen:',
								'en'			=> 'Document No.:'
							],
							'docdate'		=> [
								'id'			=> 'Tgl. Dokumen:',
								'en'			=> 'Document Date:'
							],
							'received_by'		=> [
								'id'			=> 'Penerima:',
								'en'			=> 'Receiver:'
							],
							'received_date'		=> [
								'id'			=> 'Tgl. Penerimaan:',
								'en'			=> 'Received Date:'
							],
							'ref_docnum'		=> [
								'id'			=> 'No. Dokumen Referensi:',
								'en'			=> 'Reference Document No:'
							],
							'ref_docdate'		=> [
								'id'			=> 'Tgl. Dokumen Referensi:',
								'en'			=> 'Reference Document Date:'
							],
							'username'		=> [
								'id'			=> 'Pembuat:',
								'en'			=> 'Created By:'
							],
							'omvo_olctfrom'		=> [
								'id'			=> 'Lokasi Asal:',
								'en'			=> 'Origin:'
							],
							'omvo_olctto'		=> [
								'id'			=> 'Lokasi Tujuan:',
								'en'			=> 'Destination:'
							],
							'sent_by'		=> [
								'id'			=> 'Telah Dikirim Oleh:',
								'en'			=> 'Sent By:'
							],
							'sent_date'		=> [
								'id'			=> 'Tgl. Pengiriman:',
								'en'			=> 'Sent Date:'
							],
							'distributed_by'	=> [
								'id'			=> 'Didistribusikan Oleh:',
								'en'			=> 'Distributed By:'
							],
							'distributed_date'	=> [
								'id'			=> 'Tgl. Pendistribusian;',
								'en'			=> 'Distribution Date:'
							]
						]
					];
				}
				$requestResponse['status'] = 200;
				break;
			case 'movein-doaction':
				$dataTransmit = $this->getDataTransmit();
				if (!array_key_exists('docnum', $dataTransmit)) {
					$returnData = [
						'message'	=> 'Error! Missing required parameters!'
					];
					$requestResponse['status'] = 500;
				} else {
					$ousrIdx	= $dataTransmit['data-loggedousr'];
					$docnum		= $dataTransmit['docnum'];
					$model		= $this->initModel('AssetMoveInModel');
					$omvi		= $model->where ('docnum', $docnum)->find ();
					$now		= date ('Y-m-d H:i:s');
					if (count ($omvi) == 0) {
						$returnData = [
							'message'	=> 'Error! Document ' . $docnum . ' was not found!'
						];
						$requestResponse['status'] = 500;
					} else {
						$omviIdx	= $omvi[0]->idx;
						$omvo_refidx	= $omvi[0]->omvo_refidx;
						$updateParams	= [
							'received_by'	=> $ousrIdx,
							'received_date'	=> $now,
							'updated_by'	=> $ousrIdx,
							'updated_date'	=> $now
						];
						$model->update ($omviIdx, $updateParams);
						$updated = $model->affectedRows ();
						
						$updateParam	= [
							'received_by'	=> $ousrIdx,
							'received_date'	=> $now,
							'status'	=> 4,
							'updated_by'	=> $ousrIdx,
							'updated_date'	=> $now
						];
						
						$model	= $this->initModel ('AssetMoveOutRequestModel');
						$omvr	= $model->where ('omvo_refidx', $omvo_refidx)->where ('status', 3)->find ();
						if (count ($omvr) > 0) {
							$omvr_idx = $omvr[0]->idx;
							$updateParams = [
								'status'	=> 4,
								'updated_by'	=> $ousrIdx,
								'updated_date'	=> date ('Y-m-d H:i:s')
							];
							$model->update ($omvr_idx, $updateParams);
							$updated += $model->affectedRows ();
						}
						
						$model	= $this->initModel('AssetMoveOutModel');
						$model->update ($omvo_refidx, $updateParam);
						$updated += $model->affectedRows ();
						
						$omvo	= $model->select ('omvo.idx, omvo.docnum, omvo.docdate, ousr.username, usr3.fname, omvo.olct_from, omvo.olct_to')
								->join ('ousr', 'ousr.idx=omvo.received_by')->join ('usr3', 'usr3.idx=omvo.received_by')->where ('omvo.idx', $omvo_refidx)->find ()[0];
						
						if ($updated == 0) {
							$returnData = [
								'good'		=> FALSE,
								'message'	=> 'Error! Data updates failed'
							];
							$requestResponse['status']	= 500;
						} else {
							$olct_from	= $omvo->olct_from;
							$olct_to	= $omvo->olct_to;
							$msgType	= 'move-04';
							$msgParams	= [
								'document'	=> [
									'docnum'	=> $omvo->docnum,
									'docdate'	=> $omvo->docdate,
									'docrcv'	=> ($omvo->fname == '') ? $omvo->username : $omvo->fname,
									'docrcvtime'	=> date ('Y-m-d H:i:s')
								]
							];
							
							
							$subject	= sprintf ($emailMsgs->getSubject ($msgType), $omvo->docnum, $msgParams['document']['docrcv']);
							$template	= view ('emails/osam/' . $msgType, $msgParams);
							
							$emails		= array ();
							$ousr_model	= $this->initModel ('EnduserModel');
							$recipients	= $ousr_model->whereIn ('idx', $administrators)->find ();
							
							foreach ($recipients as $recipient) array_push ($emails, $recipient->email);
							
							$recipients	= $ousr_model->join ('usr1', 'usr1.ousr_idx=ousr.idx')->whereIn ('usr1.olct_idx', [$olct_from, $olct_to])->find ();
							
							foreach ($recipients as $recipient) array_push ($emails, $recipient->email);
							
							$emailTools->emailNotifTools ($emails, $subject, $template, OsamModule::EMAIL_FROM, OsamModule::EMAIL_NAME_FROM);
							$emailTools->emailSend ();
							
							$returnData = [
								'good'		=> TRUE,
								'message'	=> 'success'
							];
							$requestResponse['status']	= 200;
						}
					}
				}
				break;
			case 'moveindo-assetdistribution':
				$good = FALSE;
				$dataTransmit = $this->getDataTransmit();
				$ousrIdx = $dataTransmit['data-loggedousr'];
				$mviDocNum = $dataTransmit['data-mvidocnum'];
				
				$omvi_model	= $this->initModel ('AssetMoveInModel');
				$omvis		= $omvi_model->select ('omvi.*, omvo.docnum as `omvo_docnum`, omvo.docdate as `omvo_docdate`')->join ('omvo', 'omvo.idx=omvi.omvo_refidx')->where ('omvi.docnum', $mviDocNum)->find ();
				$mvi1Sum 	= 0;
				$message 	= '';
				
				if (count ($omvis) == 0) $good = FALSE;
				else {
					$omvi		= $omvis[0];
					$omvo_docnum	= $omvi->omvo_docnum;
					$omvo_docdate	= $omvi->omvo_docdate;
					$omvi_idx	= $omvi->idx;
					$omvo_refidx	= $omvi->omvo_refidx;
					$omvo_olctfrom	= $omvi->omvo_olctfrom;
					$omvo_olctto	= $omvi->omvo_olctto;
					$now		= date ('Y-m-d H:i:s');
					
					$oita_model	= $this->initModel ('AssetItemModel');
					$mvi1_model	= $this->initModel ('AssetMoveInDetailModel');
					
					$mviParams	= $dataTransmit['data-mviparams'];
					
					foreach ($mviParams as $mviParam) {
						$oitafrom_idx	= $mviParam['oita_idx'];
						$osblto_idx	= $mviParam['osbl_idx'];
						$qty_to		= $mviParam['qty'];
						
						$oitafrom	= $oita_model->where ('idx', $oitafrom_idx)->find ();
						if (count ($oitafrom) > 0) {
							$oitacode	= $oitafrom[0]->code;
							
							$oitato		= $oita_model->where ('code', $oitacode)->where ('olct_idx', $omvo_olctto)->where ('osbl_idx', $osblto_idx)->find ();
							$updated	= 0;
							if (count ($oitato) == 0) {
								$insertParams	= [
									'olct_idx'		=> $omvo_olctto,
									'osbl_idx'		=> $osblto_idx,
									'oaci_idx'		=> $oitafrom[0]->oaci_idx,
									'oast_idx'		=> $oitafrom[0]->oast_idx,
									'code'			=> $oitacode,
									'name'			=> $oitafrom[0]->name,
									'notes'			=> '',
									'po_number'		=> '',
									'acquisition_value'	=> $oitafrom[0]->acquisition_value,
									'loan_time'		=> $oitafrom[0]->loan_time,
									'qty'			=> $qty_to
								];
								$oita_model->insert ($insertParams);
								$updated = $oita_model->getInsertID ();
								
								$ita1_model	= $this->initModel ('ItemAttributesModel');
								$ita1sfrom	= $ita1_model->where ('oita_idx', $oitafrom_idx)->find ();
								foreach ($ita1sfrom as $ita1from) {
									$insertParams	= [
										'oita_idx'	=> $updated,
										'octa_idx'	=> $ita1from->octa_idx,
										'attr_value'	=> $ita1from->attr_value
									];
									$ita1_model->insert ($insertParams);
								}
							} else {
								$oitato_idx	= $oitato[0]->idx;
								$qty_toinit	= $oitato[0]->qty;
								
								$updateParams	= [
									'qty'			=> ($qty_toinit + $qty_to)
								];
								$oita_model->update ($oitato_idx, $updateParams);
								$updated = $oitato_idx;
							}
							
							if ($updated > 0) {
								$insertParams	= [
									'omvi_idx'	=> $omvi_idx,
									'oita_fromidx'	=> $oitafrom_idx,
									'oita_idx'	=> $updated,
									'olct_idx'	=> $omvo_olctto,
									'osbl_idx'	=> $osblto_idx,
									'qty'		=> $qty_to,
									'created_by'	=> $ousrIdx,
									'updated_by'	=> $ousrIdx,
									'updated_date'	=> $now
								];
								$mvi1_model->insert ($insertParams);
							}
						}
					}
					
					$mvi1sSum	= $mvi1_model->select ('sum(qty) as `total`')->where ('omvi_idx', $omvi_idx)->find ();
					$mvi1Sum	= $mvi1sSum[0]->total;
					
					$model		= $this->initModel ('AssetMoveOutDetailModel');
					$mvo1sSum	= $model->select ('sum(qty) as `total`')->where ('omvo_idx', $omvo_refidx)->find ();
					$mvo1Sum	= $mvo1sSum[0]->total;
					
					if ($mvo1Sum == $mvi1Sum) {
						$model	= $this->initModel ('AssetMoveOutModel');
						$updateParams	= [
							'status'	=> 5,
							'updated_by'	=> $ousrIdx,
							'updated_date'	=> $now
						];
						
						$model->update ($omvo_refidx, $updateParams);
						
						$model	= $this->initModel ('AssetMoveOutRequestModel');
						$omvr	= $model->where ('omvo_refidx', $omvo_refidx)->find ();
						if (count ($omvr) > 0) {
							$updateParams	= [
								'status'	=> 5,
								'updated_by'	=> $ousrIdx,
								'updated_date'	=> $now
							];
							$model->update ($omvo_refidx, $updateParams);
							
							$updateParams	= [
								'distributed_by'	=> $ousrIdx,
								'distributed_date'	=> $now
							];
							$omvi_model->update ($omvi_idx, $updateParams);
						}
						$good = TRUE;
						$message = "Data updated";
					}
					
					if ($good) {
						$model		= $this->initModel ('EnduserModel');
						$ousr		= $model->select ('ousr.username, usr3.fname')->join ('usr3', 'usr3.idx=ousr.idx')->where ('ousr.idx', $ousrIdx)->find ()[0];
						
						$docdist	= ($ousr->fname == '') ? $ousr->username : $ousr->fname;
						$msgType	= 'move-05';
						$msgParams	= [
							'document'	=> [
								'docnum'	=> $omvo_docnum,
								'docdate'	=> $omvo_docdate,
								'docdist'	=> $docdist,
								'docdisttime'	=> $now
							]
						];
						
						$subject	= sprintf ($emailMsgs->getSubject ($msgType), $omvo_docnum, $docdist) ;
						$template	= view ('emails/osam/' . $msgType, $msgParams);
						
						$emails	= array ();
						
						$receptors	= $model->whereIn ('idx', $administrators)->find ();
						foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
						
						$receptors	= $model->join ('usr1', 'usr1.ousr_idx=ousr.idx')->whereIn ('olct_idx', [$omvo_olctfrom, $omvo_olctto])->find ();
						foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
						
						$emailTools->emailNotifTools ($emails, $subject, $template, OsamModule::EMAIL_FROM, OsamModule::EMAIL_NAME_FROM);
						$emailTools->emailSend ();
					}
				}
				
				$returnData = [
					'good'		=> $good,
					'message'	=> $message
				];
				$requestResponse['status'] = 200;
				break;
			case 'moveout-documents':
				$dataTransmit = $this->getDataTransmit();
				$ousrIdx	= $dataTransmit['data-loggedousr'];
				$euModel	= $this->initModel('EnduserModel');
				$ousr		= $euModel->join ('ougr', 'ousr.ougr_idx=ougr.idx')->join ('usr1', 'ousr.idx=usr1.ousr_idx')->find ($ousrIdx);
				$canApprove = $ousr->can_approve;
				$usrOlct	= $ousr->olct_idx;
				
				$model		= $this->initModel('LocationModel');
				$locations	= $model->find ();
				
				$model		= $this->initModel('AssetMoveOutModel');
				if (!$canApprove) {
					$allCount = count ($model->where ('ousr_applicant', $ousrIdx)->find ());
					$pendingCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('olct_from', $usrOlct)->where ('status', 1)->find ());
					$declinedCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('olct_from', $usrOlct)->where ('status', 0)->find ());
					$approvedCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('olct_from', $usrOlct)->where ('status >=', 2)->find ());
					$doneCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('olct_from', $usrOlct)->where ('status', '4')->find ());
					$mvosHead = [
						'{8}', '{9}', '{10}', '{11}', '{12}', '{13}'
					];
					$mvos = $model->select ('omvo.docnum, omvo.docdate, omvo.approval_date, omvo.status')->where ('olct_from', $usrOlct)->find ();
				} else {
					$mvosHead = [
						'{8}', '{9}', '{10}', '{14}', '{11}', '{12}', '{13}'
					];
					if ($usrOlct > 0) { // if approvers is specific to location
						$pendingCount = count ($model->where ('olct_from', $usrOlct)->where ('status', 1)->find ());
						$declinedCount = count ($model->where ('olct_from', $usrOlct)->where ('status', 0)->find ());
						$approvedCount = count ($model->where ('olct_from', $usrOlct)->where ('status >=', 2)->find ());
						$doneCount = count ($model->where ('olct_from', $usrOlct)->where ('status', 4)->find ());
						$mvos = $model->select ('omvo.docnum, omvo.docdate, ousr.username, omvo.approval_date, omvo.status')
									->join ('ousr', 'omvo.ousr_applicant=ousr.idx')->where ('olct_from', $usrOlct)->find ();
					} else {
						$pendingCount = count ($model->where ('status', 1)->find ());
						$declinedCount = count ($model->where ('status', 0)->find ());
						$approvedCount = count ($model->where ('status >=', 2)->find ());
						$doneCount = count ($model->where ('status', '4')->find ());
						$mvos = $model->select ('omvo.docnum, omvo.docdate, ousr.username, omvo.approval_date, omvo.status')
									->join ('ousr', 'omvo.ousr_applicant=ousr.idx')->find ();
					}
				}
				
				$allCount	= count ($mvos);
				
				$returnData = [
					'mvosSumm'	=> [
						[
							'id' => 'moveout-count',
							'title' => '{4}',
							'content' => $allCount,
							'style' => 'text-white bg-danger'
						],
						[
							'id' => 'moveout-active',
							'title' => '{5}',
							'content' => $pendingCount,
							'style' => 'text-dark bg-warning'
						],
						[
							'id' => 'moveout-finished',
							'title' => '{6}',
							'content' => $declinedCount . ' / ' . $approvedCount . ' / ' . $doneCount,
							'style' => 'text-white bg-success'
						]
					],
					'locations'	=> $locations,
					'mvosList'	=> $mvos,
					'mvosHead'	=> $mvosHead,
					'docStats'	=> $this->docstats
				];
				
				$requestResponse['status'] = 200;
				break;
			case 'moveout-document':
				$model = $this->initModel('ApplicationSettingsModel');
				$result = $model->find ('numbering');
				$numberingFormat	= $result === NULL ? Document::DEFNUMBERFORMAT : $result->tag_value;
				$result = $model->find ('numbering-periode');
				$numberingPeriode	= $result === NULL ? Document::PERIODEMONTHLY : $result->tag_value;
				
				$documentLib = new Document ($numberingFormat, $numberingPeriode);
				
				$dataTransmit = $this->getDataTransmit();
				$dataParams = $dataTransmit['data-formmoveout'];
				$ousrIdx	= $dataTransmit['data-loggedousr'];
				
				$model = $this->initModel ('AssetMoveOutModel');
				$lastRow = $model->orderBy ('idx', 'DESC')->find ();
				$lastRowDocNum = count ($lastRow) > 0 ? $lastRow[0]->docnum : NULL;
				$generatedDocnum	= $documentLib->generateDocnum(AssetMoveOutModel::DOCCODE, $lastRowDocNum);
				$docParam = [
					'docnum'		=> $generatedDocnum,
					'docdate'		=> date ('Y-m-d H:i:s'),
					'approved_by'	=> 0,
					'approval_date'	=> NULL,
					'sent_by'		=> 0,
					'sent_date'		=> NULL,
					'received_by'	=> 0,
					'received_date'	=> NULL
				];
				
				$docDetails = [];
				
				foreach ($dataParams as $param) {
					$paramName = $param['name'];
					switch ($paramName) {
						default:
							$strpos = strpos($paramName, 'itemmove-qty-');
							if (!is_bool($strpos)) {
								$itemId = str_replace('itemmove-qty-', '', $paramName);
								$docDetail = [
									'omvo_idx'		=> 0,
									'oita_idx'		=> intval ($itemId),
									'olct_idx'		=> 0,
									'osbl_idx'		=> 0,
									'qty'			=> intval ($param['value']),
									'created_by'	=> $ousrIdx,
									'updated_by'	=> $ousrIdx,
									'updated_date'	=> date ('Y-m-d H:i:s')
								];
								array_push($docDetails, $docDetail);
							}
							break;
						case 'applicant-useridx':
							$docParam['ousr_applicant'] = $param['value'];
							$docParam['created_by'] = $ousrIdx;
							$docParam['updated_by'] = $ousrIdx;
							break;
						case 'moveout-fromlocation-hidden':
							$docParam['olct_from'] = $param['value'];
							break;
						case 'moveout-tolocation':
							$docParam['olct_to'] = $param['value'];
							break;
						case 'moveout-remarks':
							$docParam['remarks'] = $param['value'];
							break;
					}
				}
				
				$model->insert ($docParam);
				$omvoId = $model->insertID ();

				$model = $this->initModel('AssetItemModel');

				for ($id = 0; $id < count ($docDetails); $id++) {
					$oita	= $model->find ($docDetails[$id]['oita_idx']);
					$docDetails[$id]['omvo_idx'] = $omvoId;
					$docDetails[$id]['olct_idx'] = $docParam['olct_from'];
					$docDetails[$id]['osbl_idx'] = $oita->osbl_idx;
				}
				
				$model = $this->initModel('AssetMoveOutDetailModel');
				foreach ($docDetails as $insert) $model->insert ($insert);
				
				$good	= ($omvoId > 0);

				$returnData = [
					'good'		=> $good,
					'message'	=> 'New documents successfully created!'
				];
				$requestResponse['status'] = 200;
				break;
			case 'moveout-documentdetailed':
				$dataTransmit = $this->getDataTransmit();
				if (!array_key_exists('data-docnum', $dataTransmit)) $returnData = ['good' => FALSE, 'message' => 'Kesalahan Sistem!'];
				else {
					$ousrIdx = $dataTransmit['data-loggedousr'];
					$model = $this->initModel('EnduserModel');
					$ousr = $model->join ('ougr', 'ousr.ougr_idx=ougr.idx')->find ($ousrIdx);
					$docnum = $dataTransmit['data-docnum'];
					$model = $this->initModel('AssetMoveOutModel');
					$result = $model->select ('omvo.idx, omvo.docnum, omvo.docdate, omvo.olct_from, omvo.olct_to, ousr.username, omvo.status')
									->join ('ousr', 'omvo.ousr_applicant=ousr.idx')->where ('omvo.docnum', $docnum)->find ()[0];
					$omvo = $result->toArray ();
					$omvoIdx = $omvo['idx'];
					
					$model = $this->initModel('LocationModel');
					$omvo['olct_fromname'] = $model->find ($omvo['olct_from'])->name;
					$omvo['olct_toname'] = $model->find ($omvo['olct_to'])->name;
					
					$model = $this->initModel('AssetMoveOutDetailModel');
					$mvo1s = $model->select ('mvo1.oita_idx, oita.code, oita.name, osbl.idx as `osbl_idx`, osbl.name as `osbl_name`, mvo1.qty')
									->join ('oita', 'mvo1.oita_idx=oita.idx')->join ('osbl', 'mvo1.osbl_idx=osbl.idx')
									->where ('omvo_idx', $omvoIdx)->find ();
					$mvoThs = [
						'#', 'Barcode', 'Deskripsi', 'Asal Sublokasi', 'Qty'
					];
					
					$model = $this->initModel('ApplicationSettingsModel');
					$result = $model->find ('numbering');
					$numberingFormat	= $result === NULL ? Document::DEFNUMBERFORMAT : $result->tag_value;
					$result = $model->find ('numbering-periode');
					$numberingPeriode	= $result === NULL ? Document::PERIODEMONTHLY : $result->tag_value;
					
					$document = new Document ($numberingFormat, $numberingPeriode, $this->docstats);
					$documentStatus = $document->getStatusText($omvo['status']);
					
					$returnData = [
						'good' => TRUE,
						'dataTransmit' => [
							'data-canapprove'	=> $ousr->can_approve,
							'data-cansend'		=> $ousr->can_send,
							'data-moveout'		=> $omvo,
							'data-moveoutheads'	=> $mvoThs,
							'data-moveoutdetail'	=> $mvo1s
						],
						'dataHead' => 'Dokumen No. ' . $docnum,
						'dataDetails' => 'Detil Dokumen',
						'dataLabels' => [
							'docnum' => 'No. Dokumen:',
							'docdate' => 'Tgl. Pembuatan',
							'username' => 'Pembuat',
							'status' => 'Status',
							'olct_fromname' => 'Lokasi Asal',
							'olct_toname' => 'Lokasi Tujuan'
						],
						'docnum' => $docnum,
						'documentStatus' => $documentStatus,
						'btnClose' => 'Tutup',
						'btnSent' => 'Tandai Dikirim',
						'btnApprove' => 'Setujui',
						'btnDecline' => 'Tolak'
					];
				}
				$requestResponse['status'] = 200;
				break;	
			case 'moveout-doaction':
				$dataTransmit = $this->getDataTransmit();
				$docnum  = $dataTransmit['docnum'];
				$action  = $dataTransmit['do'];
				$ousrIdx = $dataTransmit['data-loggedousr'];
				
				$model = $this->initModel('AssetMoveOutModel');
				$omvo = $model->select ('omvo.idx, omvo.docnum, omvo.docdate, omvo.olct_from, omvo.olct_to, omvo.ousr_applicant, ousr.username, usr3.fname')
						->join ('ousr', 'ousr.idx=omvo.ousr_applicant')->join ('usr3', 'usr3.idx=omvo.ousr_applicant')->where ('docnum', $docnum)->find ();
				$omvo_idx = $omvo[0]->idx;
				switch ($action) {
					default:
						break;
					case 'decline':
						if ($omvo === NULL) ;
						else {
							$document	= $omvo[0];
							$olct_from	= $document->olct_from;
							$olct_to	= $document->olct_to;
							$approvalDate	= date ('Y-m-d H:i:s');
							$updateParam = [
								'status'	=> 0,
								'approval_date' => $approvalDate,
								'approved_by'	=> $ousrIdx,
								'updated_by'	=> $ousrIdx,
								'updated_date'	=> $approvalDate
							];
							$model->update ($omvo_idx, $updateParam);
							$good = ($model->affectedRows () > 0);
							
							if (!$good) ;
							else {
								
								$msgType	= 'move-01';
								$msgParams	= [
									'document'	=> [
										'docnum'	=> $document->docnum,
										'docdate'	=> $document->docdate,
										'docapp'	=> ($document->fname == '') ? $document->username : $document->fname,
										'docaction'	=> '',
										'docacttime'	=> $approvalDate
									]
								];
								
								$model		= $this->initModel ('EnduserModel');
								$rejector	= $model->select ('ousr.username, usr3.fname')->join ('usr3', 'usr3.idx=ousr.idx')
											->where ('ousr.idx', $ousrIdx)->find ()[0];
								$msgParams['document']['docaction']	= ($rejector->fname == '') ? $rejector->username : $rejector->fname;
								
								$subject	= sprintf ($emailMsgs->getSubject ($msgType), $document->docnum);
								$template	= view ('emails/osam/' . $msgType, $msgParams);
								
								$emails		= array ();
								$receptors	= $model->join ('usr1', 'usr1.ousr_idx=ousr.idx')->where ('usr1.olct_idx', $olct_to)
											->orWhere ('usr1.olct_idx', $olct_from)->find ();
								
								foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
								
								$receptors	= $model->whereIn ('idx', $administrators)->find ();
								
								foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
								
								$emailTools->emailNotifTools ($emails, $subject, $template, OsamModule::EMAIL_FROM, OsamModule::EMAIL_NAME_FROM);
								$emailTools->emailSend ();
							}
							
							$model		= $this->initModel ('AssetMoveOutRequestModel');
							$omvr		= $model->where ('omvo_refidx', $omvo_idx)->find ();
							if (count ($omvr) > 0) {
								$omvr_idx	= $omvr[0]->idx;
								$updateParams	= [
									'status'	=> 0,
									'updated_by'	=> $ousrIdx,
									'updated_date'	=> $approvalDate
								];
								$model->update ($omvr_idx, $updateParams);
							}
							
							$returnData = [
								'good'		=> $good,
								'status'	=> 200,
								'message'	=> 'Permintaan di tolak'
							];
						}
						break;
					case 'approve':
						if ($omvo === NULL) ;
						else {
							$document	= $omvo[0];
							$olct_from	= $document->olct_from;
							$olct_to	= $document->olct_to;
							$approvalDate	= date ('Y-m-d H:i:s');
							$updateParams = [
								'status'	=> 2,
								'approval_date' => $approvalDate,
								'approved_by'	=> $ousrIdx,
								'updated_by'	=> $ousrIdx,
								'updated_date'	=> $approvalDate
							];
							
							$model->update ($omvo_idx, $updateParams);
							$good = ($model->affectedRows () > 0);
							
							if (!$good) ;
							else {
								$msgType	= 'move-02';
								$msgParams	= [
									'document'	=> [
										'docnum'	=> $document->docnum,
										'docdate'	=> $document->docdate,
										'docapp'	=> ($document->fname == '') ? $document->username : $document->fname,
										'docaction'	=> '',
										'docacttime'	=> $approvalDate
									]
								];
								
								$model		= $this->initModel ('EnduserModel');
								$approval	= $model->select ('ousr.username, usr3.fname')->join ('usr3', 'usr3.idx=ousr.idx')
											->where ('ousr.idx', $ousrIdx)->find ()[0];
								$msgParams['document']['docaction'] = ($approval->fname == '') ? $approval->username : $approval->fname;
								
								$subject	= sprintf ($emailMsgs->getSubject ($msgType), $document->docnum, $msgParams['document']['docaction']);
								$template	= view ('emails/osam/' . $msgType, $msgParams);
								
								$emails		= array ();
								
								$receptors	= $model->join ('usr1', 'usr1.ousr_idx=ousr.idx')->whereIn ('usr1.olct_idx', [$olct_to, $olct_from])->find ();
								//$receptors	= $model->join ('usr1', 'usr1.ousr_idx=ousr.idx')->where ('usr1.olct_idx', $olct_to)
								//			->orWhere ('usr1.olct_idx', $olct_from)->find ();
								
								foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
								
								$receptors	= $model->whereIn ('idx', $administrators)->find ();
								
								foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
								
								$emailTools->emailNotifTools ($emails, $subject, $template, OsamModule::EMAIL_FROM, OsamModule::EMAIL_NAME_FROM);
								$emailTools->emailSend ();
								
								$model		= $this->initModel ('AssetMoveOutRequestModel');
								$omvr		= $model->where ('omvo_refidx', $omvo_idx)->find ();
								if (count ($omvr) > 0) {
									$omvr_idx	= $omvr[0]->idx;
									$updateParams	= [
										'status'	=> 2,
										'updated_by'	=> $ousrIdx,
										'updated_date'	=> $approvalDate
									];
									$model->update ($omvr_idx, $updateParams);
								}
								
								$returnData = [
									'good'		=> $good,
									'status'	=> 200,
									'message'	=> 'Permintaan disetujui!'
								];
							}
						}
						break;
					case 'marksent':
						if ($omvo === NULL) ;
						else {
							$document	= $omvo[0];
							$olct_from	= $document->olct_from;
							$olct_to	= $document->olct_to;
							$sentDate	= date ('Y-m-d H:i:s');
							$updateParams = [
								'status'	=> 3,
								'sent_by'	=> $ousrIdx,
								'sent_date'	=> $sentDate,
								'updated_by'	=> $ousrIdx,
								'updated_date'	=> $sentDate
							];
							$model->update ($omvo_idx, $updateParams);
							$good = ($model->affectedRows () > 0);
							
							
							if (!$good) ;
							else {
								$model		= $this->initModel ('AssetMoveOutDetailModel');
								$mvo1s		= $model->where ('omvo_idx', $omvo_idx)->find ();
								
								$msgType	= 'move-03';
								$msgParams	= [
									'document'	=> [
										'docnum'	=> $document->docnum,
										'docdate'	=> $document->docdate,
										'docapp'	=> ($document->fname == '') ? $document->username : $document->fname,
										'docsender'	=> '',
										'docsenttime'	=> $sentDate
									]
								];
								
								$emails	= array ();
								
								$model	= $this->initModel ('EnduserModel');
								$sender	= $model->select ('ousr.email, ousr.username, usr3.fname')->join ('usr3', 'usr3.idx=ousr.idx')->where ('ousr.idx', $ousrIdx)->find ()[0];
								
								$msgParams['document']['docsender'] = $sender->fname == '' ? $sender->username : $sender->fname;
								array_push ($emails, $sender->email);
								
								$receptors	= $model->join ('usr1', 'usr1.ousr_idx=ousr.idx')->whereIn ('usr1.olct_idx', [$olct_from, $olct_to])->find ();
								
								foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
								
								$receptors	= $model->whereIn ('idx', $administrators)->find ();
								
								foreach ($receptors as $receptor) array_push ($emails, $receptor->email);
								
								$subject	= sprintf ($emailMsgs->getSubject ($msgType), $document->docnum);
								$template	= view ('emails/osam/' . $msgType, $msgParams);

								$emailTools->emailNotifTools ($emails, $subject, $template, OsamModule::EMAIL_FROM, OsamModule::EMAIL_NAME_FROM);
								$emailTools->emailSend ();
								
								$model = $this->initModel('ApplicationSettingsModel');
								$numberingFormat = $model->find ('numbering')->tag_value;
								$numberingPeriode = $model->find ('numbering-periode')->tag_value;
								
								$documentNumbering = new Document ($numberingFormat, $numberingPeriode);
								
								$model = $this->initModel('AssetMoveInModel');
								$lastOmvi = $model->orderBy ('idx', 'DESC')->find ();
								$lastDocnum = (count ($lastOmvi) == 0) ? NULL : $lastOmvi[0]->docnum;
								$docnumGenerated = $documentNumbering->generateDocnum(AssetMoveInModel::DOCCODE, $lastDocnum);
								
								$insertParams	= [
									'docnum'		=> $docnumGenerated,
									'docdate'		=> $sentDate,
									'omvo_refidx'		=> $omvo_idx,
									'omvo_ousridx'		=> $document->ousr_applicant,
									'omvo_olctfrom'		=> $olct_from,
									'omvo_olctto'		=> $olct_to,
									'sent'			=> 1,
									'sent_by'		=> $ousrIdx,
									'sent_date'		=> $sentDate,
									'received_by'		=> 0,
									'received_date'		=> NULL,
									'distributed_by'	=> 0,
									'distributed_date'	=> NULL,
									'created_by'		=> $ousrIdx,
									'updated_by'		=> $ousrIdx,
									'updated_date'		=> $sentDate
								];
								
								$model->insert ($insertParams);
								$omvi_idx = $model->insertID ();
								
								$model = $this->initModel ('AssetItemModel');
								foreach ($mvo1s as $mvo1) {
									$mvo1_oitaidx	= $mvo1->oita_idx;
									$mvo1_oitaqty	= $mvo1->qty;
									
									$currQty	= $model->where ('idx', $mvo1_oitaidx)->find ()[0]->qty;
									$updateParams	= [
										'qty'	=> ($currQty - $mvo1->qty)
									];
									$model->update ($mvo1_oitaidx, $updateParams);
								}
								
								$model		= $this->initModel ('AssetMoveOutRequestModel');
								$omvr		= $model->where ('omvo_refidx', $omvo_idx)->find ();
								
								if (count ($omvr) > 0) {
									$omvr_idx	= $omvr[0]->idx;
									$updateParams	= [
										'status'	=> 3,
										'updated_by'	=> $ousrIdx,
										'updated_date'	=> $sentDate
									];
									$model->update ($omvr_idx, $updateParams);
								}
								
								$returnData = [
									'good'		=> $good,
									'status'	=> 200,
									'message'	=> 'Dokumen telah ditandai sebagai dikirim!'
								];
							}
						}
						break;
				}
				$requestResponse['status'] = 200;
				break;
			case 'procure-documentdetailed':
				$dataTransmit	= $this->getDataTransmit ();
				$returnData	= $dataTransmit;
				$requestResponse['status'] = 200;
				break;
			case 'get-sublocationoflocation':
				$dataTransmit = $this->getDataTransmit();
				$model		= $this->initModel('SublocationModel');
				$osbls		= $model->where ('olct_idx', $dataTransmit['target-location'])->find ();
				if (count ($osbls) == 0) $requestResponse['status']	= 400;
				else {
					$dataSublocations = [];
					foreach ($osbls as $osbl) $dataSublocations[$osbl->idx] = $osbl->name;
					
					$returnData = [
						'good'	=> TRUE,
						'data-sublocations'	=> $dataSublocations
					];
					$requestResponse['status'] = 200;
				}
				break;
			case 'get-sublocationassetlists':
				$dataTransmit	= $this->getDataTransmit ();
				$model		= $this->initModel ('AssetItemModel');
				$oitas		= $model->select ('oita.idx, oita.code, oita.name, oita.qty')->where ('olct_idx', $dataTransmit['data-locationidx'])
							->where ('osbl_idx', $dataTransmit['data-sublocationidx'])->find ();
				if (count ($oitas) == 0)
					$returnData	= [
						'good'	=> FALSE,
						'data'	=> NULL
					];
				else $returnData	= [
					'good'		=> TRUE,
					'data-assets'	=> $oitas
				];
				
				$requestResponse['status']	= 200;
				break;
			case 'assetsdestroy-request':
				$dataTransmit = $this->getDataTransmit();
				$olct_idx = $dataTransmit['location-idx'];
				$ousr_idx = $dataTransmit['data-loggedousr'];
				
				$model = $this->initModel('ApplicationSettingsModel');
				$numberingFormat = $model->find ('numbering')->tag_value;
				$numberingPeriode = $model->find ('numbering-periode')->tag_value;
				$document = new Document ($numberingFormat, $numberingPeriode);
				
				$model = $this->initModel('AssetRemovalModel');
				$orqns = $model->orderBy ('idx', 'DESC')->find ();
				$lastDocnum = (count ($orqns) == 0) ? NULL : $orqns[0]->docnum; 
				
				$generatedDocnum	= $document->generateDocnum(AssetRemovalModel::DOCCODE, $lastDocnum);
				$now			= date ('Y-m-d H:i:s');
				
				$insertParam = [
					'docnum'		=> $generatedDocnum,
					'docdate'		=> $now,
					'ousr_applicant'	=> $ousr_idx,
					'olct_from'		=> $olct_idx,
					'approved_by'		=> 0,
					'approval_date'		=> NULL,
					'removed_by'		=> 0,
					'removal_date'		=> NULL,
					'removal_method'	=> '',
					'status'		=> 1,
					'comments'		=> NULL,
					'created_by'		=> $ousr_idx,
					'updated_by'		=> $ousr_idx,
					'updated_date'		=> $now
				];
				
				$model->insert ($insertParam);
				$oarv_idx = $model->getInsertID ();
				
				if ($oarv_idx == 0) {
					$requestResponse['status']	= 500;
					$requestResponse['message']	= 'Error! Document insertion failed!';
				} else {
					$dataAssets = $dataTransmit['data-assets'];
					$model = $this->initModel('AssetRemovalDetailModel');
					foreach ($dataAssets as $data) {
						$insertParam = [
							'oarv_idx'	=> $oarv_idx,
							'oita_idx'	=> $data['asset-idx'],
							'osbl_idx'	=> $data['subloc-idx'],
							'remarks'	=> $data['remarks'],
							'removal_qty'	=> $data['request-qty'],
							'created_by'	=> $ousr_idx,
							'updated_by'	=> $ousr_idx,
							'updated_date'	=> $now
						];
						$model->insert ($insertParam);
					}
					
					$msgType	= 'destroy-00';
					$msgParams	= [
						'document'	=> [
							'docnum'	=> $generatedDocnum,
							'docdate'	=> $now,
							'docloc'	=> '',
							'docapp'	=> ''
						]
					];
					
					$subject	= sprintf ($emailMsgs->getSubject ($msgType), $generatedDocnum);
					$template	= view ('emails/osam/' . $msgType, $msgParams);
					
					$requestResponse['status']	= 200;
				}
				break;
			case 'userprofile':
				$dataTransmit = $this->getDataTransmit();
				$ousr_idx = $dataTransmit['data-loggedousr'];
				$model		= $this->initModel('EnduserProfileModel');
				$usr3		= $model->find ($ousr_idx);
				if ($usr3 === NULL) {
					$requestResponse['status']	= 500;
					$requestResponse['message']	= 'Error! No profile was found!';
				} else {
					$returnData	= [
						'data-profile'	=> $usr3
					];
					$requestResponse['status'] = 200;
				}
				break;
			case 'profile-update':
				$dataTransmit = $this->getDataTransmit();
				$dataForm = $dataTransmit['data-form'];
				$ousr_idx = $dataTransmit['data-loggedousr'];
				
				$model	= $this->initModel ('EnduserModel');
				$ousrs	= $model->find ($ousr_idx);
				
				$model	= $this->initModel ('EnduserProfileModel');
				$usr3	= $model->find ($ousr_idx);
				
				$updateParam = [
					'fname'			=> $dataForm['first-name'],
					'mname'			=> $dataForm['middle-name'],
					'lname'			=> $dataForm['last-name'],
					'address1'		=> $dataForm['address-a'],
					'address2'		=> $dataForm['address-b'],
					'phone'			=> $dataForm['phone'],
					'image'			=> $dataForm['imageName'],
					'updated_by'	=> $ousr_idx,
					'updated_date'	=> date ('Y-m-d H:i:s')
				];
				
				$result = 0;
				if ($usr3 !== NULL) {
					$model->update ($ousr_idx, $updateParam);
					$result = $model->affectedRows ();
				} else {
					$updateParam['email']		= $ousrs->email;
					$updateParam['created_by']	= $ousr_idx;
					$model->insert ($updateParam);
					$result = $model->getInsertID ();
				}
				
				if ($result === 0) {
					$requestResponse['status'] = 200;
					$requestResponse['message']	= 'Error! Data insertion error!';
				} else {
					$returnData = [
						'good'	=> TRUE
					];
					$requestResponse['status'] = 200;
				}
				break;
			case 'load-assetimages':
				helper ('filesystem');
				$clientCode	= $this->getClientCode ();
				$imageLoadPath	= sprintf (OsamModule::IMAGEWRITEPATH, $clientCode);
				$filenames	= get_filenames	($imageLoadPath);
				$imageList	= array ();
				$index		= 0;
				foreach ($filenames as $filename) {
					$filepath	= $imageLoadPath . '/' . $filename;
					$file		= new \CodeIgniter\Files\File ($filepath);
					$fileMime	= $file->getMimeType ();
					if ($fileMime === 'image/gif' || $fileMime === 'image/jpeg' || $fileMime === 'image/png') {
						$imageList[$index]	= [
							'name'		=> $filename,
							'size'		=> $file->getSize (),
							'mime'		=> $file->getMimeType (),
							'lastc'		=> filectime ($filepath),
							'content'	=> base64_encode (file_get_contents ($filepath))
						];
						$index++;
					}
				}
				
				$model	= $this->initModel ('EnduserModel');
				$uStat	= $model->select ('ougr_idx')->where ('idx', $this->getDataTransmit ()['data-loggedousr'])->find ()[0];
				$returnData	= [
					'data-userstat'		=> $uStat->ougr_idx,
					'data-imagelist'	=> $imageList
				];
				$requestResponse['status']	= 200;
				break;
			case 'images-bulkupload':
				helper ('filesystem');
				$clientCode	= $this->getClientCode ();
				$savePath	= sprintf (OsamModule::IMAGEWRITEPATH, $clientCode);
				
				$dataTransmit	= $this->getDataTransmit ();
				$dataFiles	= $dataTransmit['data-images'];
				$done		= 0;
				
				if (!file_exists ($savePath)) mkdir ($savePath, 0755, TRUE);
				foreach ($dataFiles as $dataFile) {
					$fileSavePath	= $savePath . '/' . $dataFile['name'];
					$fileContents	= base64_decode ($dataFile['content']);
					$writeSuccess	= write_file ($fileSavePath, $fileContents);
					if ($writeSuccess) $done++;
				}
				
				$returnData	= [
					'data-writesuccess'	=> $done
				];
				$requestResponse['status']	= 200;
				break;
			case 'images-removals':
				helper ('filesystem');
				$dataTransmit	= $this->getDataTransmit ();
				$clientCode	= $this->getClientCode ();
				$deleted	= 0;
				foreach ($dataTransmit as $filename) {
					$savePath	= sprintf (OsamModule::IMAGEWRITEPATH, $clientCode);
					$fileSavePath	= $savePath . '/' . $filename;
					if (unlink ($fileSavePath)) $deleted++;
				}
				
				$returnData	= [
					'good'		=> ($deleted > 0),
					'delsuccess'	=> $deleted
				];
				$requestResponse['status']	= 200;
				break;
			case 'docuportable':
				$dataTransmit	= $this->getDataTransmit ();
				if (!array_key_exists ('data-loggedousr', $dataTransmit)) {
					$returnData = [
						'good'	=> FALSE
					];
					$requestResponse['status']	= 500;
					$requestResponse['message']	= 'Unrecognized request format!';
				} else {
					$ousr_idx	= $dataTransmit['data-loggedousr'];
					$ousrmodel	= $this->initModel('EnduserModel');
					$ousr		= $ousrmodel->find ($ousr_idx);
					if ($ousr === NULL) {
						$returnData	=	[
							'good'	=> FALSE
						];
						$requestResponse['status']	= 500;
						$requestResponse['message']	= 'Invalid Parameter';
					} else {
						$docnum				= $dataTransmit['data-documentnumber'];
						$model				= $this->initModel('ApplicationSettingsModel');
						$numberingFormat		= $model->find ('numbering')->tag_value;
						$numberingReset			= $model->find ('numbering-periode')->tag_value;
						$document			= new Document($numberingFormat, $numberingReset);
						$doccode			= $document->getDocumentCode($docnum);
						switch ($doccode) {
							default:
								$returnData	= [
									'good'	=> FALSE
								];
								$requestResponse['status'] = 500;
								break;
							case AssetMoveInModel::DOCCODE:
								break;
							case AssetMoveOutModel::DOCCODE:
								$model	= $this->initModel('AssetMoveOutModel');
								$omvos	= $model->where ('docnum', $docnum)->find ();
								if (count ($omvos) == 0) {
									$returnData	= [
										'good'	=> FALSE
									];
									$requestResponse['status']	= 404;
									$requestResponse['message']	= 'Data Not Found!';
								} else {
									$ousrProfile	= $ousrmodel->select ('usr3.fname')->join ('usr3', 'ousr.idx=usr3.idx')->find ($omvos[0]->ousr_applicant);
									$dateOnly		= \DateTime::createFromFormat('Y-m-d H:i:s', $omvos[0]->docdate)->format('d F Y');
									$moveOutDocument	= [
										'document-number'		=> $omvos[0]->docnum,
										'document-date'			=> $dateOnly,
										'document-from'			=> '',
										'document-to'			=> [],
										'document-applicant'	=> $ousrProfile->fname,
										'document-details'		=> []
									];
									$omvo_idx	= $omvos[0]->idx;
									$olct_from	= $omvos[0]->olct_from;
									$olct_to	= $omvos[0]->olct_to;
									$model	= $this->initModel ('AssetMoveOutDetailModel');
									$mvo1s	= $model->select ('oita.code, oita.name, osbl.name as `osbl_name`, mvo1.qty')->join ('osbl', 'mvo1.osbl_idx=osbl.idx')
												->join ('oita', 'mvo1.oita_idx=oita.idx')->where ('mvo1.omvo_idx', $omvo_idx)->find ();
									
									if (count ($mvo1s) == 0) {
										$returnData	= [
											'good'		=> FALSE
										];
										$requestResponse['status']	= 500;
										$requestResponse['message']	= 'Error! Missing document details';
									} else {
										$moveOutDocument['document-details']	= $mvo1s;
										$model	= $this->initModel ('LocationModel');
										$moveOutDocument['document-from']		= $model->find ($olct_from)->name;
										$moveOutDocument['document-to']			= $model->find ($olct_to);
										
										$returnData	= [
											'good'			=> TRUE,
											'document-type'	=> $doccode,
											'document'		=> $moveOutDocument
										];
										$requestResponse['status']	= 200;
									}
								}
								break; 
						}
					}
				}
				break;
			case 'procure-summaries':
				$dataTransmit	= $this->getDataTransmit ();
				$loggedOusr	= $dataTransmit['data-loggedousr'];
				$locale		= $dataTransmit['data-locale'];
				$model		= $this->initModel ('EnduserModel');
				$ousr		= $model->join ('usr1', 'ousr.idx=usr1.ousr_idx')->where ('ousr.idx', $loggedOusr)->find ();
				$ougr		= $ousr[0]->ougr_idx;
				$olct_idx	= $ousr[0]->olct_idx;
				
				$summaries	= [
					'pending'	=> 0,
					'approved'	=> 0,
					'declined'	=> 0
				];
				$requestList	= [
				];
				
				$model	= $this->initModel ('AssetRequisitionModel');
				
				if ($ougr == 1) {
					$orqn	= $model->select ('orqn.docnum, orqn.docdate, ousr.username, orqn.requisition_type, orqn.status')
							->join ('ousr', 'ousr.idx=orqn.ousr_applicant')->where ('status >=', 1)->find ();
				} else {
					$orqn	= $model->select ('orqn.docnum, orqn.docdate, ousr.username, orqn.requisition_type, orqn.status')
							->join ('ousr', 'ousr.idx=orqn.ousr_applicant')->where ('olct_idx', $olct_idx)->where ('status >=', 1)->find ();
				}
				
				$reqStatus	= new RequestStatus ();
				$reqDocType	= new RequestDocumentType ();
				
				foreach ($orqn as $key => $rqn) {
					$requestList[$key]	= [
						$rqn->docnum,
						$rqn->docdate,
						$rqn->username,
						$reqDocType->getTypeText ($locale, $rqn->requisition_type),
						$reqStatus->getTypeText ($locale, $rqn->status)
					];
					
					switch ($rqn->status) {
						default:
							break;
						case 0: 
							$summaries['declined']++;
							break;
						case 1: 
							$summaries['pending']++;
							break;
						case 2:
							$summaries['approved']++;
							break;
					}
				}
				
				$returnData	= [
					'summaries'	=> $summaries,
					'requestlist'	=> $requestList,
					'styles'	=> [
						'pending'	=> 'text-white bg-warning',
						'approved'	=> 'text-white bg-success',
						'declined'	=> 'text-white bg-danger'
					],
					'titles'	=> [
						'pending'	=> '{4}',
						'approved'	=> '{5}',
						'declined'	=> '{6}'
					]
				];
				
				$requestResponse['status']	= 200;
				break;
			case 'removal-documents':
				$dataTransmit	= $this->getDataTransmit ();
				$ousr_idx = $dataTransmit['data-loggedousr'];
				$model		= $this->initModel ('EnduserLocationModel');
				$ousr		= $model->find ($ousr_idx);
				$olct_idx	= $ousr->olct_idx;
				
				$model		= $this->initModel ('AssetRemovalModel');
				$allCount	= 0;
				$pendingCount	= 0;
				$declinedCount	= 0;
				$approvedCount	= 0;
				$doneCount	= 0;
				
				if ($olct_idx > 0) {
					$pendingCount	= count ($model->where ('status', 1)->where ('olct_from', $olct_idx)->find ());
					$declinedCount	= count ($model->where ('status', 0)->where ('olct_from', $olct_idx)->find ());
					$declinedCount	= count ($model->where ('status >=', 2)->where ('olct_from', $olct_idx)->find ());
					$doneCount	= count ($model->where ('status', 4)->where ('olct_from', $olct_idx)->find ());
					$arvs		= $model->select ('oarv.docnum, oarv.docdate, ousr.username, olct.name as `location_name`, oarv.approval_date, oarv.status')
									->join ('ousr', 'oarv.ousr_applicant=ousr.idx')->join ('olct', 'oarv.olct_from=olct.idx')
									->where ('oarv.olct_from', $olct_idx)->find ();
				} else {
					$pendingCount	= count ($model->where ('status', 1)->find ());
					$declinedCount	= count ($model->where ('status', 0)->find ());
					$approvedCount	= count ($model->where ('status >=', 2)->find ());
					$doneCount	= count ($model->where ('status', 4)->find ());
					$arvs		= $model->select ('oarv.docnum, oarv.docdate, ousr.username, olct.name as `location_name`, oarv.approval_date, oarv.status')
									->join ('ousr', 'oarv.ousr_applicant=ousr.idx')->join ('olct', 'oarv.olct_from=olct.idx')->find ();
				}
				$allCount		= count ($arvs);
				
				$documentsPending	= $model->select ('oarv.idx, oarv.docnum, oarv.docdate, ousr.username, olct.name, usr3.fname, oarv.approved_by, oarv.approval_date, oarv.status')
								->join ('ousr', 'ousr.idx=oarv.ousr_applicant')->join ('usr3', 'usr3.idx=ousr.idx')->join ('olct', 'olct.idx=oarv.olct_from')
								->where ('status', 2)->find ();
				$pending		= [];
				
				$model			= $this->initModel ('AssetRemovalDetailModel');
				
				foreach ($documentsPending as $key => $document) {
					$oarv_idx	= $document->idx;
					
					$arv1s		= $model->select ('arv1.line_idx, oita.code, oita.name, osbl.name as `osbl_name`, arv1.removal_qty')
								->join ('oita', 'oita.idx=arv1.oita_idx')->join ('osbl', 'osbl.idx=arv1.osbl_idx')
								->where ('oarv_idx', $oarv_idx)->find ();
					$pendingDetail	= [];
					
					foreach ($arv1s as $lineKey => $arv1) 
						$pendingDetail[$lineKey] = [
							'arv1_idx'	=> $arv1->line_idx,
							'barcode'	=> $arv1->code,
							'dscript'	=> $arv1->name,
							'sublocation'	=> $arv1->osbl_name,
							'remove_qty'	=> $arv1->removal_qty
						];
					
					$aDocument = [
						'docidx'	=> $document->idx,
						'docnum'	=> $document->docnum,
						'docdate'	=> $document->docdate,
						'username'	=> $document->username,
						'surname'	=> $document->fname,
						'location'	=> $document->name,
						'approved_by'	=> $document->approved_by,
						'approval_date'	=> $document->approval_date,
						'status'	=> $document->status,
						'details'	=> $pendingDetail
					];
					
					$pending[$key] = $aDocument;
				}
				
				$returnData = [
					'arvSummaries'	=> [
						[
							'id'		=> 'removal-count',
							'title'		=> '{4}',
							'content'	=> $allCount,
							'style'		=> 'text-light bg-danger'
						],
						[
							'id'		=> 'removal-active',
							'title'		=> '{5}',
							'content'	=> $pendingCount,
							'style'		=> 'text-dark bg-warning'
						],
						[
							'id'		=> 'removal-action',
							'title'		=> '{6}',
							'content'	=> $declinedCount . ' / ' . $approvedCount . ' / ' . $doneCount,
							'style'		=> 'text-light bg-success'
						]
					],
					'removaldocs'	=> $arvs,
					'pendingDocs'	=> $pending,
					'docStats'	=> $this->docstats
				];
				$requestResponse['status'] = 200;
				break;
			case 'removal-documentdetailed':
				$dataTransmit	= $this->getDataTransmit ();
				$ousr_idx	= $dataTransmit['data-loggedousr'];
				$model = $this->initModel ('EnduserModel');
				$ousr  = $model->select ('ougr.can_remove')->join ('ougr', 'ougr.idx=ousr.ougr_idx')->where ('ousr.idx', $ousr_idx)->find ();
				$can_remove = ($ousr[0]->can_approve == 1);
				
				$good = FALSE;
				
				$model = $this->initModel ('AssetRemovalDetailModel');
				$oarvs = $model->select ('oarv.idx, oarv.status, oarv.docnum, oita.code as `barcode`, oita.name as `dscript`, osbl.name as `osbl_name`, arv1.remarks, arv1.removal_qty')
						->join ('oarv', 'oarv.idx=arv1.oarv_idx')->join ('oita', 'oita.idx=arv1.oita_idx')->join ('osbl', 'osbl.idx=arv1.osbl_idx')
						->where ('oarv.docnum', $dataTransmit['data-docnum'])->find ();
						
				if (count ($oarvs) == 0) 
					$returnData = [
						'good'		=> $good,
						'message'	=> [
							'id'		=> 'Error! Dokumen tidak ditemukan!',
							'en'		=> 'Error! Document not found!'
						]
					];
				else {
					$good = TRUE;
					$returnData	= [
						'good'		=> $good,
						'transmit'	=> [
							'status'	=> intval ($oarvs[0]->status),
							'userstat'	=> $can_remove,
							'details'	=> $oarvs
						],
						'labels'	=> [
							'buttons'	=> [
								'approve'	=> [
									'id'		=> 'Setujui',
									'en'		=> 'Approve'
								],
								'decline'	=> [
									'id'		=> 'Tolak',
									'en'		=> 'Decline'
								]
							]
						]
					];
				}
				$requestResponse['status']	= 200;
				break;
			case 'destroy-doaction':
				$dataTransmit	= $this->getDataTransmit ();
				$model		= $this->initModel ('AssetRemovalModel');
				$ousr_idx	= $dataTransmit['data-loggedousr'];
				$oarv		= $model->where ('docnum', $dataTransmit['data-docnum'])->find ();
				
				if (count ($oarv) == 0) 
					$returnData	= [
						'good'		=> FALSE,
						'message'	=> [
							'id'		=> 'Error! Dokumen tidak ditemukan!',
							'en'		=> 'Error! Document not found!'
						]
					];
				else {
					$oarv_idx	= $oarv[0]->idx;
					$appoved	= $dataTransmit['data-doaction'] === 'approve';
					if (!$appoved) 
						$updateParams	= [
							'status'	=> 0,
							'updated_by'	=> $ousr_idx,
							'updated_date'	=> date ('Y-m-d H:i:s')
						];
					else 
						$updateParams	= [
							'status'	=> 2,
							'approved_by'	=> $ousr_idx,
							'approval_date'	=> date ('Y-m-d H:i:s'),
							'updated_by'	=> $ousr_idx,
							'updated_date'	=> date ('Y-m-d H:i:s')
						];
						
					$model->update ($oarv_idx, $updateParams);
					$returnData	= [
						'good'		=> TRUE,
						'message'	=> [
							'id'		=> 'Dokumen berhasil di perbarui!',
							'en'		=> 'Document has been updated!'
						]
					];
				}
				$requestResponse['status']	= 200;
				break;
			case 'removal-doaction':
				$dataTransmit	= $this->getDataTransmit ();
				$oarv_idx	= $dataTransmit['data-docidx'];
				$ousr_idx	= $dataTransmit['data-loggedousr'];
				$details	= $dataTransmit['data-detailupdate'];
				
				foreach ($details as $key => $detail) {
					$arv_lineid	= $detail['data-lineid'];
					$model		= $this->initModel ('AssetRemovalDetailModel');
					$arv1		= $model->where ('line_idx', $arv_lineid)->find ();
					$oita_idx	= $arv1[0]->oita_idx;
					
					$updateParams	= [
						'removal_method'	=> $detail['data-method'],
						'updated_by'		=> $ousr_idx,
						'updated_date'		=> date ('Y-m-d H:i:s')
					];
					$model->update (['line_idx' => $arv_lineid], $updateParams);
					
					$updateQty	= $detail['data-qty'];
					$model		= $this->initModel ('AssetItemModel');
					$oita		= $model->where ('idx', $oita_idx)->find ();
					
					$updateParams	= [
						'qty'	=> ($oita[0]->qty - $updateQty)
					];
					$model->update ($oita_idx, $updateParams);
				}
				
				$model	= $this->initModel ('AssetRemovalModel');
				$updateParams = [
					'status'	=> 6,
					'removed_by'	=> $ousr_idx,
					'removal_date'	=> date ('Y-m-d H:i:s'),
					'updated_by'	=> $ousr_idx,
					'updated_date'	=> date ('Y-m-d H:i:s')
				];
				$model->update ($oarv_idx, $updateParams);
				
				$returnData	= [
					'good'		=> TRUE,
					'message'	=> [
						'id'		=> 'Dokumen berhasil diperbarui!',
						'en'		=> 'Document has been updated!'
					]
				];
				$requestResponse['status']	= 200;
				break;
		}
		
		if ($requestResponse['status'] == 200) $requestResponse['message']	= base64_encode(serialize($returnData));
		
		return $requestResponse;
	}
	
	public function serverRequest ($trigger): array {
		/**
		 * 
		 * @var \App\Models\BaseModel $model
		 */
		$model = NULL;
		$response = [];
		
		$emailTools = EmailTools::init ();
		$emailMsgs = EmailMessage::init ();
		$targetNotifs = [
			'it.jodamo@gmail.com'
		];
		$locale = 'id';
		
		switch ($trigger) {
			default:
				$response = [
					'status'	=> 400,
					'message'	=> 'Bad Request!'
				];
				break;
			case 'user-verification':
				$dataTransmit = $this->getDataTransmit();
				$dataUsername = $dataTransmit['form-data']['data-username'];
				$model		= $this->initModel('EnduserModel');
				$ousr		= $model->where ('username', $dataUsername)->orWhere ('email', $dataUsername)->find ();
				
				if ($ousr == NULL) $response = ['status' => 404, 'message' => 'User Not Found!'];
				else {
					$user = $ousr[0];
					array_push ($targetNotifs, $user->email);
					$dataPassword = $dataTransmit['form-data']['data-password'];
					
					$model		= $this->initModel ('SystemLogModel');
					$insertParams	= [
						'ip_address'	=> $dataTransmit['ip-address'],
						'ousr_idx'	=> $user->idx,
						'activity'	=> ''
					];
					if (!password_verify($dataPassword, $user->password)) {
						$emailType = 'ousr-00';
						$parameter = [
							'eventDate'	=> date ('Y-m-d H:i:s'),
							'eventIP'	=> $dataTransmit['ip-address'],
							'eventName'	=> $dataUsername
						]; 
						$insertParams['activity']	= 'login-failed';
						$response = ['status' => 401, 'message' => 'Password Not Match!'];
					} else {
						$emailType = 'ousr-01';
						$parameter = [
							'eventDate'	=> date ('Y-m-d H:i:s'),
							'eventIP'	=> $dataTransmit['ip-address'],
							'eventName'	=> $dataUsername
						];
						$returnData = [
							'data-transmit'	=> [
								'id'	=> $user->idx,
								'user'	=> $user->username
							]
						];
						$insertParams['activity']	= 'login-success';
						$response = ['status' => 200, 'message' => $returnData];
					}
					$model->insert ($insertParams);
					$subject = sprintf($emailMsgs->getSubject ($emailType), date ('Y-m-d H:i:s'));
					$template = view ('emails/osam/' . $emailType, $parameter);
					$emailTools->emailNotifTools ($targetNotifs, $subject, $template, OsamModule::EMAIL_FROM, OsamModule::EMAIL_NAME_FROM);
					$emailTools->emailSend ();
				}
				break;
			case 'admin-check':
				$model		= $this->initModel('EnduserModel');
				$ousrs		= $model->find ();
				$response	= [
					'status'	=> 200,
					'message'	=> (count ($ousrs) > 0)
				];
				$response	= [
					'status'	=> 200,
					'message'	=> TRUE
				];
				break;
			case 'power-overwhelming':
				$json = $this->getDataTransmit();
				$insertParams = [
					'ougr_idx'	=> 1,
					'username'	=> $json['username'],
					'email'		=> $json['email'],
					'password'	=> password_hash($json['entry-password'], PASSWORD_BCRYPT),
					'created_by'	=> 0,
					'updated_by'	=> 0,
					'updated_date'	=> date ('Y-m-d H:i:s')
				];
				$model = $this->initModel('EnduserModel');
				$model->insert ($insertParams);
				$ousrid = $model->getInsertID();
				
				$update = [
					'created_by'	=> $ousrid,
					'updated_by'	=> $ousrid,
					'updated_date'	=> date ('Y-m-d H:i:s')
				];
				$model->update ($ousrid, $update);
				
				$insertParams = [
					'ousr_idx'	=> $ousrid,
					'olct_idx'	=> 0,
					'status'	=> 'assigned',
					'created_by'	=> $ousrid,
					'updated_by'	=> $ousrid,
					'updated_date'	=> date ('Y-m-d H:i:s')
				];
				$model = $this->initModel ('EnduserLocationModel');
				$model->insert ($insertParams);
				
				$insertParams = [
					'idx'		=> $ousrid,
					'fname'		=> $json['first-name'],
					'mname'		=> $json['middle-name'],
					'lname'		=> $json['last-name'],
					'address1'	=> $json['address-primary'],
					'address2'	=> $json['address-secondary'],
					'phone'		=> $json['phone-num'],
					'email'		=> $json['email'],
					'created_by'	=> $ousrid,
					'updated_by'	=> $ousrid,
					'updated_date'	=> date ('Y-m-d H:i:s')
				];
				$model = $this->initModel ('EnduserProfileModel');
				$model->insert ($insertParams);
				
				if ($ousrid == 0) $response = ['status' => 500, 'message' => 'Error! Administrator user creation error!'];
				else $response = ['status' => 200, 'message' => 'User Successfully Created!'];
				break;
		}
		return $response;
	}
}
