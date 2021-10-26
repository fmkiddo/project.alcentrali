<?php
namespace App\Controllers;


class Officer extends BaseController {
	
	const LOGIN_TOKEN_EXPIRY	= 600;
	
	private $logon_expiry;
	
	private function dataRequest ($dest, $data) {
		$options = [
			'auth'		=> [
				$this->config->accessOfficerKey,
				'',
				'basic'
			],
			'headers'	=> [
				'Accept'		=> 'application/json',
				'Content-Type'	=> 'application/json'
			],
			'json'		=> $data
		];
		
		$client = \CodeIgniter\Config\Services::curlrequest();
		$serverResponse = $client->put(server_url($dest), $options);
		return json_decode($serverResponse->getBody (), TRUE);
	}
	
	private function getCookie () {
		$cookie = get_cookie(BaseController::OFFICER_DATA_ON);
		return unserialize($cookie);
	}
	
	private function updateNewPassword ($newPswd, $sessionData) {
		$transmissible = [
			'password'	=> $newPswd,
			'session'	=> $sessionData
		];
		$options	= [
			'auth'	=> [
				$this->config->accessOfficerKey,
				'',
				'basic'
			],
			'headers'	=> [
				'Accept'		=> 'application/json',
				'Content-Type'	=> 'application/json'
			],
			'json'		=> [
				'trigger'		=> 'update-password',
				'data-transmit'	=> 'Basic_' . $this->officerDataEncoding(serialize($transmissible))
			]
		];
		
		$client			= \CodeIgniter\Config\Services::curlrequest();
		$serverResponse	= $client->put(server_url('api/dataaccess'), $options);
		return json_decode($serverResponse->getBody (), TRUE);
	}
	
	private function validateOldPassword ($oldpswd, $sessionData) {
		$transmissible = [
			'old-pswd'	=> $oldpswd,
			'session'	=> $sessionData
		];
		$options = [
			'auth'		=> [
				$this->config->accessOfficerKey,
				'',
				'basic'
			],
			'headers'	=> [
				'Accept'		=> 'application/json',
				'Content-Type'	=> 'application/json'
			],
			'json'		=> [
				'trigger'		=> 'check-current-password',
				'data-transmit'	=> 'Basic_' . $this->officerDataEncoding(serialize($transmissible))
			]
		];
		
		$client			= \CodeIgniter\Config\Services::curlrequest();
		$serverResponse	= $client->put(server_url('api/dataaccess'), $options);
		return json_decode($serverResponse->getBody (), TRUE);
	}
	
