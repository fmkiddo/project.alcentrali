<?php	
namespace App\Controllers;


use CodeIgniter\Controller;


abstract class BaseController extends Controller {
	
	protected const OFFICER_DATA_ON = 'officer-dataon';
	
	protected $locale;
	protected $pageData;
	protected $trigger;
	protected $config;
	private $obfuscator;
	
	protected function sessionDeleteRequest () {
		$cookie = get_cookie(BaseController::OFFICER_DATA_ON);
		$result = [];
		if ($cookie == NULL)
			$result = [
				'status'	=> 404,
				'message'	=> ''
			];
		else {
			$encodedData = 'Basic_' . $this->officerDataEncoding($cookie);
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
					'trigger'		=> 'session-delete-request',
					'data-transmit'	=> $encodedData
				]
			];
			
			$client = \CodeIgniter\Config\Services::curlrequest();
			$srvResponse = $client->put (server_url('api/validation'), $options);
			$result = json_decode($srvResponse->getBody (), TRUE);
		}
		
		return $result;
	}
	
	protected function sessionCheck (): array {
		$cookieData = get_cookie(BaseController::OFFICER_DATA_ON);
		$result = [];
		if ($cookieData == NULL) {
			delete_cookie(BaseController::OFFICER_DATA_ON);
			$result = [
				'status'	=> 401,
				'message'	=> 'No session is available'
			];
		} else {
			$cookieData = unserialize($cookieData);
			$now = time();
			$timeDifference = $now - $cookieData['loginTime'];
			if ($timeDifference >= $this->config->authExpiration) {
				$this->sessionDeleteRequest();
				delete_cookie(BaseController::OFFICER_DATA_ON);
				$result = [
					'status'	=> 440,
					'message'	=> 'Your session has expired, relogin to access the dashboard!'
				];
			} else {
				$data = [
					'time-request'	=> time(),
					'data-transmit'	=> $cookieData['authToken']
				];
				
				$encodedData = 'Basic_' . $this->officerDataEncoding(serialize($data));
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
						'trigger'		=> 'cookie-check',
						'data-transmit'	=> $encodedData
					]
				];
				$client = \Config\Services::curlrequest();
				$serverResponses = $client->put(server_url('api/validation'), $options);
				$response = json_decode($serverResponses->getBody (), TRUE);
				if ($response['status'] != 200) {
					delete_cookie(BaseController::OFFICER_DATA_ON);
					$result = [
						'status'	=> 440,
						'message'	=> 'Your session has expired, relogin to access the dashboard!'
					];
				} else {
					$cookieData = [
						'loginTime'	=> $now,
						'authToken' => $cookieData['authToken']
					];
					
					set_cookie(BaseController::OFFICER_DATA_ON, serialize($cookieData), $this->config->authExpiration);
					$result = $response;
				}
			}
		}
		return $result;
	}
	
	protected function officerDataEncoding ($plaindata): string {
		$iv = $this->obfuscator->generate_iv();
		$encryptedData = $this->obfuscator->encrypt($plaindata, $this->config->officerEncryptKey, $iv);
		$encodedData = 'fmkiddo ' . base64_encode($encryptedData . '____' . $iv);
		return $encodedData;
	}
	
	protected function officerDataDecoding ($encodeddata): string {
		$decodedData = base64_decode(str_replace('fmkiddo ', '', $encodeddata));
		$decryptedData = explode('____', $decodedData);
		$plainData = $this->obfuscator->decrypt($decryptedData[0], $this->config->officerEncryptKey, $decryptedData[1]);
		return $plainData;
	}
	
	protected function isPost () : bool {
		$method = $this->request->getMethod(TRUE);
		return strcmp('POST', $method) == 0;
	}
	
	protected function getLocale () : string {
		$locale = $this->request->getLocale();
		$locale = $locale == NULL || strlen($locale) == 0 ? $this->request->getDefaultLocale() : $locale;		
		return $locale;
	}
	
	protected function validateServerToken ($param) : int {
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
				'trigger'		=> 'token-verification',
				'client-data'	=> $param
			]
		];
		$client = \CodeIgniter\Config\Services::curlrequest();
		$srvResponses = $client->put(server_url('api/administration'), $options);
		$json = json_decode($srvResponses->getBody (), TRUE);
		return $json['status'];
	}
	
	protected function requestServerToken () : string {
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
				'locale-data'	=> $this->getLocale(),
				'trigger'		=> 'token-request'
			]
		];
		
		$client = \CodeIgniter\Config\Services::curlrequest();
		$serverResponse = $client->put(server_url('api/administration'), $options);
		$json = json_decode($serverResponse->getBody (), TRUE);
		if ($json['status'] == 200) $securityToken = $json['message'];
		else $securityToken = '!error!';
		return $securityToken;
	}

	protected function getLocaleResource ($resId) {
		$client = \Config\Services::curlrequest();
		$options =  [
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
				'trigger'		=> $this->trigger,
				'locale-data'	=> $this->locale,
				'resource-id'	=> $resId
			]
		];
		$response = $client->request("PUT", server_url('api/getlocaledata'), $options);
		$json = json_decode($response->getBody (), TRUE);
		return $json['content'];
	}
	
	protected function isAccessKeyValid () : bool {
		$config = $this->request->config;
		
		$result = FALSE;
		
		$configured = $this->isAccessKeyConfigured();
		
		if ($configured) {
			$curlOptions = [
				'auth'		=>	[
					$config->accessOfficerKey,
					'',
					'basic'
				],
				'headers'	=>	[
					'Content-Type'	=> 'application/json',
					'Accept'		=> 'application/json'
				],
				'json'		=>	[
					'locale'		=> $this->locale,
					'trigger'		=> 'serverAuth'
				]
			];
			
			$client = \Config\Services::curlrequest();
			$response = $client->put(server_url('api/validation'), $curlOptions);
			$processed = json_decode($response->getBody (), TRUE);
			if ($processed['status'] == 200) $result = TRUE;
		}
		
		return $result;
	}
	
	protected function checkServerResponse ($response) {
		if (array_key_exists('status', $response) && 
				array_key_exists('srvResponse', $response) && 
				array_key_exists('message', $response)) return true;
		return false;
	}
	
	protected function isAccessKeyConfigured () : bool {
		return strlen(trim($this->config->accessOfficerKey)) != 0;
	}

	protected function isServerAccessKeyConfigured () : bool {
		$result = false;
		$option	= [
			'headers'	=> [
				'Content-Type'	=> 'application/json',
				'Accept'		=> 'application/json'
			],
			'json'		=> [
				'locale'		=> $this->locale,
				'trigger'		=> 'serverAuthCheck'
			]
		];
		$client = \CodeIgniter\Config\Services::curlrequest();
		$serverResponse = $client->put(server_url('api/validation'), $option);
		$responses = json_decode($serverResponse->getBody (), TRUE);
		if ($responses['status'] == 200) $result = TRUE;
		return $result;
	}
	
	protected function isSuperAdminConfigured () : bool {
		$result = false;
		$option = [
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
				'locale'		=> $this->locale,
				'trigger'		=> 'saCheck'
			]
		];
		
		$curl = \Config\Services::curlrequest();
		$serverResponse = $curl->put(server_url('api/validation'), $option);
		$responses = json_decode($serverResponse->getBody (), TRUE);
		if ($responses['status'] == 200) $result = TRUE;
		return $result;
	}
	
	protected function initControllerComponents () { }
	
	public function initController(
			\CodeIgniter\HTTP\RequestInterface $request, 
			\CodeIgniter\HTTP\ResponseInterface $response, 
			\Psr\Log\LoggerInterface $logger) {
		parent::initController($request, $response, $logger);
		$urlLocale = $this->request->getLocale();
		$this->config = \Config\Services::request()->config;
		$this->obfuscator = new \App\Libraries\Obfuscator();
		$this->pageData = [];
		$this->locale = $urlLocale !== NULL ? $urlLocale : $this->request->getDefaultLocale();
		$this->pageData['locale'] = $this->locale;
		$this->initControllerComponents();
	}
	
	public function index () { }
}