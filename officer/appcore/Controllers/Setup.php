<?php
namespace App\Controllers;


class Setup extends BaseController {
    
    /**
     * {@inheritDoc}
     * @see \App\Controllers\BaseController::init()
     */
    protected function init() {
        // TODO Auto-generated method stub
        $this->dataView['assetsFolder'] = base_url('assets');
    }
    
    public function index () {
        return view ('setup', $this->dataView);
    }
}