<?php
namespace App\Controllers;


class Starter extends BaseController {
	
	private const LOGIN_TOKEN_EXPIRY	= 600;
	
	private const SRV_TOKEN = 'srv-token';
	
	/**
	 * {@inheritDoc}
	 * @see \App\Controllers\BaseController::initControllerComponents()
	 */
	protected function initControllerComponents() {
		helper(['cookie']);
	}
	
	public function index () {
		if (!$this->isAccessKeyConfigured()) ;
		elseif (!$this->isServerAccessKeyConfigured()) ;
		elseif (!$this->isAccessKeyValid()) ;
		elseif (!$this->isSuperAdminConfigured())
			return $this->response->redirect(base_url($this->locale . '/server/setup/saofficer'));
		else {
			$toLogin = TRUE;
			
			$checkResult = $this->sessionCheck();
			if ($checkResult['status'] == 200) $toLogin = FALSE;
			
			if ($toLogin) return $this->response->redirect(base_url($this->locale . '/dashboard/do/login'));
			else return $this->response->redirect(base_url($this->locale . '/dashboard/data/welcome'));
		}
	}
	
	public function setup () {
		$isPost = $this->isPost();
		$this->trigger = 'setup';
		if ($isPost) {
			$srvSecurityToken = get_cookie('srv-sectoken');
			if ($srvSecurityToken == NULL or strlen(trim($srvSecurityToken)) == 0) 
				return $this->response->redirect(base_url($this->locale . '/begin'));
			else {
				$statusCode = $this->validateServerToken($srvSecurityToken);
				if (!($statusCode == 200) ) {
					$data;
					if ($statusCode == 404) 
						$data	= [];
					else 
						$data	= [];
					
					return view('', $data);
				} else {
					$post = $this->request->getPost();
					$clientSource = base64_decode($post['client-src']);
					$data;
					$viewName;
					switch ($clientSource) {
						default:
							$options	= [
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
									'trigger'		=> 'token-tablereset'
								]
							];
							
							$client			= \CodeIgniter\Config\Services::curlrequest();
							$srvResponse	= $client->put(server_url('api/administration'), $options);
							$messages		= json_decode($srvResponse->getBody (), TRUE);
							
							if ($messages['status'] == 200) 
								return $this->response->redirect(base_url($this->locale . '/begin'));
							break;
						case 'system-starts':
							$pretext = [
								'page-title'		=> $this->getLocaleResource('C0001'),
								'page-description'	=> $this->getLocaleResource('C0005'),
								'body-title'		=> $this->getLocaleResource('C0006'),
								'body-description'	=> $this->getLocaleResource('C0007'),
								'tusername'			=> $this->getLocaleResource('C0008'),
								'tuserdesc'			=> '',
								'temail'			=> $this->getLocaleResource('C0009'),
								'tphone'			=> $this->getLocaleResource('C0010'),
								'tpassword'			=> $this->getLocaleResource('C0011'),
								'tpconfirm'			=> $this->getLocaleResource('C0012'),
								'tnext'				=> $this->getLocaleResource('C0013'),
								'treset'			=> $this->getLocaleResource('C0014'),
								'message1'			=> '',
								'title1'			=> '',
								'message2'			=> '',
								'title2'			=> ''
							];
							
							$data = [
								'locale'			=> $this->locale,
								'formAction'		=> base_url($this->locale . '/server/setup/initiate-profiles'),
								'vendorAssetsPath'	=> base_url('assets/vendors'),
								'clientSource'		=> base64_encode('data-administration'),
								'text'				=> $pretext,
								'addrels'			=> [
									base_url('assets/setup/theme.css')
								],
								'addscripts'		=> [
									base_url('assets/functions.js')
								]
							];
							
							$viewName = 'setup/administration';
							break;
						case 'data-administration':
							$administration = $post['admin-username'] . '__' . $post['admin-email'] . '__' 
								. $post['admin-phone'] . '__' . $post['admin-password'];
							
							$admindata = $this->officerDataEncoding($administration);
							
							$pretext = [
								'page-title'		=> $this->getLocaleResource('C0001'),
								'page-description'	=> $this->getLocaleResource('C0005'),
								'body-title'		=> $this->getLocaleResource('C0006'),
								'body-description'	=> $this->getLocaleResource('C0015'),
								'tfirstname'		=> $this->getLocaleResource('C0016'),
								'tmidname'			=> $this->getLocaleResource('C0017'),
								'tlastname'			=> $this->getLocaleResource('C0018'),
								'taddress1'			=> $this->getLocaleResource('C0019'),
								'taddress2'			=> $this->getLocaleResource('C0019'),
								'tsubmit'			=> $this->getLocaleResource('C0020'),
								'treset'			=> $this->getLocaleResource('C0014')
							];
							
							$data = [
								'locale'			=> $this->locale,
								'formAction'		=> base_url($this->locale . '/server/setup/process-setup'),
								'vendorAssetsPath'	=> base_url('assets/vendors'),
								'clientSource'		=> base64_encode('data-profiles'),
								'administration'	=> $admindata,
								'text'				=> $pretext,
								'addrels'			=> [
									base_url('assets/setup/theme.css')
								],
								'addscripts'		=> [
									base_url('assets/functions.js')
								]
							];
							
							$viewName = 'setup/profiles';
							break;
						case 'data-profiles':
							$admindata	= $post['encoded-data'];
							
							$profiles		= $post['admin-firstname'] . '__' . $post['admin-middlename'] . '__'
								. $post['admin-lastname'] . '__' . $post['admin-address1'] . '__' . $post['admin-address2'];
							
							$profilesdata = $this->officerDataEncoding($profiles);
							
							$options	= [
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
									'trigger'		=> 'firstTimeSetup',
									'server-token'	=> $srvSecurityToken,
									'data1'			=> 'Basic_' . $admindata,
									'data2'			=> 'Basic_' . $profilesdata
								]
							];
							
							$client = \CodeIgniter\Config\Services::curlrequest();
							$serverResponse = $client->put(server_url('api/administration-setup'), $options);
							$response = json_decode($serverResponse->getBody (), TRUE);
							
							$tokenOptions = [
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
									'trigger'		=> 'token-invalidation',
									'client-data'	=> $srvSecurityToken
								]
							];
							$tokenClient	= \CodeIgniter\Config\Services::curlrequest();
							$tokenClient->put(server_url('api/administration'), $tokenOptions);
							delete_cookie('srv-sectoken');
							
							$viewName = 'setup/dosetup';
							$data = [
								'setupStatus'		=> $response['status'],
								'vendorAssetsPath'	=> base_url('assets/vendors'),
								'formAction'		=> $response['status'] == 200 ? base_url($this->locale . '/begin') : base_url($this->locale . '/server/setup/process-setup'),
								'text'				=> [
									'page-title'		=> $this->getLocaleResource('C0001'),
									'page-description'	=> $this->getLocaleResource('C0005'),
									'body-title'		=> $this->getLocaleResource('C0021'),
									'body-description'	=> $this->getLocaleResource('C0022'),
									'message'			=> $response['status'] == 200 ? $this->getLocaleResource('C0023') : $this->getLocaleResource('C0024'),
									'proceed-btn-text'	=> $response['status'] == 200 ? $this->getLocaleResource('C0025') : $this->getLocaleResource('C0026')
								],
								'addrels'			=> [
									base_url('assets/setup/theme.css')
								],
								'addscripts'		=> [
									base_url('assets/functions.js')
								]
							];
							break;
					}
					
					return view ($viewName, $data);
				}
			}
		} else {
			$pretext = [
				'page-title'		=> $this->getLocaleResource('C0001'),
				'page-description'	=> $this->getLocaleResource('C0002'),
				'body-title'		=> $this->getLocaleResource('C0003'),
				'body-description'	=> $this->getLocaleResource('C0004'),
				'button-start'		=> $this->getLocaleResource('C0005')
			];
			
			$options = [
				'locale'			=> $this->locale,
				'formAction'		=> base_url ($this->locale . '/server/setup/initiate-administrator'),
				'vendorAssetsPath'	=> base_url ('assets/vendors'),
				'clientSource'		=> base64_encode('system-starts'),
				'text'				=> $pretext,
				'addrels'			=> [
					base_url('assets/setup/theme.css')
				],
				'addscripts'		=> [
					base_url('assets/functions.js')
				]
			];
			
			$srvSecurityToken		= $this->requestServerToken();
			set_cookie('srv-sectoken', $srvSecurityToken, Starter::LOGIN_TOKEN_EXPIRY);
			return view ('setup/welcome', $options);
		}
	}
}