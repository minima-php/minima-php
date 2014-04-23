<?php
namespace Minima;

class TemplateRenderer
{
	const VIEW_DIR = 'app/View/';

	private $ctrl = '';

	private $action = '';

	static private $helpersInit = false;

	public function __construct($ctrl, $action)
	{
		$this->initHelpers();
		$this->ctrl = $ctrl;
		$this->action = $action;
	}

	public function render(array $params)
	{
		ob_start();
		extract($params);
		include self::VIEW_DIR . $this->ctrl . '/' . $this->action . '.php';
		return ob_get_clean();
	}

	public function renderWithLayout(array $params)
	{
		global $data;
		$data = $this->render($params);
		extract($params);
		include self::VIEW_DIR . 'layout.php';
	}

	private function initHelpers()
	{
		if (self::$helpersInit)
			return;
		self::$helpersInit = true;

		foreach (array('lib', 'app') as $prefix) {
			foreach (glob($prefix . '/Helper/*.php') as $helper) {
				include $helper;
			}
		}
	}
}