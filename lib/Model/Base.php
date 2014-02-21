<?php
// This is PotterORM. <3 Aegis.
namespace Model;

/**
 * Class Base
 * @package Model
 */
class Base implements \ArrayAccess
{
	/** @var string Table name */
	static protected $table = '';

	/** @var string Primary Key */
	static protected $pk = '';

	/** @var string[] Field names */
	static protected $fields = array();

	/** @var \PDO */
	static private $db;

	/** @var string[] Values */
	protected $values;

	/**
	 * @param $values
	 */
	public function __construct($values)
	{
		$this->values = $values;
	}

	/**
	 * @return bool
	 */
	public function exists()
	{
		return !empty($this->values[static::$pk]);
	}

	/**
	 * @param array $values
	 */
	public function setValues(array $values)
	{
		foreach (static::$fields as $field) {
			if (isset($values[$field])) {
				$this->values[$field] = $values[$field];
			}
		}
	}

	/**
	 * @return bool
	 */
	public function save()
	{
		$sql = $this->{'get' . ($this->exists() ? 'Update' : 'Insert') . 'Sql'}();

		$stmt = self::$db->prepare($sql);
		$bool = $stmt->execute($this->exists() ? $this->values : $this->getValuesWithoutPk());
		if (!$this->exists())
			$this->values[static::$pk] = self::$db->lastInsertId();
		return $bool;
	}

	public function getValues()
	{
		return $this->values;
	}

	/**
	 * @return array
	 */
	private function getValuesWithoutPk()
	{
		$cols = array();
		foreach (static::$fields as $field) {
			if ($field != static::$pk)
				$cols[$field] = $this->values[$field];
		}
		return $cols;
	}

	/**
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function offsetSet($key, $value)
	{
		if (method_exists($this, $m = 'get' . ucfirst($key)))
			return $this->$m($value);
		$this->values[$key] = $value;
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->values[$offset]);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->values[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return null|string
	 */
	public function offsetGet($offset)
	{
		if (method_exists($this, $m = 'get' . ucfirst($offset)))
			return $this->$m();
		return isset($this->values[$offset]) ? $this->values[$offset] : null;
	}

	/**
	 * @return string
	 */
	public function getPk()
	{
		return $this->values[static::$pk];
	}

	/**
	 * @return string
	 */
	private function getInsertSql()
	{
		return '
			INSERT INTO ' . static::$table . '
			(' . implode(', ', static::$fields) . ')
			VALUES (' . implode(', ', array_map(function ($col)
			{ return ':' . $col; }, static::$fields)) . ')
		';
	}

	/**
	 * @return string
	 */
	private function getUpdateSql()
	{
		return '
			UPDATE ' . static::$table . '
			SET ' . implode(', ', array_map(function ($col)
			{ return $col . ' = :' . $col; }, static::$fields)) . '
			WHERE ' . static::$pk . ' = :' . static::$pk . '
		';
	}

	/**
	 * @param \PDO $db
	 */
	static public function setDb(\PDO $db)
	{
		self::$db = $db;
	}

	/**
	 * @param string $field
	 */
	static public function hasField($field)
	{
		return in_array($field, static::$fields);
	}

	/**
	 * Finds ONE record by criterions and returns a new instance or null if nothing found
	 *
	 * @param array $where Where clauses (optional)
	 * @param array $opts Limit & Order (optional)
	 * @return static
	 */
	static public function find($where = array(), $opts = array())
	{
		if (!is_array($where))
			$where = array(static::$pk => $where);
		$record = self::prepareFind($where, $opts)->fetch(\PDO::FETCH_ASSOC);
		if ($record)
			return new static($record);
	}

	/**
	 * @param array $where Where clauses (optional)
	 * @param array $opts Limit & Order (optional)
	 * @return static[]
	 */
	static public function findAll($where = array(), $opts = array())
	{
		$records = array();
		foreach (self::prepareFind($where, $opts)->fetchAll(\PDO::FETCH_ASSOC) as $record)
			$records[$record[static::$pk]] = new static($record);
		return $records;
	}

	/**
	 * @param $where
	 * @return \PDOStatement
	 */
	static private function prepareFind($where, $opts = array())
	{
		$sql = '
			SELECT *
			FROM ' . static::$table .
			($where ? '
			WHERE ' . implode(' AND ', array_map(function ($col)
			{ return $col . ' = :' . $col; }, array_keys($where))) . '
		' : '');
		if (!empty($opts['order'])) {
			$sql .= '
				ORDER BY ' . $opts['order'];
		}
		if (!empty($opts['limit'])) {
			$sql .= '
				LIMIT ' . $opts['limit'];
		}
		$stmt = self::$db->prepare($sql);
		$stmt->execute($where);
		return $stmt;
	}
}