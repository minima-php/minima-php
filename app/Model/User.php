<?php
namespace Model;

class User extends Base
{
	static protected $table = 'users';
	static protected $pk = 'user_id';
	static protected $fields = array('username', 'password', );
}