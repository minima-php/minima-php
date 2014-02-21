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
 */
function link_to($url, $text)
{
	$url = url($url);
	return "<a href='$url'>$text</a>";
}

/**
 * Creates an HTML Input
 *
 * @var string $name input name
 * @var string|null $label Label content
 * @var string $type Input type
 * @var string $value Input value
 * @return string Input's HTML
 */
function input($name, $label = null, $type = 'text', $value = '')
{
	if (null === $label)
		$label = ucfirst($name);

	return "
<label for='$name'>$label</label>
<input type='$type' name='$name' id='$name' value='" . $value . "' />
	";
}

/**
 * @see htmlspecialchars
 */
function h($v) { return htmlspecialchars($v); }