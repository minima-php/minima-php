<?php
namespace Minima\Form;

/**
 * Class Base
 * @package Form
 */
abstract class Base
{
	/**
	 * @var string[] Field list
	 */
	protected $fields = array();

	/**
	 * @var array<string, array> Built-in validations
	 *
	 * Available validations :
	 *  - mandatory array<string $col>
	 *     Checks for columns presence
	 *     example : `array('username, 'password')`
	 *  - pattern array<string $col, string $pattern>
	 *     Checks that some column match a pattern
	 *     example: `array('username' => '^[A-Z][a-z]{8,15}$')`
	 *  - unique array<string $col, string $table>
	 *     Checks that the value is unique in the table
	 *     ! Requires a PDO object-like passed as `"db"` in `$variables`
	 *     example: `array('username' => 'users')`
	 */
	protected $validations = array();

	/** @var array<string, array<string>> Errors arrays by column */
	protected $errors = null;

	/** @var array Values (like $_POST) */
	protected $values;

	/** @var array Extra variables */
	protected $variables;

	/**
	 * @param array $values
	 * @param array $variables
	 */
	public function __construct($values, array $variables = array())
	{
		$this->values = $values;
		$this->variables = $variables;
	}

	/**
	 * @return bool
	 */
	public function isValid()
	{
		if (null === $this->errors) {
			$this->errors = array();
			$this->validateBuiltins();
			if (!count($this->errors))
				$this->validate();
		}
		return !count($this->errors);
	}

	/**
	 * @return array
	 */
	public function getValues()
	{
		return array_extract($this->values, $this->fields);
	}

	/**
	 * Validate built-in filters
	 * Override {@link self::validate()} to add additional validations
     *
     * @TODO this should probably not follow the `validateX` naming scheme
	 */
	private function validateBuiltins()
	{
		foreach ($this->validations as $type => $cols) {
			$this->{'validate' . ucfirst($type)}($cols);

			if (count($this->errors))
				return;
		}
	}

	/**
	 * @param $cols
	 */
	protected function validateMandatory($cols)
	{
		foreach ($cols as $col) {
			if (empty($this->values[$col]))
				$this->errors[$col][] = 'This field is mandatory';
		}
	}

    /**
     * @param $cols
     */
    protected function validateFilter($cols)
    {
        foreach ($cols as $col => $filter) {
            if (!filter_var($this->values[$col], $filter, array('flags' => FILTER_NULL_ON_FAILURE))) {
                $this->errors[$col][] = 'Invalid format';
            }
        }
    }

    /**
	 * @param $patterns
	 */
	protected function validatePattern($patterns)
	{
		foreach ($patterns as $col => $pattern) {
			if (!preg_match($pattern, $this->values[$col])) {
				$this->errors[$col][] = 'Invalid value';
			}
		}
	}

	/**
	 * @param $uniques
	 * @throws \RuntimeException
	 */
	protected function validateUnique($uniques)
	{
		if (!isset($this->variables['db']))
			throw new \RuntimeException('You need to pass in a PDO-like `db` object as variable');
		/** @var \PDO $db */
		$db = $this->variables['db'];

		foreach ($uniques as $col => $table) {
			$stmt = $db->prepare('
				SELECT *
				FROM ' . $table . '
				WHERE ' . $col . ' = ?
			');
			$stmt->execute(array($this->values[$col]));
			if ($stmt->fetch()) {
				$this->errors[$col][] = 'A record with that value already exists';
			}
		}
	}

	/**
	 * Additional validations for children classes
	 * Override if needed
	 */
	protected function validate()
	{ }

	// Helpers API
	/**
	 * Generates HTML for an input
	 * @see input
	 *
	 * @param string $name Field name
	 * @param string|null $label
	 * @param string $type
	 * @return string
	 */
	public function input($name, $label = null, $type = 'text')
	{
		if ('password' == $type) {
			$value = '';
		} else {
			$value = isset($this->values[$name]) ? h($this->values[$name]) : '';
		}
		$html = input($name, $label, $type, $value);
		if (isset($this->errors[$name])) {
			$html .= '<span class="flash-error">(' . implode(' - ', $this->errors[$name]) . ')</span>';
		}
		return $html;
	}
} 