<?php

namespace App\Database\Structures;


class Osam extends \App\Database\DBStructure {
	
	protected $prefix = 'asm_';
	
	protected $tables = [
		'oaci', 'oast', 'octa', 'oita', 'olct', 'olgt', 'omdl', 'omtd', 'oprf', 'optl', 'osbl', 
		'otxt', 'ougr', 'ousr', 'owar', 'aci1', 'cta1', 'ita1', 'ita2', 'ugr1', 'usr1', 'usr2'
	];
	
	protected $tableStructures = [
		'oaci'	=> [
			'key'			=> 'idx',
			'struct'		=> [
				'idx'			=> [
					'type'				=> 'INT',
					'constraint'		=> 11,
					'unsigned'			=> TRUE,
					'auto_increment'	=> TRUE
				],
				'ci_name'		=> [
					'type'				=> 'VARCHAR',
					'constraint'		=> 100,
				],
				'ci_descript'	=> [
					'type'				=> 'TEXT'
				]
			]
		],
		'oast'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		],
		'octa'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		],
		'oita'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'olct'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		],
		'olgt'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'omdl'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'omtd'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		],
		'oprf'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'optl'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'osbl'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		],
		'otxt'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'ougr'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'ousr'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'owar'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'aci1'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'cta1'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'ita1'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'ita2'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'ugr1'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'usr1'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		], 
		'usr2'	=> [
			'key'			=> '',
			'struct'		=> [
				
			]
		]
	];
}