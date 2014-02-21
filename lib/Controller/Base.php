<?php
namespace Controller;

/**
 * @property string $method
 * @property \UserSession $session
 * @property \PDO $db
 */
abstract class Base
{
	/**
	 * @param array $variables
	 */
	public function setVariables($variables)
	{
		foreach ($variables as $k => $v) {
			$this->{$k} = $v;
		}
	}

	protected function assert($cond)
	{
		if (!$cond)
			throw new Exception\NotFound();
	}

	protected function redirect($url)
	{
		header('Location: ' . BASEURL . $url);
		exit;
	}
}