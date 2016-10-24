<?php
return [
	'propel' => [
		'database' => [
			'connections' => [
				'fresh_fridge' => [
					'adapter' => 'mysql',
					'classname' => 'Propel\Runtime\Connection\ConnectionWrapper',
					'dsn' => 'mysql:host=localhost;dbname=fresh_fridge',
					'user' => 'fresh_fridge',
					'password' => 'a+cvL~.R/4lkj~44',
					'attributes' => []]]],
		'runtime' => [
			'defaultConnection' => 'fresh_fridge',
			'connections' => ['fresh_fridge']],
		'generator' => [
			'defaultConnection' => 'fresh_fridge',
			'connections' => ['fresh_fridge']]]];
