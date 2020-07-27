<?php
namespace App\Models;


use CodeIgniter\Model;


abstract class BaseModel extends Model {
    
    protected function init () { }
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Model::__construct()
     */
    public function __construct(
            \CodeIgniter\Database\ConnectionInterface &$db = null, 
            \CodeIgniter\Validation\ValidationInterface $validation = null) {
        parent::__construct($db, $validation);
        // TODO Auto-generated method stub
        $this->init();
    }
    
}