<?php
namespace App\Controllers;


use CodeIgniter\Controller;


abstract class BaseController extends Controller {
    
    protected $dataView;
    
    protected function init () { }
    
    protected function isFirstTime () {
        $firstTime = false;
        return $firstTime;
    }
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Controller::initController()
     */
    public function initController(
            \CodeIgniter\HTTP\RequestInterface $request, 
            \CodeIgniter\HTTP\ResponseInterface $response, 
            \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        // TODO Auto-generated method stub
        $this->setupModel = new \App\Models\SetupModel ();
        $this->dataView = [
            'appLocale'     => $this->request->getLocale(),
            'appCharset'    => 'UTF-8'
        ];
        $this->init();
    }
    
}