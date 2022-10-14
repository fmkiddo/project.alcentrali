<?php
namespace App\Controllers;


use CodeIgniter\Controller;


abstract class BaseController extends Controller {
	
	protected $config;
	protected $obfuscator;
	
	protected function initControllerComponents () { }
	
	public function initController (
			\CodeIgniter\HTTP\RequestInterface $request,
			\CodeIgniter\HTTP\ResponseInterface $response,
			\Psr\Log\LoggerInterface $logger) {
		parent::initController($request, $response, $logger);
		$this->config = $this->request->config;
		$this->obfuscator = new \App\Libraries\Obfuscator();
		$this->initControllerComponents();
	}
	
	public function index () { }
}