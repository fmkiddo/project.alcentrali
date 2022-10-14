<?php
namespace App\Controllers;


class OfficerRequest extends BaseRESTController {
	
	const MIN_LENGTH = 16;
	const MAX_LENGTH = 64;
	const RANDOM_PASSWORD_LENGTH = 16;
	
	/**
	 * {@inheritDoc}
	 * @see \App\Controllers\BaseRESTController::initControllerComponents()
	 */
	
	private function insertOfficer ($data) {
		$dataTransmit = str_replace('Basic_', '', $data);
		$post = unserialize($this->officerDataDecoding($dataTransmit));
		$model = model ('App\Models\OfficerModel');
		$finding = $model->find ($post['username']);
		if ($finding == NULL) {
			$insert = [];
			foreach ($post as $key => $value) {
				if (strcmp($key, 'password') == 0) $insert[$key] = password_hash($value, PASSWORD_BCRYPT);
				else $insert[$key] = $value;
			}
			$insert['created_by'] = 0;
			$insert['updated_by'] = 0;
			$model->insert ($insert);
		} else {
			$password = $post['password'];
			$set = [
				'email'		=> $post['email'],
				'phone'		=> $post['phone']
			];
			if (strlen($password) > 0) $set['password'] = password_hash($password, PASSWORD_BCRYPT);
			$model->set ($set);
			$model->where (['username' => $post['username']]);
			$model->update ();
		}
		return [
			'status'	=> 200,
			'message'	=> 'Operation Success!'
		];
	}
	
	private function officerDataEncoding ($plainData) {
		$iv = $this->obfuscator->generate_iv();
		$encryptedData = $this->obfuscator->encrypt($plainData, $this->config->officerEncryptKey, $iv);
		$encodedData = 'fmkiddo ' . base64_encode($encryptedData . '____' . $iv);
		return $encodedData;
	}
	
	private function officerDataDecoding ($encodedData) {
		$decodedData = base64_decode(str_replace('fmkiddo ', '', $encodedData));
		$decryptedData = explode('____', $decodedData);
		$plainData = $this->obfuscator->decrypt($decryptedData[0], $this->config->officerEncryptKey, $decryptedData[1]);
		return $plainData;
	}
	
	private function updatePassword ($input) {
		
		$newPswd = $input['password'];
		$session = $input['session'];
		
		$ossnmodel = model ('App\Models\UserSessionModel');
		$ossnfinding = $ossnmodel->find ($session);
		
		$result = [];
		if ($ossnfinding == NULL) 
			$result = [
				'status'	=> 500,
				'message'	=> 'System Error! Missing officer session record!'
			];
		else {
			$sessionData = unserialize($ossnfinding->data);
			$username = $sessionData['username'];
			
			$model = model ('\App\Models\OfficerModel');
			$update = [
				'password' => password_hash($newPswd, PASSWORD_BCRYPT)
			];
			$model->set ($update);
			$model->where (['username' => $username]);
			$model->update ();
			$result = [
				'status'	=> 200,
				'message'	=> 'Password Updated!'
			];
		}
		return $result;
	}
	
	private function checkCurrentPassword ($input) {
		$oldPswd = $input['old-pswd'];
		$session = $input['session'];
		
		$ossnmodel = model ('App\Models\UserSessionModel');
		$ossnfinding = $ossnmodel->find ($session);
		
		$result = [];
		if ($ossnfinding == NULL)
			$result = [
				'status' 	=> 500,
				'message'	=> 'Server Error! missing session data!'
			];
		else {
			$sessionData = unserialize($ossnfinding->data);
			$username = $sessionData['username'];
			
			$model = model ('App\Models\OfficerModel');
			$finding = $model->find ($username);
			
			if ($finding == NULL)
				$result = [
					'status'	=> 500,
					'message'	=> 'Server Error! Missing user data!'
				];
			else {
				$passwordVerify = password_verify($oldPswd, $finding->password);
				if (!$passwordVerify)
					$result = [
						'status'	=> 400,
						'message'	=> 'Password does not match!'
					];
				else 
					$result = [
						'status'	=> 200,
						'message'	=> 'Password OK!'
					];
			}
		}
		return $result;
	}
	
