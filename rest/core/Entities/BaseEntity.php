<?php
namespace App\Entities;


use CodeIgniter\Entity;


abstract class BaseEntity extends Entity {
    
    /**
     * {@inheritDoc}
     * @see \CodeIgniter\Entity::__construct()
     */
    public function __construct(array $data = null) {
        parent::__construct($data);
        // TODO Auto-generated method stub
    }
    
}