<?php
namespace App\Controllers;


class ClientRequest extends BaseRESTController {
	
	private function decodeClientDBPassword (
			$encryptedPassword,
			$clientKey) {
		$dbpassword = '';
		$decode1 = str_replace('fmkiddo_', '', $encryptedPassword);
		$decode2 = base64_decode($decode1);
		$decode3 = explode('_=:', $decode2);
		$dbpassword = $this->obfuscator->decrypt($decode3[0], $clientKey, $decode3[1]);
		return $dbpassword;
	}
	
	private function isPostAndMultipart () {
		$method	= $this->request->getMethod(TRUE);
		$haveContentType = $this->request->hasHeader('Content-Type');
		if ($method !== 'POST') return false;
		elseif (!$haveContentType) return false;
		else {
			$header = $this->request->getHeader('Content-Type')->getValue();
			return ($method === 'POST' && strpos ($header, 'multipart/form-data') !== FALSE);
		}
	}
	
	private function isPutAndJSON () {
		$method = $this->request->getMethod(TRUE);
		$haveContentType = $this->request->hasHeader('Content-Type');
		if ($method !== 'PUT') return false;
		elseif (!$haveContentType) return false;
		else {
			$header = $this->request->getHeader('Content-Type')->getValue();
			return ($method === 'PUT' && $header === 'application/json');
		}
	}
	
	private function getRequestDirective () {
		if ($this->request->getMethod(TRUE) === 'PUT') return $this->request->getJSON(TRUE)['data-trigger'];
		elseif ($this->request->getMethod(TRUE) === 'POST') return $this->request->getPost('data-trigger');
	}
	
	private function getClientAPI ($clientapi_id) {
		$model = model('App\Models\APIModel');
		return $model->where (['id' => $clientapi_id])->find ()[0];
	}
	
	private function initModules ($request, $clientData) {
		$apiid	= $clientData->clientapi;
		$api	= $this->getClientAPI($apiid);
		if ($api == NULL) return ['status'	=> 'invalid'];
		else {
			$db				= $this->initClientDatabase($clientData);
			$moduleclass	= 'App\Libraries\APIModules\\' . ucfirst($api->apicode) . 'Module';
			return new $moduleclass ($db, $request);
		}
	}
	
	private function requestExecution () {
		$requestResponse = [];
		$clientData = $this->getClient();
		if ($clientData == NULL) 
			$requestResponse = [
				'status'	=> 400,
				'message'	=> 'Bad Request'
			];
		else {
			$module = $this->initModules($this->request, $clientData);
			if ($module === NULL) $requestResponse = ['status' => 400, 'message' => 'Bad Request!'];
			else $requestResponse = $module->executeRequest($this->getRequestDirective());
		}
		return $requestResponse;
	}
	
	private function getClient () {
		$clientAuth		= $this->decodeAuth();
		$clientModel = model ('App\Models\ClientModel');
		return $clientModel->find ($clientAuth[0]);
		// return $clientModel->find ('osamjodex_617906f1d431f');
	}
	
	private function serverRequest ($authString) {
		$response = [];
		if (!$this->verifyClientKey($authString)) $response = ['status' => 401, 'message' => 'Unauthorized System Access Logged!'];
		else {
			$authData	= explode('#', json_decode(base64_decode(base64_decode(str_replace('Basic ', '', $authString)))));
			$model		= new \App\Models\ClientModel();
			$clientData	= $model->find ($authData[0]);
			if ($clientData == NULL) $response = ['status' => 401, 'message' => 'Unauthorized System Access Logged - ' . $authData[0]];
			else {
				/**
				 * 
				 * @var \App\Libraries\ModuleInterface $module
				 */
				$module = $this->initModules($this->request, $clientData);
				$response = $module->serverRequest($this->getRequestDirective());
			}
		}
		return $response;
	}
	
	private function decodeAuth () {
		$authString = base64_decode (str_replace ('Basic ', '', $this->request->getHeader ('Authorization')->getValue ()));
		$authString = substr ($authString, 0, strlen ($authString) - 1);
		$authString = json_decode (base64_decode ($authString), TRUE);
		$clientAuth = explode ('#', $authString);
		return $clientAuth;
	}
	
	private function initClientDatabase ($clientData) {
		$db = NULL;
		
		$clientDBModel = model('App\Models\ClientDBModel');
		$clientDB = $clientDBModel->find ($clientData->id);
		if ($clientDB != NULL) {
			$dboptions = [
				'DSN'      => '',
				'hostname' => 'localhost',
				'username' => $clientDB->dbuser,
				'password' => $this->decodeClientDBPassword($clientDB->dbpswd, $clientData->clientkey),
				'database' => $clientDB->dbname,
				'DBDriver' => 'MySQLi',
				'DBPrefix' => $clientDB->dbprefix,
				'pConnect' => true,
				'DBDebug'  => (ENVIRONMENT !== 'production'),
				'cacheOn'  => false,
				'cacheDir' => '',
				'charset'  => 'utf8',
				'DBCollat' => 'utf8_bin',
				'swapPre'  => '',
				'encrypt'  => false,
				'compress' => false,
				'strictOn' => false,
				'failover' => [],
				'port'     => 3306,
			];
			
			$db = db_connect($dboptions);
		}
		
		return $db;
	}
	