	private function getOfficerProfile ($input) {
		$result = [];
		$osmodel	= model ('App\Models\UserSessionModel');
		$osfinding	= $osmodel->find ($input);
		
		if ($osfinding == NULL)
			$result = [
				'status'	=> 500,
				'message'	=> 'Error! Session Not Found'
			];
		else {
			$sessionData = unserialize($osfinding->data);
			$offmodel	= model ('App\Models\OfficerModel');
			$offfinding	= $offmodel->find ($sessionData['username']);
			if ($offfinding == NULL)
				$result = [
					'status'	=> 500,
					'message'	=> 'Error! User Suddenly Not Found!'
				];
			else {
				$model		= model ('App\Models\OfficerProfileModel');
				$finding	= $model->find ($offfinding->id);
				if ($finding == NULL)
					$result = [
						'status'	=> 404,
						'message'	=> 'Error! User has no profile!'
					];
				else {
					$fullname	= $finding->fullname;
					$names		= explode(' ', $fullname);
					$profile	= [
						'officer'	=> $offfinding->username,
						'fullname'	=> $fullname,
						'fname'		=> $names[0],
						'mname'		=> '',
						'lname'		=> $names[count($names) - 1],
						'email'		=> $offfinding->email,
						'phone'		=> $offfinding->phone,
						'address1'	=> $finding->address1,
						'address2'	=> $finding->address2
					];
					
					for ($idx = 1; $idx < (count ($names) - 1); $idx++)
						$profile['mname'] .= $names[$idx];
						
						$result = [
							'status'		=> 200,
							'information'	=> serialize($profile)
						];
				}
			}
		}
		return $result;
	}
	
	private function profileUpdate ($transmission) {
		$result = [];
		$ossnModel		= model ('App\Models\UserSessionModel');
		$ossnFinding	= $ossnModel->find ($transmission['sessionData']);
		if ($ossnFinding == NULL) 
			$result = [
				'status'	=> 401,
				'message'	=> 'Error! session data is not found!'
			];
		else {
			$sessionData	= unserialize($ossnFinding->data);
			$username		= $sessionData['username'];
			$ofcrModel		= model ('App\Models\OfficerModel');
			$ofcrFinding	= $ofcrModel->find ($username);
			
			if ($ofcrFinding == NULL) 
				$result = [
					'status'	=> 404,
					'message'	=> 'Error! Missing officer account data'
				];
			else {
				$userId 	= $ofcrFinding->id;
				$model		= model ('App\Models\OfficerProfileModel');
				$finding	= $model->find ($userId);
				
				if ($finding == NULL) 
					$result = [
						'status'	=> 400,
						'message'	=> 'Missing user profile data!'
					];
				else {
					$update = [
						'fullname'		=> $transmission['fname'] . ' ' . $transmission['mname'] . ' ' . $transmission['lname'],
						'address1'		=> $transmission['address1'],
						'address2'		=> $transmission['address2'],
						'updated_by'	=> $userId
					];
					$model->set ($update)
							->where (['usr_id' => $userId])
							->update ();
					$result = [
						'status'	=> 200,
						'message'	=> 'Profile updated!'
					];
				}
			}
		}
		return $result;
	}
	
	private function getClientList () {
		$result = [];
		$clientModel = model ('App\Models\ClientModel');
		
		$cmFindings = $clientModel->find ();
		
		if ($cmFindings == NULL)
			$result = [
				'status'	=> 404,
				'message'	=> 'No Clients found!'
			];
		else {
			$cprofileModel = model ('App\Models\ClientProfileModel');
			$clientData = [];
			$id = 0;
			
			foreach ($cmFindings as $client) {
				$cac_id = $client->id;
				$cpFindings = $cprofileModel->find ($cac_id);
				$clientData[$id] = [
					'cac'	=> $cac_id,
					'code'	=> $client->clientcode,
					'name'	=> $cpFindings->clientname,
					'pic'	=> $cpFindings->picname,
					'email'	=> $cpFindings->picemail
				];
				$id++;
			}
			$result = [
				'status'	=> 200,
				'data'		=> $clientData
			];
		}
		
		return $result;
	}
	
