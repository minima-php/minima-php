<?php
namespace Minima\Controller;

/**
 * @property string $method
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