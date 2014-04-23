<?php
namespace Form;

class Register extends Base
{
	protected $fields = array('username', 'password', );

	protected $validations = array(
		'mandatory' => array(
			'username', 'password',
		),
		'pattern' => array(
			'username' => '/^[A-Z][a-z_-]{7,15}$/',
			'password' => '/^.{8,16}$/',
		),
		'unique' => array(
			'username' => 'users',
		),
	);
} 