<?php
class UserSession
{
	/** @var Model\User */
	private $user = null;

	public function __construct(PDO $db, $key = 'user_id')
	{
		if (isset($_SESSION[$key])) {
			if ($user = Model\User::find(array($key => $_SESSION[$key]))) {
				$this->user = new Model\User($user);
			}
		}
	}

	/**
	 * @return Model\User
	 */
	public function getUser()
	{
		return $this->user;
	}
} 