<?php
namespace App\Libraries\APIModules;

use App\Models\Osam\AssetMoveOutModel;
use App\Libraries\Document;
use App\Models\Osam\AssetMoveInModel;
use App\Models\Osam\AssetMoveOutRequestModel;
use App\Models\Osam\AssetRequisitionModel;

class OsamModule extends Modules {
	
	private const IMAGEWRITEPATH = 'osam/images';
	
	protected $moduleName = 'Osam';
	
	private $attrtypes = [
		'text'				=> 'Teks',
		'date'				=> 'Tanggal',
		'list'				=> 'Daftar',
		'prepopulated-list'	=> 'Daftar Berisi'
	];
	
	public function executeRequest($trigger): array {
		$requestResponse;
		
		/**
		 * 
		 * @var \App\Models\BaseModel $model;
		 */
		$model;
		$returnData = [];
		switch ($trigger) {
			default:
				$requestResponse = [
					'status'	=> 400,
					'message'	=> 'Bad Directives!'
				];
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
				$returnData = [
					'locations'	=> $model->find (),
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
			case 'assets-main-list':
				$model = $this->initModel('AssetItemModel');
				$assets = $model->select ('code, name, SUM(qty) AS `total`')->groupBy ('code')
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
								->where ('code', $assetCode)->groupby ('code')->find ()[0];
				$oita_idx = $assets->idx;
				$detail = [
					'Kode'				=> $assets->code,
					'Nama'				=> $assets->name,
					'Kategori'			=> $assets->ci_name,
					'Deskripsi'			=> $assets->ci_dscript,
					'Waktu Guna (jam)'	=> $assets->loan_time,
					'Total Aset'		=> $assets->totalqty
				];
				
				$locations = $model->select ('olct_idx, olct.code, olct.name')->join ('olct', 'olct.idx=oita.olct_idx', 'LEFT')
								->where ('oita.code', $assetCode)->groupby ('olct_idx')->find ();
				
				$condition = [
					'oita.code' => $assetCode,
					'qty >' => 0
				];
				$sbl1 = $model->select ('olct.code, osbl.name, oita.qty')->join ('olct', 'oita.olct_idx=olct.idx')->join ('osbl', 'oita.osbl_idx=osbl.idx')
							->where ($condition)->orderby ('olct.code')->find ();
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
				
				$returnData = [
					'details'		=> $detail,
					'attrdetail'	=> $attrdetail,
					'locations'		=> $locations,
					'sublocations'	=> $sublocations
				];
				
				$requestResponse['status'] = 200;
				break;
			case 'get-assetlist':
				$dataTransmit = $this->getDataTransmit ();
				if ($dataTransmit['output-type'] === 'perlocation') {
					$model = $this->initModel('AssetItemModel');
					$items = [];
					$items = $model->select ('oita.idx, oita.code, osbl.name as `sublocname`, oita.name, oita.qty')->join ('osbl', 'osbl.idx=oita.osbl_idx')
									->where ('oita.qty >=', '1')->where ('oita.olct_idx', $dataTransmit['from-location'])->like ('oita.code', $dataTransmit['barcode-search'])
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
						'good'			=> true,
						'sublocs'		=> $sublocs,
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
				$model = $this->initModel('LocationModel');
				$returnData['location'] = $model->find ($dataTransmit['olctid']);
				$returnData['locationheader'] = $model->getColumnHeader ();
				$model = $this->initModel('SublocationModel');
				$sublocations = $model->where ('olct_idx', $dataTransmit['olctid'])->find ();
				$returnData['sublocations'] = $sublocations;
				$returnData['sblheader'] = $model->getColumnHeader ();
				$model = $this->initModel('AssetItemModel');
				$locationassets = $model->select ('osbl_idx, code, name, ci_name, notes, po_number, qty')->join ('oaci', 'oaci.idx = oita.oaci_idx')->where ('olct_idx', $dataTransmit['olctid'])->find ();
				$locationassets_raw = [];
				
				foreach ($locationassets as $locationasset) {
					$attribute = $locationasset->toArray ();
					$osbl_id = $locationasset->osbl_idx;
					foreach ($sublocations as $sublocation) 
						if ($sublocation->idx == $osbl_id) {
							$attribute['sublocation'] = $sublocation->name;
							break;
						}
					
					$assetEntity = new \CodeIgniter\Entity ();
					$assetEntity->fill ($attribute);
					array_push($locationassets_raw, $assetEntity);
				}
				
				$returnData['locationassets'] = $locationassets_raw;
				$returnData['assetheader'] = $model->getColumnHeader ('locationassets');
				
				$requestResponse['status'] = 200;
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
					$model = $this->initModel('EnduserModel');
					$userid = $dataTransmit['data-loggedousr'];
					$dataParam = $dataTransmit['param'];
					if ($userid > 0) {
						$find = $model->find ($userid);
						if ($find == NULL) {
							
						} else {
							$updateParam = [
								'ougr_idx'	=> $dataParam['update-usergroup'],
								'email'		=> $dataParam['update-email']
							];
							
							if (array_key_exists('update-password', $dataParam)) 
								$updateParam['password'] = password_hash($dataParam['update-password'], PASSWORD_BCRYPT);
							
							$model->update ($userid, $updateParam);
							
							$model = $this->initModel('EnduserLocationModel');
							$updateParam = [
								'olct_idx'	=> $dataParam['update-accesslocation'],
								'status'	=> 'assigned'
							];
							$model->update ($userid, $updateParam);

							$requestResponse['status']	= 200;
							$requestResponse['message']	= '';
							$returnData = [
								'returnCode' => 200
							];
						}
					} else {
						$find = $model->where ('username', $dataParam['new-username'])->where ('email', $dataParam['new-email'])->findAll ();
						if (count ($find) > 0) {
							$requestResponse['status'] = 400;
							$requestResponse['message'] = 'Nama User sudah ada dalam basis data!';
						} else {
							$insertParam = [
								'ougr_idx'	=> $dataParam['new-usergroup'],
								'username'	=> $dataParam['new-username'],
								'email'		=> $dataParam['new-email'],
								'password'	=> password_hash($dataParam['new-password'], PASSWORD_BCRYPT)
							];
							$model->insert ($insertParam);
							$usrid = $model->insertID ();
							
							$insertParam = [
								'ousr_idx'	=> $usrid,
								'olct_idx'	=> $dataParam['new-accesslocation']
							];
							$model = $this->initModel('EnduserLocationModel');
							$model->insert ($insertParam);
							$usrlocid = $model->insertID ();
							
							if ($usrid > 0 && $usrlocid > 0) {
								$requestResponse['status'] = 200;
								$returnData = [
									'returnCode' => 200
								];
							} else {
								$requestResponse['status']	= 500;
								$requestResponse['message'] = 'Error! Something happened when system trying to save your data!';
							}
						}
					}
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
				foreach ($olcts as $olct) $locations[$olct->idx] = $olct->name;
				
				$model			= $this->initModel('AssetItemModel');
				$oitas			= $model->select ('code, name')->groupBy ('code')->find ();
				$assets			= [];
				foreach ($oitas as $oita) $assets[$oita->code] = $oita->name;
				
				$requestDocuments = [];
				$model			= $this->initModel('AssetMoveOutRequestModel');
				if ($ousrOlct > 0) {
					
				}
				
				$returnData = [
					'summaries'		=> [
						[
							'id'		=> 'request-total',
							'title'		=> '{5}',
							'content'	=> 0,
							'style'		=> 'text-white bg-primary'
						],
						[
							'id'		=> 'request-new',
							'title'		=> '{6}',
							'content'	=> 0,
							'style'		=> 'text-white bg-info'
						],
						[
							'id'		=> 'request-move`',
							'title'		=> '{7}',
							'content'	=> 0,
							'style'		=> 'text-white bg-success'
						],
						[
							'id'		=> 'request-destroy',
							'title'		=> '{8}',
							'content'	=> 0,
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
					'docStats'	=> [
						0	=> 'Ditolak',
						1	=> 'Menunggu Persetujuan',
						2	=> 'Disetujui',
						3	=> 'Dikirim',
						4	=> 'Diterima',
						5	=> 'Didistribusikan'
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
				$ousrIdx			= $dataTransmit['data-loggedousr'];
				$formData			= $dataTransmit['data-formnewasset'];
				$model				= $this->initModel('ApplicationSettingsModel');
				$numberingFormat	= $model->find ('numbering')->tag_value;
				$numberingReset		= $model->find ('numbering-periode')->tag_value;
				$document			= new Document ($numberingFormat, $numberingReset);
				$model				= $this->initModel('AssetRequisitionModel');
				$orqns				= $model->orderBy ('idx', 'DESC')->find ();
				$lastDocnum			= count ($orqns) > 0 ? $orqns[0]->docnum : NULL;
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
					'updated_by'		=> $ousrIdx
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
						'orqn_idx'		=> $insertID,
						'name'			=> $formData['new-name'],
						'dscript'		=> $formData['new-description'],
						'est_value'		=> $formData['new-valueestimation'],
						'qty'			=> $formData['new-requestqty'],
						'imgs'			=> $formData['new-imagenames'],
						'created_by'	=> $ousrIdx,
						'updated_by'	=> $ousrIdx
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
				$ousrIdx			= $dataTransmit['data-loggedousr'];
				$formData			= $dataTransmit['data-formrequest'];
				$model				= $this->initModel('ApplicationSettingsModel');
				$numberingFormat	= $model->find ('numbering')->tag_value;
				$numberingReset		= $model->find ('numbering-periode')->tag_value;
				$document			= new Document ($numberingFormat, $numberingReset);
				
				$model				= $this->initModel('AssetRequisitionModel');
				$orqns				= $model->orderBy ('idx', 'DESC')->find ();
				$lastDocnum			= count ($orqns) > 0 ? $orqns[0]->docnum : NULL;
				$insertParam		= [
					'docnum'			=> $document->generateDocnum(AssetRequisitionModel::DOCCODE, $lastDocnum),
					'docdate'			=> date ('Y-m-d H:i:s'),
					'requisition_type'	=> AssetRequisitionModel::REQTYPEEXT, 
					'olct_idx'			=> 0,
					'ousr_applicant'	=> $ousrIdx,
					'approved_by'		=> 0,
					'approval_date'		=> NULL,
					'status'			=> 1,
					'comments'			=> '',	
					'created_by'		=> $ousrIdx,
					'updated_by'		=> $ousrIdx
				];
				
				$rqn1s	=	[];
				foreach ($formData as $data) {
					switch ($data['name']) {
						default:
							$name = $data['name'];
							if (strpos($name, 'sample-code-') !== FALSE) {
								$rowId = str_replace('sample-code-', '', $name);
								$rqn1 = [
									'orqn_idx'		=> 0,
									'code'			=> $data['value'],
									'qty'			=> 0,
									'created_by'	=> $ousrIdx,
									'updated_by'	=> $ousrIdx
								];
								$rqn1s[$rowId] = $rqn1;
							}
							
							if (strpos($name, 'input-reqextqty-') !== FALSE) {
								$rowId = str_replace ('input-reqextqty-', '', $name);
								$rqn1s[$rowId]['qty'] = $data['value'];
							}
							break;
						case 'requisition-location':
							$insertParam['olct_idx'] = $data['value'];
							break;
					}
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
				$ousrIdx			= $dataTransmit['data-loggedousr'];
				$model				= $this->initModel('ApplicationSettingsModel');
				$numberingFormat	= $model->find ('numbering')->tag_value;
				$numberingReset		= $model->find ('numbering-periode')->tag_value;
				$document			= new Document ($numberingFormat, $numberingReset);
				
				$model				= $this->initModel('AssetMoveOutModel');
				$omvos				= $model->orderBy ('idx', 'DESC')->find ();
				$lastDocnum			= count ($omvos) > 0 ? $omvos[0]->docnum : NULL;
				$insertParam		= [
					'docnum'			=> $document->generateDocnum(AssetMoveOutModel::DOCCODE, $lastDocnum),
					'docdate'			=> date ('Y-m-d H:i:s'),
					'olct_from'			=> 0,
					'olct_to'			=> 0,
					'ousr_applicant'	=> $ousrIdx,
					'approved_by'		=> 0,
					'approval_date'		=> NULL,
					'sent_by'			=> 0,
					'sent_date'			=> NULL,
					'received_by'		=> 0,
					'received_date'		=> NULL,
					'status'			=> 1,
					'status_comment'	=> '',
					'created_by'		=> $ousrIdx,
					'updated_by'		=> $ousrIdx
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
									'omvo_idx'		=> 0,
									'oita_idx'		=> intval($itemId),
									'olct_idx'		=> 0,
									'osbl_idx'		=> 0,
									'qty'			=> intval ($formdata['value']),
									'created_by'	=> $ousrIdx,
									'updated_by'	=> $ousrIdx
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
					$mvo1Param[$id]['omvo_idx']		= $insertId;
					$mvo1Param[$id]['olct_idx']		= $insertParam['olct_from'];
					$mvo1Param[$id]['osbl_idx']		= $oita->osbl_idx;
				}
				
				$model		= $this->initModel('AssetMoveOutDetailModel');
				foreach ($mvo1Param as $param) $model->insert ($param);
				
				$model		= $this->initModel('AssetMoveOutRequestModel');
				$omvrs		= $model->orderBy ('idx', 'DESC')->find ();
				$lastOmvrDocNum = count ($omvrs) > 0 ? $omvrs[0]->docnum : NULL;
				
				$mvrInsertParam	= [
					'docnum'		=> $document->generateDocnum(AssetMoveOutRequestModel::DOCCODE, $lastOmvrDocNum),
					'docdate'		=> $insertParam['docdate'],
					'omvo_refidx'	=> $insertId,
					'olct_from'		=> $insertParam['olct_from'],
					'olct_to'		=> $insertParam['olct_to'],
					'status'		=> $insertParam['status'],
					'created_by'	=> $insertParam['created_by'],
					'updated_by'	=> $insertParam['updated_by']
				];
				
				$model->insert ($mvrInsertParam);
				$good = ($model->getInsertID () > 0);
				
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
					'sublocations'	=> $sublocations,
					'ousrlocation'	=> $ousrOlct,
					'mvisList'		=> $mvis,
					'mvisDetails'	=> $mvisDetails,
					'mvisRcvs'		=> [],
					'docStats'		=> [
						0	=> 'Ditolak',
						1	=> 'Menunggu Persetujuan',
						2	=> 'Disetujui',
						3	=> 'Dikirim',
						4	=> 'Diterima',
						5	=> 'Didistribusikan'
					]
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
					$omvi		= $model->select ('omvi.idx, omvi.docnum, omvi.sent, omvi.docdate, omvi.received_by, omvi.received_date, omvi.omvo_refidx, omvo.docnum as `ref_docnum`, ' .
									'omvo.docdate as `ref_docdate`, ousr.username, omvi.omvo_olctfrom, omvi.omvo_olctto, omvi.sent_by, omvi.sent_date')
									->join ('omvo', 'omvi.omvo_refidx=omvo.idx')->join ('ousr', 'omvi.omvo_ousridx=ousr.idx')
									->where ('omvi.docnum', $docnum)->find ();
					$document	= $omvi[0];
					$omviIdx	= $document->idx;
					$refDocIdx	= $document->omvo_refidx;
					$refOlctTo	= $document->omvo_olctto;
					$moveinSent = ($document->sent == 1);
					$moveinReceived = ($document->received_by > 0);
					
					if ($moveinReceived) $omviThs = ['#', 'Barcode', 'Deskripsi', 'Sublokasi Asal', 'Sublokasi Tujuan', 'Qty'];
					else $omviThs = ['#', 'Barcode', 'Deskripsi', 'Sublokasi Asal', 'Qty'];
					
					$moveinDetailed = [];
					if ($moveinReceived) {
						$model		= $this->initModel('AssetMoveInDetailModel');
						$mvidetails	= $model->select ('mvi1.oita_fromidx, osbl.name as `osbl_name`')->join ('osbl', 'mvi1.osbl_idx=osbl.idx')
											->where ('mvi1.omvi_idx', $omviIdx)->find ();
						foreach ($mvidetails as $detail) $moveinDetailed[$detail->oita_fromidx] = $detail->osbl_name;
					}
					
					$model = $this->initModel('AssetMoveOutDetailModel');
					$mvi1		= $model->select ('mvo1.oita_idx, oita.code, oita.name, osbl.name as `osbl_name`, mvo1.qty')
									->join ('oita', 'mvo1.oita_idx=oita.idx')->join ('osbl', 'mvo1.osbl_idx=osbl.idx')
									->where ('mvo1.omvo_idx', $refDocIdx)->find ();
									
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
					if ($moveinSent) {
						$model	= $this->initModel('SublocationModel');
						$osbls	= $model->select ('idx, name')->where ('olct_idx', $refOlctTo)->find ();
						foreach ($osbls as $osbl) $sublocations[$osbl->idx] = $osbl->name;
					}
					
					$returnData = [
						'good'			=> TRUE,
						'dataTransmit'	=> [
							'data-locationfrom' => $locations[$document->omvo_olctfrom],
							'data-locationto'	=> $locations[$document->omvo_olctto],
							'data-usersent'		=> ($document->sent_by == 0 ? 'Belum Dikirim' : $users[$document->sent_by]),
							'data-userreceived'	=> ($document->sent_by == 0 ? 'Belum Dikirim' : ($document->received_by == 0 ? 'Belum Diterima' : $users[$document->received_by])),
							'data-movein'		=> $document,
							'data-moveinheads'	=> $omviThs,
							'data-moveindetail'	=> $mvi1,
							'data-moveinrcvd'	=> $moveinDetailed
						],
						'docnum'		=> $docnum,
						'isSent'		=> $moveinSent,
						'isReceived'	=> $moveinReceived,
						'btnClose'		=> 'Tutup',
						'btnReceived'	=> 'Diterima',
						'statusText'	=> [
							'ns'		=> 'Belum Dikirim',
							'nr'		=> 'Belum Diterima'
						],
						'titles'		=> [
							'doctitle'		=> 'Dokumen Kedatangan Aset',
							'refdoctitle'	=> 'Referensi Dokumen Perpindahan Aset',
							'refdocdetail'	=> 'Detail Referensi Dokumen Perpindahan Aset'
						],
						'labels'		=> [
							'docnum'		=> 'No. Dokumen:',
							'docdate'		=> 'Tgl. Dokumen:',
							'received_by'	=> 'Penerima',
							'received_date'	=> 'Tgl. Penerimaan',
							'ref_docnum'	=> 'No. Dokumen Referensi:',
							'ref_docdate'	=> 'Tgl. Dokumen Referensi:',
							'username'		=> 'Pembuat:',
							'omvo_olctfrom'	=> 'Lokasi Asal:',
							'omvo_olctto'	=> 'Lokasi Tujuan:',
							'sent_by'		=> 'Telah Dikirim Oleh:',
							'sent_date'		=> 'Tgl. Pengiriman:'
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
					if (count ($omvi) == 0) {
						$returnData = [
							'message'	=> 'Error! Document ' . $docnum . ' was not found!'
						];
						$requestResponse['status'] = 500;
					} else {
						$omviIdx		= $omvi[0]->idx;
						$omvo_refidx	= $omvi[0]->omvo_refidx;
						$updateParam	= [
							'received_by'	=> $ousrIdx,
							'received_date'	=> date ('Y-m-d H:i:s'),
							'updated_by'	=> $ousrIdx
						];
						$model->update ($omviIdx, $updateParam);
						$updated = $model->affectedRows ();
						
						$updateParam	= [
							'received_by'	=> $ousrIdx,
							'received_date'	=> date ('Y-m-d H:i:s'),
							'status'		=> 4,
							'updated_by'	=> $ousrIdx
						];
						
						$model	= $this->initModel('AssetMoveOutModel');
						$model->update ($omvo_refidx, $updateParam);
						$updated += $model->affectedRows ();
						
						if ($updated == 0) {
							$returnData = [
								'good'		=> FALSE,
								'message'	=> 'Error! Data updates failed'
							];
							$requestResponse['status']	= 500;
						} else {
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
				$dataTransmit = $this->getDataTransmit();
				$ousrIdx = $dataTransmit['data-loggedousr'];
				$target_olct = 0;
				$targetDocnum = '';
				$mvi1s = [];
				
				foreach ($dataTransmit as $key => $data) {
					if (is_numeric($key)) {
						$dataName = $data['name'];
						$dataValue = $data['value'];
						if (strpos ($dataName, 'tolocation-id') !== false) $target_olct = $dataValue;
						if (strpos ($dataName, 'item-id-') !== false) {
							$rowId = str_replace('item-id-', '', $dataName);
							$mvi1s[$rowId] = [
								'omvi_idx'		=> 0,
								'oita_fromidx'	=> 0,
								'oita_idx'		=> $dataValue,
								'olct_idx'		=> $target_olct,
								'osbl_idx'		=> 0,
								'qty'			=> 0,
								'created_by'	=> $ousrIdx,
								'updated_by'	=> $ousrIdx
							];
						}
						if (strpos ($dataName, 'to-sublocation-') !== false) {
							$rowId = str_replace('to-sublocation-', '', $dataName);
							$mvi1s[$rowId]['osbl_idx'] = $dataValue;
						}
						if (strpos ($dataName, 'move-qty-') !== false) {
							$rowId = str_replace('move-qty-', '', $dataName);
							$mvi1s[$rowId]['qty'] = $dataValue;
						}
						if ($dataName === 'movein-docnum') $targetDocnum = $dataValue;
					}
				}
				
				$model			= $this->initModel ('AssetMoveInModel');
				$omvi			= $model->where ('docnum', $targetDocnum)->find ()[0];
				$omviIdx		= $omvi->idx;
				$fromolct		= $omvi->omvo_olctfrom;
				$omvo_refidx	= $omvi->omvo_refidx;
				for ($id = 0; $id < count ($mvi1s); $id++) {
					$mvi1s[$id]['omvi_idx'] = $omviIdx;
					$mvi1s[$id]['oita_fromidx'] = $fromolct;
				}
				
				$model		= $this->initModel ('AssetMoveInDetailModel');
				foreach ($mvi1s as $insertParam) $model->insert ($insertParam);
				$mvi1InsertID = $model->getInsertID ();
				
				$good = true;
				
				if ($mvi1InsertID == 0) $good = false;
				else {
					$model		= $this->initModel ('AssetMoveOutModel');
					$updateStatus	= [
						'status'		=> 5,
						'updated_by'	=> $ousrIdx
					];
					$model->update ($omvo_refidx, $updateStatus);
					
					$model = $this->initModel('AssetItemModel');
					foreach ($mvi1s as $mvi1) {
						$oitaIdx	= $mvi1['oita_idx'];
						$fromOita	= $model->find ($oitaIdx);
						$fromQty	= $fromOita->qty;
						$fromCode	= $fromOita->code;
						$toOlct		= $mvi1['olct_idx'];
						$toOsbl		= $mvi1['osbl_idx'];
						$toQty		= $mvi1['qty'];
						
						$toOitas	= $model->where ('osbl_idx', $toOsbl)->where ('olct_idx', $toOlct)->where ('code', $fromCode)->find ();
						if (count ($toOitas) == 0) {
							$insertNewOITA	= [
								'olct_idx'			=> $toOlct,
								'osbl_idx'			=> $toOsbl,
								'oaci_idx'			=> $fromOita->oaci_idx,
								'oast_idx'			=> $fromOita->oast_idx,
								'code'				=> $fromCode,
								'name'				=> $fromOita->name,
								'notes'				=> $fromOita->notes,
								'po_number'			=> $fromOita->po_number,
								'acquisition_value'	=> $fromOita->acquisition_value,
								'loan_time'			=> $fromOita->loan_time,
								'qty'				=> $toQty
							];
							$model->insert ($insertNewOITA);
							if ($model->getInsertID () == 0) $good = false;
						} else {
							$toOita		= $toOitas[0];
							$toOitaIdx	= $toOita->idx;
							$toOldQty	= $toOita->qty;
							$toNewQty	= $toOldQty + $toQty;
							$updateToOITA	= [
								'qty'	=> $toNewQty
							];
							$model->update ($toOitaIdx, $updateToOITA);
							if ($model->getAffectedRows () == 0) $good = false; 
						}
						
						if ($good) {
							$fromNewQty	= $fromQty - $toQty;
							$updateFromOita = [
								'qty'	=> $fromNewQty
							];
							$model->update ($oitaIdx, $updateFromOita);
						}
					}
				}
				
				$returnData = [
					'good'		=> $good,
					'message'	=> 'data updated!'
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
					$pendingCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('status', 1)->find ());
					$declinedCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('status', 0)->find ());
					$approvedCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('status >=', 2)->find ());
					$doneCount = count ($model->where ('ousr_applicant', $ousrIdx)->where ('status', '4')->find ());
					$mvosHead = [
						'#', 'No. Dokumen', 'Tgl. Pembuatan', 'Disetujui', 'Tgl. Persetujuan', 'Status'
					];
					$mvos = $model->select ('omvo.docnum, omvo.docdate, omvo.approval_date, omvo.status')->where ('olct_from', $usrOlct)->find ();
				} else {
					$mvosHead = [
						'#', 'No. Dokumen', 'Tgl. Pembuatan', 'Pemohon', 'Disetujui', 'Tgl. Persetujuan', 'Status'
					];
					if ($usrOlct > 0) { // if approvers is specific to location
						$allCount = count ($model->where ('olct_from', $usrOlct)->find ());
						$pendingCount = count ($model->where ('olct_from', $usrOlct)->where ('status', 1)->find ());
						$declinedCount = count ($model->where ('olct_from', $usrOlct)->where ('status', 0)->find ());
						$approvedCount = count ($model->where ('olct_from', $usrOlct)->where ('status >', 2)->find ());
						$doneCount = count ($model->where ('olct_from', $usrOlct)->where ('status', 4)->find ());
						$mvos = $model->select ('omvo.docnum, omvo.docdate, ousr.username, omvo.approval_date, omvo.status')
									->join ('ousr', 'omvo.ousr_applicant=ousr.idx')->where ('olct_from', $usrOlct)->find ();
					} else {
						$allCount = count ($model->find ());
						$pendingCount = count ($model->where ('status', 1)->find ());
						$declinedCount = count ($model->where ('status', 0)->find ());
						$approvedCount = count ($model->where ('status >', 2)->find ());
						$doneCount = count ($model->where ('status', '4')->find ());
						$mvos = $model->select ('omvo.docnum, omvo.docdate, ousr.username, omvo.approval_date, omvo.status')
									->join ('ousr', 'omvo.ousr_applicant=ousr.idx')->find ();
					}
				}
				
				$returnData = [
					'mvosSumm'	=> [
						[
							'id' => 'moveout-count',
							'title' => 'Jumlah Dokumen Aset Keluar',
							'content' => $allCount,
							'style' => 'text-white bg-danger'
						],
						[
							'id' => 'moveout-active',
							'title' => 'Jumlah Dokumen Menunggu Persetujuan',
							'content' => $pendingCount,
							'style' => 'text-dark bg-warning'
						],
						[
							'id' => 'moveout-finished',
							'title' => 'Ditolak / Disetujui / Selesai',
							'content' => $declinedCount . ' / ' . $approvedCount . ' / ' . $doneCount,
							'style' => 'text-white bg-success'
						]
					],
					'locations'	=> $locations,
					'mvosList'	=> $mvos,
					'mvosHead'	=> $mvosHead,
					'docStats'	=> [
						0	=> 'Ditolak',
						1	=> 'Menunggu Persetujuan',
						2	=> 'Disetujui',
						3	=> 'Dikirim',
						4	=> 'Diterima',
						5	=> 'Didistribusikan'
					]
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
				$docParam = [
					'docnum'		=> $documentLib->generateDocnum(AssetMoveOutModel::DOCCODE, $lastRowDocNum),
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
									'updated_by'	=> $ousrIdx
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

				$returnData = [
					'good'		=> TRUE,
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
					
					$document = new Document ();
					$documentStatus = $document->getStatusText($omvo['status']);
					
					$returnData = [
						'good' => TRUE,
						'dataTransmit' => [
							'data-canapprove'		=> $ousr->can_approve,
							'data-cansend'			=> $ousr->can_send,
							'data-moveout'			=> $omvo,
							'data-moveoutheads'		=> $mvoThs,
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
				$omvo = $model->where ('docnum', $docnum)->find ()[0];
				$omvo_idx = $omvo->idx;
				switch ($action) {
					default:
						break;
					case 'decline':
						if ($omvo === NULL) ;
						else {
							$updateParam = [
								'status'		=> 0,
								'approval_date' => date ('Y-m-d H:i:s'),
								'approved_by'	=> $ousrIdx,
								'updated_by'	=> $ousrIdx
							];
							$model->update ($omvo_idx, $updateParam);
							$returnData = [
								'good'		=> TRUE,
								'status'	=> 200,
								'message'	=> 'Permintaan di tolak'
							];
						}
						break;
					case 'approve':
						if ($omvo === NULL) ;
						else {
							$updateParam = [
								'status' => 2,
								'approval_date' => date ('Y-m-d H:i:s'),
								'approved_by'	=> $ousrIdx,
								'updated_by'	=> $ousrIdx
							];
							
							$model->update ($omvo_idx, $updateParam);
							$updated = $model->affectedRows ();

							$updated = 1;
							
							if ($updated < 0) ;
							else {
								$model = $this->initModel('ApplicationSettingsModel');
								$numberingFormat = $model->find ('numbering')->tag_value;
								$numberingPeriode = $model->find ('numbering-periode')->tag_value;
								
								$document = new Document ($numberingFormat, $numberingPeriode);
								$model = $this->initModel('AssetMoveInModel');
								$lastOmvi = $model->orderBy ('idx', 'DESC')->find ();
								$lastDocnum = (count ($lastOmvi) == 0) ? NULL : $lastOmvi[0]->docnum;
								$docnumGenerated = $document->generateDocnum(AssetMoveInModel::DOCCODE, $lastDocnum);
								$insertParam = [
									'docnum'		=> $docnumGenerated,
									'omvo_refidx'	=> $omvo_idx,
									'omvo_ousridx'	=> $omvo->ousr_applicant,
									'omvo_olctfrom'	=> $omvo->olct_from,
									'omvo_olctto'	=> $omvo->olct_to,
									'sent'			=> FALSE,
									'sent_date'		=> NULL,
									'received_date'	=> NULL,
									'created_by'	=> $ousrIdx,
									'updated_by'	=> $ousrIdx
								];
								
								$model->insert ($insertParam);
								$omviid = $model->insertID ();
								
								if ($omviid < 1) ;
								else 
									$returnData = [
										'good'		=> TRUE,
										'status'	=> 200,
										'message'	=> 'Permintaan disetujui!'
									];
							}
						}
						break;
					case 'marksent':
						if ($omvo === NULL) ;
						else {
							$model = $this->initModel('AssetMoveOutModel');
							$updateParam = [
								'status'		=> 3,
								'sent_by'		=> $ousrIdx,
								'sent_date'		=> date ('Y-m-d H:i:s'),
								'updated_by'	=> $ousrIdx
							];
							$model->update ($omvo_idx, $updateParam);
							$updated = $model->affectedRows ();
							
							if ($updated < 0) ;
							else {
								$model = $this->initModel('AssetMoveInModel');
								$updateParam = [
									'sent'			=> true,
									'sent_by'		=> $ousrIdx,
									'sent_date'		=> date ('Y-m-d H:i:s'),
									'updated_by'	=> $ousrIdx
								];
								$model->where ('omvo_refidx', $omvo_idx)
										->set ($updateParam)
										->update ();
								$updated = $model->affectedRows ();
								if ($updated == 0) ;
								else
									$returnData = [
										'good'		=> TRUE,
										'status'	=> 200,
										'message'	=> 'Dokumen telah ditandai sebagai dikirim!'
									];
							}
						}
						break;
				}
				$requestResponse['status'] = 200;
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
					$dataPassword = $dataTransmit['form-data']['data-password'];
					if (!password_verify($dataPassword, $user->password)) $response = ['status' => 401, 'message' => 'Password Not Match!'];
					else {
						$returnData = [
							'data-transmit'	=> [
								'id'	=> $user->idx,
								'user'	=> $user->username
							]
						];
						$response = ['status' => 200, 'message' => $returnData];
					}
				}
				break;
			case 'admin-check':
				$model		= $this->initModel('EnduserModel');
				$ousrs		= $model->find ();
				$response	= [
					'status'	=> 200,
					'message'	=> (count ($ousrs) > 0)
				];
				break;
			case 'power-overwhelming':
				$json = $this->getDataTransmit();
				$insertParams = [
					'ougr_idx'		=> 1,
					'username'		=> $json['username'],
					'email'			=> $json['email'],
					'password'		=> password_hash($json['entry-password'], PASSWORD_BCRYPT),
					'created_by'	=> 0,
					'updated_by'	=> 0
				];
				$model = $this->initModel('EnduserModel');
				$model->insert ($insertParams);
				$ousrid = $model->getInsertID();
				
				$update = [
					'created_by'	=> $ousrid,
					'updated_by'	=> $ousrid
				];
				$model->update ($ousrid, $update);
				
				$insertParams = [
					'ousr_idx'		=> $ousrid,
					'olct_idx'		=> 0,
					'status'		=> 'assigned',
					'created_by'	=> $ousrid,
					'updated_by'	=> $ousrid
				];
				$model = $this->initModel ('EnduserLocationModel');
				$model->insert ($insertParams);
				
				$insertParams = [
					'idx'			=> $ousrid,
					'fname'			=> $json['first-name'],
					'mname'			=> $json['middle-name'],
					'lname'			=> $json['last-name'],
					'address1'		=> $json['address-primary'],
					'address2'		=> $json['address-secondary'],
					'phone'			=> $json['phone-num'],
					'email'			=> $json['email'],
					'created_by'	=> $ousrid,
					'updated_by'	=> $ousrid
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