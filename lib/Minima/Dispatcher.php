<?php
namespace Minima;

class Dispatcher
{
	private $routes = array();

	private $variables = array();

	private $url = '';

	/**
	 * @param array $routes Routes
	 */
	public function __construct(array $routes)
	{
		$this->routes = $routes;

		$url = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
		list($url) = explode('?', $url);
		$this->url = $url ? ltrim($url, '/') : '';
	}

	/**
	 * Dispatchs the request
	 */
	public function dispatch()
	{
		$this->variables = array_merge($this->variables, array(
			'method' => $_SERVER['REQUEST_METHOD']
		));

		$code = 404;
		foreach ($this->routes as $path => $action) {
			if (is_array($params = $this->routeMatch($path))) {
				list($ctrl, $action) = explode('#', $action);
				$ctrl_class = 'Controller\\' . $ctrl;
				/** @var Controller\Base $ctrl_inst */
				$ctrl_inst = new $ctrl_class();
				$ctrl_inst->setVariables($this->variables);
				try {
					if (method_exists($ctrl_inst, $action . 'Action')) {
						$template_params = $ctrl_inst->{$action . 'Action'}($params);
					} else {
						$template_params = array();
					}
					$renderer = new TemplateRenderer($ctrl, $action);
					$renderer->renderWithLayout(array_merge(
						$this->variables,
						$template_params ?: array()
					));
					return;
				} catch (Controller\Exception\Base $e) {
					break;
				} catch (\Exception $e) {
					file_put_contents('res/log/error.log', $e->getMessage());
					$code = 500;
					break;
				}
			}
		}

		$renderer = new TemplateRenderer('Error', $code);
		$renderer->renderWithLayout($this->variables);
	}

	/**
	 * @param $path string Route to match against
	 * @return array Variables
	 */
	private function routeMatch($path)
	{
		$vars = array();
		if ($this->url == $path)
			return $vars;

		$url_parts = explode('/', $this->url);
		$path_parts = explode('/', $path);
		if (($c = count($url_parts)) != count($path_parts))
			return null;

		for ($i = 0; $i < $c; ++$i) {
			if ($path_parts[$i] && $path_parts[$i][0] == ':') {
				$vars[substr($path_parts[$i], 1)] = $url_parts[$i];
			} else if ($path_parts[$i] != $url_parts[$i])
				return null;
		}

		return $vars;
	}

	/**
	 * @param array $variables
	 */
	public function setVariables($variables)
	{
		$this->variables = $variables;
	}

}