	private function getApiList () {
		$result = [];
		$apiModel = model ('App\Models\APIModel');
		$findings = $apiModel->find ();
		if ($findings == NULL) 
			$result = [
				'status'	=> 404,
				'message'	=> 'No APIs found!'
			];
		else {
			$result['status']	= 200;
			
			$id = 0;
			foreach ($findings as $row) {
				$apiData[$id] = $row;
				$id++;
			}
			
			$result = [
				'status'	=> 200,
				'data'		=> $apiData
			];
		}
		
		return $result;
	}
	
	public function dataRequest () {
		$params = $this->request->getJSON(TRUE);
		$result = [];
		if (!$this->isServerAccessKeyConfigured()) 
			$result = [];
		else {
			$transmission = $params['data-transmit'];
			switch ($params['trigger']) {
				default:
					$result = [
						'status'	=> 400,
						'message'	=> $params
					];
					break;
				case 'clientapi-list':
					$getApiResult		= $this->getApiList();
					
					if ($getApiResult['status'] != 200) $result = $getApiResult;
					else {
						$getClientResult	= $this->getClientList();
						$dataTransmit = [
							'list-api'		=> $getApiResult['data'],
							'list-client'	=> NULL
						];
						
						if ($getClientResult['status'] == 200) $dataTransmit['list-client'] = $getClientResult['data'];
						$result = [
							'status'		=> 200,
							'data-transmit'	=> 'Basic_' . $this->officerDataEncoding(serialize($dataTransmit))
						];
					}
					break;
				case 'systemapi-list':
					$getResult = $this->getApiList();
					if ($getResult['status'] != 200) $result = $getResult;
					else {
						$dataTransmit = $getResult['data'];
						$result = [
							'status'		=> 200,
							'data-transmit'	=> 'Basic_' . $this->officerDataEncoding(serialize($dataTransmit))
						];
					}
					break;
				case 'process-officer':
					$result = $this->insertOfficer($transmission);
					break;
				case 'profile-update':
					$transmission = str_replace('Basic_', '', $transmission);
					$dataTransmit = unserialize($this->officerDataDecoding($transmission));
					$result = $this->profileUpdate($dataTransmit);
					break;
				case 'update-password': 
					$transmission = str_replace('Basic_', '', $transmission);
					$dataTransmit = unserialize($this->officerDataDecoding($transmission));
					$result = $this->updatePassword($dataTransmit);
					break;
				case 'check-current-password':
					$transmission = str_replace('Basic_', '', $transmission);
					$dataTransmit = unserialize($this->officerDataDecoding($transmission));
					$result = $this->checkCurrentPassword($dataTransmit);
					break;
				case 'officer-profile-get':
					$result = $this->getOfficerProfile($transmission);
					break;
				case 'master-data-officers':
					$model	= model ('App\Models\OfficerModel');
					$officerData = $model->find ();
					if ($officerData == NULL)
						$result = [
							'status'	=> 400,
							'message'	=> 'Error! No data found!'
						];
					else {
						$transmission	= [];
						$idx = 0;
						foreach ($officerData as $row) {
							$dataRow	= [
								'id'		=> $row->id,
								'username'	=> $row->username,
								'email'		=> $row->email,
								'phone'		=> $row->phone
							];
							$transmission[$idx] = $dataRow;
							$idx++;
						}
						
						$dataTransmit = [
							'header'		=> [
								'id'		=> 'ID',
								'username'	=> 'Username',
								'email'		=> 'Email',
								'phone'		=> 'Phone'
							],
							'officerData'	=> $transmission
						];
						
						$result = [
							'status'	=> 200,
							'message'	=> 'Basic_' . $this->officerDataEncoding(serialize($dataTransmit))
						];
					}
					
					break;
				case 'generate-clientkey':
					$clientDataGenerator	= new \App\Libraries\ClientDataGenerator ();
					$newClientKey			= $clientDataGenerator->generateClientKey();
					$result					= [
						'status'		=> 200,
						'data-transmit'	=> 'Basic_' . $this->officerDataEncoding($newClientKey)
					];
					break;
				case 'generate-clientcode':
					$transmit				= $params['transmit'];
					$dataParams 			= unserialize($this->officerDataDecoding(str_replace('Basic_', '', $transmit)));
					$clientDataGenerator	= new \App\Libraries\ClientDataGenerator();
					$newClientCode			= $clientDataGenerator->generateClientCode($dataParams['client-name'], $dataParams['client-api']);
					$result = [
						'status'		=> 200,
						'data-transmit'	=> 'Basic_' . $this->officerDataEncoding($newClientCode)
					];
					break;
				case 'generate-dbname':
					$transmit				= $params['transmit'];
					$dataParams				= unserialize($this->officerDataDecoding(str_replace('Basic_', '', $transmit)));
					$clientDataGenerator	= new \App\Libraries\ClientDataGenerator();
					$newClientDbname		= $clientDataGenerator->generateClientDBName($dataParams['client-name'], $dataParams['client-api']);
					$result = [
						'status'		=> 200,
						'data-transmit'	=> 'Basic_' . $this->officerDataEncoding($newClientDbname)
					];
					break;
				case 'generate-dbpassword':
					$charCollections = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
					$randomPassword = '';
					for ($i = 0; $i < OfficerRequest::RANDOM_PASSWORD_LENGTH; $i++) {
						$char			= $charCollections[mt_rand(0, strlen($charCollections) - 1)];
						$randomPassword .= $char;
					}
					$result = [
						'status'		=> 200,
						'data-transmit'	=> 'Basic_' . $this->officerDataEncoding($randomPassword)
					];
					break;
				case 'generate-dbprefix':
					$transmit				= $params['transmit'];
					$dataParams				= unserialize($this->officerDataDecoding(str_replace('Basic_', '', $transmit)));
					$clientDataGenerator	= new \App\Libraries\ClientDataGenerator();
					$dbprefix				= $clientDataGenerator->generateDbPrefix($dataParams['client-api']);
					$result	= [
						'status'		=> 200,
						'data-transmit'	=> 'Basic_' . $this->officerDataEncoding($dbprefix)
					];
					break;
				case 'api-data-update':
					$dataParams		= unserialize($this->officerDataDecoding(str_replace('Basic_', '', $transmission)));
					$model			= model ('App\Models\APIModel');
					$api = $model->find ($dataParams['apicode']);
					if ($api == NULL) {
						$model->insert ($dataParams);
						$insertID = $model->getInsertID ();
						
						$result = [
							'status'	=> ($insertID > 0) ? 200 : 500,
							'message'	=> ($insertID > 0) ? 'Data Insertion Success!' : 'Data Insertion Failed!'
						];
					} else {
						$update = [
							'apiname'	=> $dataParams['apiname']
						];
						$n = $model->update ($dataParams['apicode'], $update);
						$result = [
							'status'	=> $n ? 200 : 500,
							'message'	=> $n ? 'Data Updated Successfully!' : 'Data Not Updated!'
						];
					}
					break;
				case 'api-clientprocessing':
					$result = [];
					$dataParams = unserialize($this->officerDataDecoding(str_replace('Basic_', '', $transmission)));
					$ocacModel = model ('App\Models\ClientModel');
					$clientCodeName = $dataParams['client-codename'];
					$ocacFinding = $ocacModel->find ($clientCodeName);
					if ($ocacFinding != NULL) {
						$clientID = $ocacFinding->id;
						// update client information first
						
						$cac2Model = model ('App\Models\ClientProfileModel');
						$cac2Update = [
							'clientname'	=> $dataParams['client-fullname'],
							'address'		=> $dataParams['client-address'],
							'npwp'			=> $dataParams['client-npwp'],
							'picname'		=> $dataParams['client-picname'],
							'picemail'		=> $dataParams['client-picemail'],
							'picphone'		=> $dataParams['client-picphone']
						];
						
						$cac2Model->update ($clientID, $cac2Update);
						$result	= [
							'status'	=> 200,
							'message'	=> 'Data updated!'
						];
					} else {
						$oapiModel = model ('App\Models\APIModel');
						$oapiFindings = $oapiModel->find ($dataParams['client-api']);
						
						$ocacInsert = [
							'clientcode'		=> $dataParams['client-codename'],
							'clientpasscode'	=> password_hash($dataParams['client-passcode'], PASSWORD_BCRYPT),
							'clientkey'			=> $dataParams['client-key'],
							'clientapi'			=> $oapiFindings->id,
							'created_by'		=> 0,
							'updated_by'		=> 0
						];
						$ocacModel->insert ($ocacInsert);
						$clientID = $ocacModel->getInsertID ();
						
// 						$result = [
// 							'status' => 400,
// 							'mssage' => $clientID
// 						];
						
						if ($clientID == 0) 
							$result = [
								'status'	=> 500,
								'message'	=> 'Data insertion error!'
							];
						else {
							$realDb = $this->officerDataDecoding(str_replace('Basic_', '', $dataParams['dbpswd']));
							$encrypted = '';
							$iv = $this->obfuscator->generate_iv();
							$encrypted = $this->obfuscator->encrypt($realDb, $dataParams['client-key'], $iv);
							$encodedPswd = 'fmkiddo_' . base64_encode($encrypted . '_=:' . $iv);
							
							$cac1Model = model ('App\Models\ClientDBModel');
							$cac1Insert = [
								'cac_id' 	=> $clientID,
								'dbname'	=> $dataParams['dbname'],
								'dbuser'	=> $dataParams['dbuser'],
								'dbprefix'	=> $dataParams['dbprefix'],
								'dbpswd'	=> $encodedPswd
							];
// 							$result = [
// 								'status'	=> 400,
// 								'message'	=> $encodedPswd
// 							];
							
							$cac1Model->insert ($cac1Insert);
							
							$cac2Model = model ('App\Models\ClientProfileModel');
							$cac2Insert = [
								'cac_id'		=> $clientID,
								'clientname'	=> $dataParams['client-fullname'],
								'address'		=> $dataParams['client-address'],
								'npwp'			=> $dataParams['client-npwp'],
								'picname'		=> $dataParams['client-picname'],
								'picemail'		=> $dataParams['client-picemail'],
								'picphone'		=> $dataParams['client-picphone'],
								'created_by'	=> 0,
								'updated_by'	=> 0
							];
							$cac2Model->insert ($cac2Insert);
						}
						
						$client = \CodeIgniter\Config\Services::curlrequest();
						$dbpswd = $dataParams['dbpswd'];
						$plain	= $this->officerDataDecoding(str_replace('Basic_', '', $dbpswd));
						$dataForger = [
							'headers'	=> [
								'Accept'		=> 'application/json',
								'Content-Type'	=> 'application/json'
							],
							'json'		=> [
								'client-api'	=> $dataParams['client-api'],
								'dbname'		=> $dataParams['dbname'],
								'dbuser'		=> $dataParams['dbuser'],
								'dbpswd'		=> $plain,
								'dbprefix'		=> $dataParams['dbprefix']
							]
						];
						$serverResponses = $client->put(base_url('api/forger'), $dataForger);
						$result = [
							'status'	=> 400,
							'message'	=> $serverResponses
						];
// 						$result = json_decode($serverResponses->getBody (), TRUE);
					}
					break;
			}
		}
		
		return $this->respond($result);
	}
	
