<?php
namespace App\Controllers;


use CodeIgniter\RESTful\ResourceController;


abstract class BaseReSTController extends ResourceController {
    
    protected $format = 'json';
    
    protected function init () { }
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\RESTful\ResourceController::initController()
     */
    public function initController(
            \CodeIgniter\HTTP\RequestInterface $request, 
            \CodeIgniter\HTTP\ResponseInterface $response, 
            \Psr\Log\LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        // TODO Auto-generated method stub
        $this->init();
    }
    
}