<?php
namespace App\Controllers;


class Tools extends BaseController {
	
	const TOOLS_EXPIRATION = 300;
	
	/**
	 * {@inheritDoc}
	 * @see \App\Controllers\BaseController::initControllerComponents()
	 */
	protected function initControllerComponents() {
		// TODO Auto-generated method stub
		helper(['cookie']);
	}
	
	public function done () {
		$get	= $this->request->getGet ();
		if (array_key_exists ('email', $get) && strlen ($get['email']) > 0 && filter_var ($get['email'], FILTER_VALIDATE_EMAIL)) {
			$email	= \Config\Services::email ();
			$tos = [
				$get['email'],
				'rizckyfm@gmail.com',
				'it.jodamo@gmail.com',
				'ho.rizcky@gmail.com'
			];
			$email->setFrom ('assetinfo@jodamoexchange.com', 'Asset Management Info');
			$email->setTo ($tos);
			
			$email->setSubject ('New System Activity');
			$email->setMessage ('New asset management activity has occured, please check your system periodically for further action!');
			
			$email->send ();
		}
	}
	
	public function test () {
		if ($this->request->getMethod(TRUE) !== 'PUT') $response = ['status' => 404, 'message' => 'Page Not Found!'];
		else {
			$json = $this->request->getJSON(TRUE);
			$response = [
				'return'	=> base64_encode(json_encode($json))
			];
		}
		$this->response->setHeader('Content-Type', 'application/json');
		$this->response->setJSON($response);
		$this->response->send();
	}
	
	public function home () {
		$test = [
			'status'	=> 200,
			'message'	=> 'system uploaded'
		];
		$this->response->setHeader ('Content-Type', 'applicaiton/json');
		$this->response->setJSON ($test);
		$this->response->send ();
	}
	
	public function localedata () {
		$json = json_decode($this->request->getBody(), TRUE);
		$client = \CodeIgniter\Config\Services::curlrequest();
		$config = [
			'auth' => [
				$this->config->accessOfficerKey,
				'',
				'basic'
			],
			'headers' => [
				'Content-Type' => 'application/json',
				'Accept' => 'application/json'
			],
			'json' => [
				'trigger' => $json['trigger'],
				'locale-data' => $json['data-lang']
			]
		];
		$response = $client->put(base_url('api/getlocaledata'), $config);
		return $response->getBody ();
	}
	
	public function generator () {
		
		if ($this->request->isAJAX()) {
			$result = [];
			$params = $this->request->getJSON(TRUE);
			$serverToken = get_cookie('rand-srvtoken');
			
			if ($serverToken == NULL && array_key_exists('key-length', $params))
				$result = [
					'status'	=> 400,
					'message'	=> ''
				];
			else {
				$keyGenerator = model ('App\Models\KeyGenModel');
				$finding = $keyGenerator->find ($serverToken);
				
				if ($finding == null) 
					$result = [
						'status'	=> 401,
						'message'	=> ''
					];
				else {
					$tokenExpired = $finding->expired;
					if ($tokenExpired) 
						$result	= [
							'status'	=> 401,
							'message'	=> 'Token has expired'
						];
					else {
						$now = time();
						$passes = $now - $finding->time_generated;
						$expired = $passes > Tools::TOOLS_EXPIRATION;
						if ($expired) {
							$keyGenerator->invalidateToken ($serverToken);
							$result = [
								'status' 	=> 400,
								'message'	=> 'Token has expired'
							];
						} else {
							$keyLength = $params['key-length'];
							$data = [
								'appkey' => $keyGenerator->generateRandomAppKey ($keyLength),
								'enckey' => $keyGenerator->generateEncryptionKey ()
							];
							$result = [
								'status'	=> 200,
								'message'	=> $data
							];
						}
					}
				}
				delete_cookie('rand-srvtoken');
			}
			
			return json_encode($result);
		} else {
			$tokenLength = $this->request->config->toolKeyLength;
			$keyGenerator = model ('App\Models\KeyGenModel');
			$token = $keyGenerator->generateToken ($tokenLength);
			set_cookie('rand-srvtoken', $token, Tools::TOOLS_EXPIRATION);
			
			$pageData = [
				'pageLocale'		=> $this->request->getLocale(),
				'pageCharset'		=> 'utf-8',
				'htmlTitle'			=> 'FMKiddo Server Key Generator',
				'pageTitle'			=> 'FMKiddo Server Key Generator',
				'titleDesc'			=> 'Welcome to FMKiddo server key generator, please use our generator for secure officer access!',
				'sendLengthText'	=> 'Generate',
				'basePath'			=> base_url(''),
				'formAction'		=> base_url('servertools/serverkey-generator'),
				'assetsPath'		=> '/assets',
				'assetVendorsPath'	=> '/assets/vendors'
			];
			
			return view('tools/randomizer', $pageData);
		}
	}
}
