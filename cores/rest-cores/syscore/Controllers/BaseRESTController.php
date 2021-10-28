<?php
namespace App\Controllers;


use CodeIgniter\RESTful\ResourceController;


abstract class BaseRESTController extends ResourceController {
	
	protected $config;
	protected $format = 'json';
	protected $locale;
	protected $obfuscator;
	protected $keygenmodel;
	
	protected function initControllerComponents () { }
	
	protected function isServerAccessKeyConfigured () {
		return strlen(trim($this->config->accessOfficerKey)) != 0;
	}
	
	protected function isAccessKeyValid (): bool {
		$clientAuth	= $this->request->getHeader('Authorization');
		$clientKey	= $clientAuth->getValue();
		$clientKey	= base64_decode(str_replace('Basic ', '', $clientKey));
		$clientKey	= substr($clientKey, 0, strlen($clientKey) - 1);
		return strcmp($clientKey, $this->config->accessOfficerKey) == 0;
	}
	
	public function initController (
			\CodeIgniter\HTTP\RequestInterface $request,
			\CodeIgniter\HTTP\ResponseInterface $response,
			\Psr\Log\LoggerInterface $logger) {
		parent::initController($request, $response, $logger);
		$this->config = $this->request->config;
		$this->obfuscator = new \App\Libraries\Obfuscator();
		$this->initControllerComponents();
	}
}