	private function updateOfficerProfile ($params) {
		$dataTransmit = $this->officerDataEncoding(serialize($params));
		
		$options		= [
			'auth'		=> [
				$this->config->accessOfficerKey,
				'',
				'basic'
			],
			'headers'	=> [
				'Accepts'		=> 'application/json',
				'Content-Type'	=> 'application/json'
			],
			'json'		=> [
				'trigger'		=> 'profile-update',
				'data-transmit'	=> 'Basic_' . $dataTransmit
			]
		];
		
		$client 		= \CodeIgniter\Config\Services::curlrequest();
		$serverResponse	= $client->put(server_url('api/dataaccess'), $options);
		return json_decode($serverResponse->getBody (), TRUE);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \App\Controllers\BaseController::initControllerComponents()
	 */
	protected function initControllerComponents() {
		// TODO Auto-generated method stub
		helper(['cookie']);
	}
	
	public function dataprocessing ($params) {
		$dataCookie = $this->getCookie();
		$result = [];
		if ($dataCookie == NULL) ;
		else {
			$sessionCheck = $this->sessionCheck();
			if ($sessionCheck['status'] != 200) {
				delete_cookie(BaseController::OFFICER_DATA_ON);
				
			} else {
				switch ($params) {
					case 'officer':
						$post			= $this->request->getPost();
						$dataTransmit	= $this->officerDataEncoding(serialize($post));
						$jsonData = [
							'trigger'		=> 'process-officer',
							'data-transmit'	=> 'Basic_' . $dataTransmit
						];
						$response = $this->dataRequest('api/dataaccess', $jsonData);
						if ($response ['status'] != 200) ;
						$this->response->redirect(base_url($this->locale . '/dashboard/data/officer'));
						break;
					case 'updateprofilesave':
						$result = [];
						$json		= $this->request->getJSON(TRUE);
						$curlParam	= [];
						foreach ($json as $line) 
							$curlParam[$line['name']] = $line['value'];
						
						$curlParam['sessionData'] = $dataCookie['authToken'];
						
						$result = $this->updateOfficerProfile($curlParam);
						break;
					case 'newpasswordsave':
						$result = [];
						
						$json		= $this->request->getJSON(TRUE);
						$oldpswd	= $json[0]['value'];
						$newpswd	= $json[1]['value'];
						$cnfpswd	= $json[2]['value'];
						
						if (strlen(trim($oldpswd)) == 0 || 
								strlen(trim($newpswd)) == 0 || 
								strlen(trim($cnfpswd)) == 0)
							$result = [
								'status'	=> 404,
								'message'	=> 'Invalid Password Input'
							];
						else {
							$oldPasswordValidation = $this->validateOldPassword($oldpswd, $dataCookie['authToken']);
							if (strcmp($oldpswd, $newpswd) == 0)
								$result = [
									'status'	=> 403,
									'message'	=> ''
								];
							elseif (!strcmp($newpswd, $cnfpswd) == 0) 
								$result = [
									'status'	=> 412,
									'message'	=> ''
								];
							elseif (!($oldPasswordValidation['status'] == 200))
								$result = [
									'status'	=> 400,
									'message'	=> $oldPasswordValidation['message']
								];
							else
								$result = $this->updateNewPassword($newpswd, $dataCookie['authToken']);
						}
						break;
					case 'client-processing':
						$post = $this->request->getPost();
						$post['dbpswd']		= 'Basic_'	. $this->officerDataEncoding($post['dbpswd']);
						$post['cookieData'] = $dataCookie['authToken'];
						$jsonData = [
							'trigger'		=> 'api-clientprocessing',
							'data-transmit'	=> 'Basic_' . $this->officerDataEncoding(serialize($post))
						];
						$result = $this->dataRequest('api/dataaccess', $jsonData);
						break;
					case 'api':
						$post = $this->request->getPost();
						$jsonData = [
							'trigger'		=> 'api-data-update',
							'data-transmit'	=> 'Basic_' . $this->officerDataEncoding(serialize($post))
						];
						$result = $this->dataRequest('api/dataaccess', $jsonData);
						return $this->response->redirect(base_url($this->locale . '/dashboard/data/api'), 'get');
					case 'client':
						$post = $this->request->getPost();
						$trigger = $post['trigger'];
						if (!(strcmp($trigger, 'generate-key') == 0 ||
							strcmp($trigger, 'generate-clientcode') == 0 ||
							strcmp($trigger, 'generate-dbname') == 0 ||
							strcmp($trigger, 'generate-password') == 0 ||
							strcmp($trigger, 'generate-dbprefix') == 0)) {
							$result = [
								'status'	=> 401,
								'message'	=> 'Invalid Request'
							];
						} else {
							$jsonData = [];
							switch ($trigger) {
								case 'generate-key':
									$jsonData = [
										'trigger'			=> 'generate-clientkey'
									];
									break;
								case 'generate-clientcode':
									$transmit = [
										'client-name'		=> $post['client-name'],
										'client-api'		=> $post['client-api']
									];
									$jsonData = [
										'trigger'			=> 'generate-clientcode',
										'transmit'		=> 'Basic_' . $this->officerDataEncoding(serialize($transmit))
									];
									break;
								case 'generate-dbname':
									$transmit = [
										'client-name'		=> $post['client-name'],
										'client-api'		=> $post['client-api']
									];
									$jsonData = [
										'trigger'			=> 'generate-dbname',
										'transmit'		=> 'Basic_' . $this->officerDataEncoding(serialize($transmit))
									];
									break;
								case 'generate-password':
									$jsonData = [
										'trigger'			=> 'generate-dbpassword'
									];
									break;
								case 'generate-dbprefix':
									$transmit = [
										'client-api'		=> $post['client-api']
									];
									$jsonData = [
										'trigger'			=> 'generate-dbprefix',
										'transmit'		=> 'Basic_' . $this->officerDataEncoding(serialize($transmit))
									];
									break;
							}
							$jsonData['data-transmit'] = $dataCookie['authToken'];
							$dataResponse = $this->dataRequest('api/dataaccess', $jsonData);
							if ($dataResponse['status'] != 200) $result = $dataResponse;
							else {
								$transmit	= $dataResponse['data-transmit'];
								$message	= $this->officerDataDecoding(str_replace('Basic_', '', $transmit));
								$result		= [
									'status'	=> 200,
									'generated'	=> $message
								];
							}
						}
						break;
				}
			}
		}
		return json_encode($result);
	}
	
	public function dashboard ($params) {
		$dataCookie = $this->getCookie();
		if ($dataCookie == NULL) return $this->response->redirect(base_url($this->locale . '/begin'));
		else {
			$sessionCheck = $this->sessionCheck();
			if ($sessionCheck['status'] != 200) {
				delete_cookie(BaseController::OFFICER_DATA_ON);
				return $this->response->redirect(base_url($this->locale . '/begin'));
			} else {
				$options	= [
					'assets_url'		=> base_url('assets'),
					'vendor_components'	=> [
						'vendors_dir'	=> '/vendors',
						'styles'		=> [
							'/bootstrap/css/bootstrap.min.css',
							'/fontawesome/css/all.css',
							'/fonts/fondamento/stylesheet.css'
						],
						'scripts'		=> [
							'/jquery/jquery-3.5.1.min.js',
							'/jquery-easing/jquery.easing.min.js',
							'/bootstrap/js/bootstrap.bundle.min.js',
							'/fontawesome/js/all.min.js'
						]
					],
					'assets_components'	=> [
						'assets_dir'	=> '',
						'styles'		=> [
							'/dashboard/theme.css'
						],
						'scripts'		=> [
							'/functions.js',
							'/dashboard/scripts.js'
						]
					]
				];
				
				switch ($params) {
					default:
						throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('The page you are looking for is not found!');
						break;
					case 'client':
						$cookieData = $this->getCookie();
						$jsonData = [
							'trigger'		=> 'clientapi-list',
							'data-transmit'	=> $cookieData['authToken']
						];
						$dataResponse	= $this->dataRequest('api/dataaccess', $jsonData);
						if ($dataResponse['status'] != 200) ;
						else {
							$responseData = $this->officerDataDecoding(str_replace('Basic_', '', $dataResponse['data-transmit']));
							$responseData = unserialize($responseData);
							array_push($options['vendor_components']['styles'], '/datatables/datatables.css');
							array_push($options['vendor_components']['scripts'], '/datatables/datatables.js');
							$options['theaders']	= [
								'Client ID', 'Client Name', 'PIC', 'Email'
							];
							$options['listApi'] 	= $responseData['list-api'];
							$options['listClients']	= $responseData['list-client'];
						}
						return view ('officer/dashboard/clients', $options);
					case 'api':
						$cookieData = $this->getCookie();
						$jsonData = [
							'trigger'		=> 'systemapi-list',
							'data-transmit'	=> $cookieData['authToken']
						];
						$dataResponse = $this->dataRequest('api/dataaccess', $jsonData);
						$options['theaders'] = [
							'API Code', 'API Description'
						];
						array_push($options['vendor_components']['styles'], '/datatables/datatables.css');
						array_push($options['vendor_components']['scripts'], '/datatables/datatables.js');
						if ($dataResponse['status'] != 200) 
							$options['listApi'] = $dataResponse;
						else {
							$dataApi = $this->officerDataDecoding(str_replace('Basic_', '', $dataResponse['data-transmit']));
							$options['listApi'] = unserialize($dataApi);
						}
						return view ('officer/dashboard/system-api', $options);
					case 'officer':
						$cookieData = $this->getCookie();
						$jsonData = [
							'trigger'		=> 'master-data-officers',
							'data-transmit'	=> $cookieData['authToken']
						];
						$dataResponse	= $this->dataRequest('api/dataaccess', $jsonData);
						$decode			= $this->officerDataDecoding(str_replace('Basic_', '', $dataResponse['message']));
						$officerData	= unserialize($decode);
						array_push($options['vendor_components']['styles'], '/datatables/datatables.css');
						array_push($options['vendor_components']['scripts'], '/datatables/datatables.js');
						$options['thead']	= $officerData['header'];
						$options['tbody']	= $officerData['officerData'];
						return view ('officer/dashboard/officers', $options);
					case 'profile':
						$cookieData = $this->getCookie();
						$data = [
							'trigger'		=> 'officer-profile-get',
							'data-transmit'	=> $cookieData['authToken']
						];
						$response = $this->dataRequest('api/dataaccess', $data);
						if ($response['status'] == 500) {
							delete_cookie(BaseController::OFFICER_DATA_ON);
							$this->response->redirect(base_url($this->locale . '/begin'));
						} elseif ($response['status'] == 404) {
							$options['officername']		= 'Unavailable -- edit to change';
							$options['profiles']		= [
								'fullname'	=> 'not set',
								'fname'		=> 'not set',
								'mname'		=> 'not set',
								'lname' 	=> 'not set',
								'email'		=> 'not set',
								'phone'		=> 'not set',
								'address1'	=> 'not set',
								'address2'	=> 'not set'
							];
						} else {
							$profile = unserialize($response['information']);
							
							$options['officername']		= $profile['officer'];
							$options['profiles']		= $profile;
						}
						$keys = [
							'fname'			=> 'First Name',
							'mname'			=> 'Middle Name',
							'lname'			=> 'Last Name',
							'email'			=> 'Email',
							'phone'			=> 'Phone Number',
							'address1'		=> 'Address 1',
							'address2'		=> 'Address 2'
						];
						$options['keys']			= $keys;
						$options['formpswdaction']	= base_url($this->locale . '/dashboard/data/newpasswordsave');
						$options['formpupdtaction']	= base_url($this->locale . '/dashboard/data/updateprofilesave');
						return view('officer/dashboard/profile', $options);
					case 'index':
					case 'welcome':
						return view('officer/dashboard/welcome', $options);
					case 'logout':
						$return = $this->sessionDeleteRequest();
						if ($return['status'] == 200) {
							delete_cookie(BaseController::OFFICER_DATA_ON);
							$this->response->redirect(base_url($this->locale . '/begin'));
						}
						break;
				}
			}
		}
	}
	
	public function do ($params) {
		$view;
		$data;
		
		$isPost = $this->isPost();
		if ($isPost) {
			$postData = $this->request->getPost();
//			$srvSecurityToken = get_cookie ('srv-loginsectoken');
			$isTokenValid = TRUE; // $this->validateServerToken ($srvSecurityToken);
			if (!$isTokenValid) 
				return $this->response->redirect(base_url($this->locale . '/begin'));
			else 
				switch ($params) {
					default:
						$view = '';
						$data = [];
						break;
					case 'process-login':
						$postData = json_encode($postData);
						$unverified = $this->officerDataEncoding($postData);
						$options = [
							'auth'		=> [
								$this->config->accessOfficerKey,
								'',
								'basic'
							],
							'headers'	=> [
								'Content-Type'	=> 'application/json',
								'Accept'		=> 'application/json'
							],
							'json'		=> [
								'trigger'		=> 'officer-verification',
								'ip_address'	=> $this->request->getIPAddress(),
								'unverified'	=> 'Basic_' . $unverified
							]
						];
						
						$client	= \CodeIgniter\Config\Services::curlrequest();
						$serverResponses = $client->put(server_url('api/validation'), $options);;
						$response = json_decode($serverResponses->getBody (), TRUE);
						if ($response['status'] != 200) 
							return $serverResponses->getBody ();
						else {
							$authData		= str_replace('Basic_', '', $response['information']);
							$authToken		= $this->officerDataDecoding($authData);
							$cookieData		= [
								'loginTime'		=> time(),
								'authToken'		=> $authToken
							];
							
							$serialized_authdata = serialize($cookieData);
							set_cookie(BaseController::OFFICER_DATA_ON, $serialized_authdata, $this->config->authExpiration);
							$jsonResponse = [
								'status'		=> $response['status'],
								'redirect_data'	=> base_url($this->locale . '/begin')
							];
							return json_encode($jsonResponse);
						}
						break;
				}
		} else {
			$scResponse = $this->sessionCheck();
			if ($scResponse['status'] == 200) 
				return $this->response->redirect(base_url($this->locale . '/begin'));
			else {
				$view = 'officer/dologin';
				$data = [
					'vendorAssetsPath'	=> base_url('assets/vendors'),
					'formAction'		=> base_url($this->locale . '/dashboard/do/loginprocess'),
					'text'				=> [
						'username'			=> 'Nama User',
						'password'			=> 'Password',
						'login'				=> 'Sign In',
						'sign-up-message'	=> ''
					],
					'addrels'			=> [
						base_url('assets/login/theme.css')
					],
					'addscripts'		=> [
						base_url('assets/functions.js')
					]
				];
	// 			$srvSecurityToken	= $this->requestServerToken();
	// 			set_cookie('srv-loginsectoken', $srvSecurityToken, Officer::LOGIN_TOKEN_EXPIRY);
				
				return view($view, $data);
			}
		}
	}
}