	public function officerValidation () {
		$params = $this->request->getJSON(TRUE);
		$result = [];
		if (!$this->isServerAccessKeyConfigured())
			$result = [];
		else {
			$model;
			switch ($params['trigger']) {
				default:
					break;
				case 'session-delete-request':
					$model = model('App\Models\UserSessionModel');
					$encodedData	= str_replace('Basic_', '', $params['data-transmit']);
					$decodedData	= unserialize($this->officerDataDecoding($encodedData));
					$authToken		= $decodedData['authToken'];
					
					$finding		= $model->find ($authToken);
					if ($finding == NULL) 
						$result = [
							'status'	=> 404,
							'message'	=> 'Session not found!'
						];
					else {
						$model->delete ($authToken);
						$result = [
							'status'	=> 200,
							'message'	=> 'Your session has been cleared!'
						];
					}
					break;
				case 'cookie-check':
					$model = model('App\Models\UserSessionModel');
					$encodedData	= str_replace('Basic_', '', $params['data-transmit']);
					$decodedData	= unserialize($this->officerDataDecoding($encodedData));
					$authToken		= $decodedData['data-transmit'];
					
					$finding = $model->find ($authToken);
					
					if ($finding == NULL) 
						$result = [
							'status'	=> 401,
							'message'	=> 'Invalid client token!'
						];
					else {
						$requestTime = $decodedData['time-request'];
						$timeDifference = $requestTime - $finding->timestamp;
						if ($timeDifference >= $this->config->authExpiration) 
							$result = [
								'status'	=> 440,
								'message'	=> 'Your session has expired!'
							];
						else {
							$updateData = [
								'timestamp'	=> $requestTime
							];
							$model->update ($authToken, $updateData);
							$result = [
								'status'	=> 200,
								'message'	=> 'Session is valid!'
							];
						}
					}
					break;
				case 'officer-verification':
					
					$model = model ('App\Models\OfficerModel');
					$unverified = $this->officerDataDecoding(str_replace('Basic_', '', $params['unverified']));
					$unverified = json_decode($unverified, TRUE);
					
					$unverifiedUsername = $unverified['input-username'];
					
					$finding = $model->findOfficer ($unverifiedUsername);
					if ($finding == NULL) 
						$result = [
							'status'	=> 401,
							'message'	=> 'Officer Account not found!'
						];
					else {
						$userid = $finding->id;
						$validPassword = password_verify($unverified['input-password'], $finding->password);
						if (!$validPassword) 
							$result = [
								'status'	=> 401,
								'message'	=> 'Account and password combination does not match!'
							];
						else {
							$timestamp		= time();
							$usModel		= model ('App\Models\UserSessionModel');
							$seed			= random_int(OfficerRequest::MIN_LENGTH, OfficerRequest::MAX_LENGTH);
							$authToken		= $usModel->generateAuthToken ($seed);
							$sessionData	= [
								'username'		=> $finding->username,
								'logged_time'	=> $timestamp,
								'logged'		=> TRUE
							];
							
							$sessionInformation = [
								'id'			=> $authToken,
								'ip_address'	=> $params['ip_address'],
								'timestamp'		=> $timestamp,
								'data'			=> serialize($sessionData)
							];
							$usModel->insert ($sessionInformation);
							
							$encodedInformation = $this->officerDataEncoding($authToken);
							
							$result = [
								'status'		=> 200,
								'message'		=> 'Officer Log in successful!',
								'information'	=> 'Basic_' . $encodedInformation
							];
						}
					}
					break;
				case 'firstTimeSetup':
					$isKeyValid = $this->isAccessKeyValid();
					if (!$isKeyValid) 
						$result = [
							'status'	=> 511,
							'message'	=> ''
						];
					else {
						$data1		= $params['data1'];
						$data1		= str_replace('Basic_', '', $data1);
						$data1		= $this->officerDataDecoding($data1);
						$data1		= explode('__', $data1);
						
						$data2		= $params['data2'];
						$data2		= str_replace('Basic_', '', $data2);
						$data2		= $this->officerDataDecoding($data2);
						$data2		= explode('__', $data2);
						
						$officerModel = model ('App\Models\OfficerModel');
						$officerProfileModel = model ('App\Models\OfficerProfileModel');
						$officerModel->insert ([
							'username'		=> $data1[0],
							'email' 		=> $data1[1],
							'phone'			=> $data1[2],
							'password'		=> password_hash($data1[3], PASSWORD_BCRYPT),
							'created_by'	=> 0,
							'updated_by'	=> 0
						]);
						$officerid = $officerModel->getInsertID ();
						$officerProfileModel->insert ([
							'usr_id'		=> $officerid,
							'fullname'		=> $data2[0] . ' ' . $data2[1] . ' ' . $data2[2],
							'address1'		=> $data2[3],
							'address2'		=> $data2[4],
							'created_by'	=> 0,
							'updated_by'	=> 0
						]);
						
						if ($officerid > 0) 
							$result = [
								'status'	=> 200,
								'message'	=> 'new officer information successfully saved!'
							];
						else 
							$result = [
								'status'	=> 500,
								'message'	=> ''
							];
					}
					break;
				case 'serverAuth':
					$isKeyValid = $this->isAccessKeyValid();
					if (!$isKeyValid)
						$result = [
							'status'	=> 511,
							'message'	=> 'network authentication failed! invalid officer access key'
						];
					else
						$result = [
							'status'	=> 200,
							'message'	=> 'officer key valid! authorization granted'
						];
					break;
				case 'serverAuthCheck':
					if (strlen(trim($this->config->accessOfficerKey)) == 0) 
						$result = [
							'status'	=> 500,
							'message'	=> 'officer access key is not configured! please contact your system administrator!'
						];
					else 
						$result = [
							'status'	=> 200,
							'message'	=> 'access key has been configured!'
						];
					break;
				case 'saCheck':
					$model	= model('App\Models\OfficerModel');
					$checkData	= count ($model->findAll());
					if ($checkData > 0) 
						$result = [
							'status'	=> 200,
							'message'	=> 'sa confirmed'
						];
					else 
						$result = [
							'status'	=> 404,
							'message'	=> 'sa not found'
						];
					break;
			}
		}
		return $this->respond($result);
	}
	
