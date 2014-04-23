<?php

/**
 * Renders a partial template in View/. The view name must be underscore-prefixed
 *
 * @param string $name Like "controller/action" (will find View/controller/_action.php)
 * @param string[] $vars
 * @return string Rendered template
 */
function partial($name, $vars)
{
	list($ctrl, $action) = explode('/', $name);
	$renderer = new TemplateRenderer($ctrl, '_' . $action);
	return $renderer->render($vars);
}

/**
 * Appends a string to the base url (index.php)
 *
 * @param string $url
 * @return string
 */
function url($url = '')
{
	return BASEURL . $url;
}

/**
 * Creates a link to something
 *
 * @param string $url
 * @param string $text
 * @return string HTML link
 */
function link_to($url, $text)
{
	$url = url($url);
	return "<a href='$url'>$text</a>";
}

/**
 * @see htmlspecialchars
 */
function h($v) { return htmlspecialchars($v); }