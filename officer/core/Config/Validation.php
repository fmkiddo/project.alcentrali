<?php

namespace Config;

class Validation
{

    // --------------------------------------------------------------------
    // Setup
    // --------------------------------------------------------------------

    /**
     * Stores the classes that contain the
     * rules that are available.
     *
     * @var array
     */
    public $ruleSets = [
        \CodeIgniter\Validation\Rules::class,
        \CodeIgniter\Validation\FormatRules::class,
        \CodeIgniter\Validation\FileRules::class,
        \CodeIgniter\Validation\CreditCardRules::class
    ];

    /**
     * Specifies the views that are used to display the
     * errors.
     *
     * @var array
     */
    public $templates = [
        'list' => 'CodeIgniter\Validation\Views\list',
        'single' => 'CodeIgniter\Validation\Views\single'
    ];

    // --------------------------------------------------------------------
    // Rules
    // --------------------------------------------------------------------
    public $setup = [
        'input-new-username' => [
            'rules' => 'required|regex_match[/^([a-zA-Z]).[a-zA-Z\d]*$/]'
        ],
        'input-new-email' => [
            'rules' => 'required|valid_email'
        ],
        'input-new-password' => [
            'rules' => 'required|min_length[8]|regex_match[/^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[^\w\s\d]).+$/]'
        ],
        'input-confirm-password' => [
            'rules' => 'required|matches[input-new-password]'
        ]
    ];
    
    public $newClient = [
        'input-new-clientname' => [
            'rules' => 'required|alpha_numeric_space'
        ],
        'input-new-clientid' => [
            'rules' => 'required'
        ],
        'input-new-clientkey' => [
            'rules' => 'required'
        ]
    ];

    public $setup_errors = [
        'input-new-username' => [
            'required' => '',
            'regex_match' => ''
        ],
        'input-new-email' => [
            'required' => '',
            'valid_email' => ''
        ],
        'input-new-password' => [
            'required' => '',
            'min_length' => '',
            'regex_match' => ''
        ],
        'input-confirm-password' => [
            'required' => '',
            'matches' => ''
        ]
    ];
    
    public $newClient_errors = [
        'input-new-clientname' => [
            'required' => '',
            'alpha_numeric_space' => ''
        ],
        'input-new-clientid' => [
            'required' => ''
        ],
        'input-new-clientkey' => [
            'required' => ''
        ]
    ];
}