	public function serverSecurity () {
		$isAuthValid	= $this->isAccessKeyValid();
		
		$srvResponse;
		if (!$isAuthValid) 
			$srvResponse = [
				'status'	=> '',
				'message'	=> ''
			];			
		else {
			$params = $this->request->getJSON(TRUE);
			switch ($params['trigger']) {
				default:
					$tokenParam		= base64_decode($params['client-data']);
					$tokenModel		= model ('App\Models\KeyGenModel');
					$tokenModel->invalidateToken ($tokenParam);
					$srvResponse	= [
						'status'	=> 200,
						'message'	=> 'token successfully been invalidated and cannot be used anymore'
					];
					break;
				case 'token-tablereset': 
					$kgmodel		= model ('App\Models\KeyGenModel');
					$kgmodel->truncate ();
					$srvResponse	= [
						'status'	=> 200,
						'message'	=> 'table has been successfully truncated!'
					];
					break;
				case 'token-verification':
					$tokenParam		= base64_decode($params['client-data']);
					$tokenExpiry	= $this->request->config->tokenExpiry;
					$tokenModel		= model ('App\Models\KeyGenModel');
					$tokenIsValid	= $tokenModel->validateToken ($tokenParam, $tokenExpiry);
					switch ($tokenIsValid) {
						default:
							$srvResponse	= [
								'status'	=> 200,
								'message'	=> 'security token is valid'
							];
							break;
						case 0:
							$srvResponse	= [
								'status'	=> 404,
								'message'	=> 'security token is not found!'
							];
							break;
						case -1:
							$srvResponse	= [
								'status'	=> 403,
								'message'	=> 'security token has been expired!'
							];
							break;
					}
					break;
				case 'token-request':
					$tokenSize		= $this->request->config->tokenSize;
					$tokenModel		= model ('App\Models\KeyGenModel');
					$token			= $tokenModel->generateToken ($tokenSize);
					$srvResponse	= [
						'status'	=> 200,
						'message'	=> base64_encode($token)
					];
					break;
			}
		}
		
		return $this->respond($srvResponse);
	}
	
	public function localedata () {
		$srvResponse;
		if (!$this->isAccessKeyValid()) 
			$srvResponse = [
				'status'	=> 400,
				'message'	=> ''
			];
		else {
			$params = $this->request->getJSON(TRUE);
			$localeCode = $params['locale-data'];
			switch ($params['trigger']) {
				default:
					break;
				case 'getbuttonlocales':
					$srvResponse = [
						'status' => 'kontol'
					];
					break;
				case 'setup':
					$localeModel	= model ('App\Models\LocaleModel');
					$textResModel	= model ('App\Models\TextResModel');
					$localeResult	= $localeModel->find ($localeCode);
					$localeId		= $localeResult->id;
					$texts			= $textResModel->where ('rsc_id', $localeId)->find ($params['resource-id']);
					if ($texts == NULL) 
						$srvResponse = [
							'status'	=> 404,
							'message'	=> 'data not found!'
						]; 
					else 
						$srvResponse	= [
							'status'	=> 200,
							'message'	=> 'data found!',
							'content'	=> $texts->content
						];
					break;
			}
		}
		
		return $this->respond($srvResponse);
	}
}