	private function authorizationProcess () {
		$response = [];
		if ($this->request->getMethod(TRUE) !== 'PUT') 
			$response	= [
				'status'	=> 404,
				'message'	=> 'Page Not Found!'
			];
		else {
			$authString = base64_decode (str_replace ('Basic ', '', $this->request->getHeader ('Authorization')->getValue ()));
			$clientAuth = json_decode (base64_decode (str_replace ('fmkiddo_', '', $authString)), TRUE);
			$model		= new \App\Models\ClientModel ();
			$client		= $model->find ($clientAuth['clientcode']);
			$verified	= TRUE;
			$clientdata	= '';
			if ($client === NULL) $verified = FALSE;
			else {
				$clientVerified = password_verify ($clientAuth['clientpasscode'], $client->clientpasscode);
				if (!$clientVerified) $verified = FALSE;
				else $clientdata = base64_encode($client->clientkey);
			}
			
			if (!$verified) 
				$response = [
					'status'	=> 401,
					'message'	=> 'Unauthorized Access Attempt Logged!'
				];
			else { 
// 				/**
// 				 * 
// 				 * @var \App\Libraries\ModuleInterface $module
// 				 */
				$module = $this->initModules($this->request, $client);
				if (is_array($module)) $response = ['status' => '401', 'message' => $module['message']];
				else {
					$isAdminSet = $module->serverRequest('admin-check');
					$response	= [
						'status'	=> 200,
						'message'	=> [
							'System Access Authorized!',
							$client->clientcode . '#' . $clientdata,
							$isAdminSet['message']
						]
					];
				}
			}
		}
		return $response;
	}
	
	private function verifyClientKey ($authData) {
		$authString = str_replace('Basic ', '', $authData);
		$auth		= explode('#', json_decode(base64_decode(base64_decode($authString))));
		$model		= new \App\Models\ClientModel();
		$client		= $model->find ($auth[0]);
		if ($client == NULL) $response = ['status' => 401, 'message' => 'Unauthorized Access Detected! Your action has beed logged!'];
		else {
			$validKey = strcmp (base64_decode($auth[1]), $client->clientkey) == 0;
			if (!$validKey) $response = ['status' => 401, 'message' => 'Unauthorized Access Detected! Your action has beed logged!'];
			else $response = ['status' => 200, 'message' => TRUE];
		}
		
		return $response;
	}
	
	private function processSetup ($authResponse, $data) {
		if ($authResponse['status'] != 200) $response = $authResponse;
		else {
			$auth = $this->request->getHeader('Authorization')->getValue();
			$authString = str_replace('Basic ', '', $auth);
			$authData = explode ('#', json_decode(base64_decode(base64_decode($authString)), TRUE));
			
			$model	= new \App\Models\ClientModel();
			$clientData = $model->find ($authData[0]);
			if ($clientData == NULL) $response = ['status' => 500, 'message' => 'Internal Server Error!'];
			else {
				$module = $this->initModules($this->request, $clientData);
				$response = $module->serverRequest ($this->getRequestDirective());
			}
		}
		return $response;
	}
	
	public function connectionTest () {
		if ($this->request->getMethod(TRUE) === 'PUT') 
			return $this->response->setJSON(['status' => 200, 'message' => 'Done!']);
		else return $this->response->setJSON(['status' => 404, 'message' => 'Not Found!']);
	}
	
	public function sendEmailTest () {
		if ($this->request->getMethod(TRUE) === 'PUT') {
			$email = \Config\Services::email();
			$email->setFrom('rizkyfm64@gmail.com', 'Rizcky N. Ardhy');
			$email->setTo('rizckyfm@gmail.com');
			$email->setCC('it.jodamo@gmail.com');
			
			$email->setSubject('This is asset management email');
			$email->setMessage('Test Asset Managmenet Message');
			
			$email->send();
		}
		return $this->response->setJSON(['status' => 404, 'message' => 'Page not found!']);
	}
	
	public function dataCheck () {
		if ($this->request->getMethod(TRUE) !== 'PUT') $response = ['status' => 404, 'message' => 'Page Not Found!'];
		else {
			$authString = $this->request->getHeader ('Authorization')->getValue ();
			$response = $this->serverRequest($authString);
		}
		$this->response->setJSON ($response);
		$this->response->send ();
	}
	
	public function setupFirstTime () {
		if ($this->request->getMethod(TRUE) !== 'PUT') $response = ['status' => 404, 'message' => 'Page Not Found!'];
		else {
			$auth = $this->request->getHeader ('Authorization')->getValue ();
			$response = $this->processSetup($this->verifyClientKey($auth), $this->request->getJSON(TRUE));
		}
		$this->response->setJSON ($response);
		$this->response->send ();
	}
	
	public function clientAuthentication () {
		$responseData	= $this->authorizationProcess();
		$this->response->setJSON ($responseData);
		$this->response->send ();
	}
	
	public function keyAuthentication () {
		$auth = $this->request->getHeader('Authorization')->getValue ();
		$this->response->setJSON($this->verifyClientKey($auth));
		$this->response->send();
	}
	
	public function dataRequest () {
		$requestMethod = $this->request->getMethod(TRUE);
		if (!($requestMethod === 'PUT' || $requestMethod === 'POST'))
			return $this->respond([
				'status'	=> 400,
				'message'	=> 'Bad Request!'
			]);
		else {
			$response = $this->requestExecution();
			return $this->respond($response);
		}
	}

	public function mobileDataRequest () {
		$requestMethod = $this->request->getMethod(TRUE);
		if (!($requestMethod === 'PUT' || $requestMethod === 'POST'))
			return $this->respond([
				'status'	=> 400,
				'message'	=> 'Bad Request!'
			]);
		else {
			$response = $this->requestExecution();
			if ($response['status'] == 200) {
				$decodeResponse = unserialize (base64_decode ($response['message']));
				$jsonResponse = json_encode ($decodeResponse);
				$newResponse = [
					'status'	=> $response['status'],
					'message'	=> base64_encode ($jsonResponse)
				];
				return $this->respond ($newResponse);
			}
			return $this->respond ($response);
		}
	}
}
