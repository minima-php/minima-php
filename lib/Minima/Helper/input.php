<?php
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

    if (function_exists('input_' . $type))
        $input_html = call_user_func('input_' . $type, $name, $value);
    else
        $input_html = "<input type='$type' name='$name' id='$name' value='" . $value . "' />";

    return "
<label for='$name'>$label</label>
$input_html
";
}

function input_textarea($name, $value = '')
{
    return "
<textarea name='$name'>$value</textarea>
";
}