<?php

if (!defined('JSON_PRETTY_PRINT'))
{
	define('JSON_PRETTY_PRINT', null);
}

class ChargeOverAPI_Object
{
	const TYPE_CUSTOMER = 'customer';
	const TYPE_BILLINGPACKAGE = 'billing_package';
	const TYPE_USER = 'user';
	const TYPE_ITEM = 'item';
	const TYPE_INVOICE = 'invoice';
	const TYPE_CREDITCARD = 'creditcard';
	const TYPE_TRANSACTION = 'transaction';

	protected $_arr;
	
	public function __construct($arr = array())
	{
		$this->_arr = $arr;
	}
	
	/**
	 * 
	 */
	public static function transformMethodToField($method)
	{
		$strip = array(
			'set', 
			'get', 
			'add', 
			);

		foreach ($strip as $prefix)
		{
			if (substr($method, 0, strlen($prefix)) == $prefix)
			{
				$method = substr($method, strlen($prefix));
				break;
			}
		}

		$last = 0;

		$parts = array();
		$len = strlen($method);
		for ($i = 0; $i < $len; $i++)
		{
			if ($method{$i} >= 'A' and $method{$i} <= 'Z')
			{
				$parts[] = substr($method, $last, $i);
				$last = $i;
			}
		}

		$parts[] = substr($method, $last);

		return strtolower(trim(implode('_', $parts), '_'));
	}

	public static function transformFieldToMethod($field, $prefix = 'set')
	{
		$last = 0;

		$parts = array();
		$len = strlen($field);
		for ($i = 0; $i < $len; $i++)
		{
			if ($field{$i} == '_')
			{
				$parts[] = ucfirst(substr($field, $last, $i));
				$i++;
				$last = $i;				
			}
		}

		$parts[] = ucfirst(substr($field, $last));

		return $prefix . implode('', $parts);
	}

	public function __call($name, $args)
	{
		if (substr($name, 0, 3) == 'set')
		{
			$field = ChargeOverAPI_Object::transformMethodToField($name);
			$this->_arr[$field] = current($args);
			return true;
		}
		else if (substr($name, 0, 3) == 'get')
		{
			$field = ChargeOverAPI_Object::transformMethodToField($name);
			if (array_key_exists($field, $this->_arr))
			{
				return $this->_arr[$field];
			}

			return null;
		}
		else if (substr($name, 0, 3) == 'add')
		{
			$field = ChargeOverAPI_Object::transformMethodToField($name);

			if (!isset($this->_arr[$field]))
			{
				$this->_arr[$field] = array();
			}

			$Obj = current($args);
			$this->_arr[$field][] = $Obj;
		}
	}

	protected function _massage($val)
	{
		if (is_object($val))
		{
			$val = $val->toArray();
		}
		else if (is_array($val))
		{
			foreach ($val as $key => $value)
			{
				$val[$key] = $this->_massage($value);
			}
		}
	
		return $val;
	}

	protected function toJSON()
	{
		$arr = $this->_massage($this->_arr);
		
		return json_encode($arr, JSON_PRETTY_PRINT);
	}

	public function toArray()
	{
		return $this->_massage($this->_arr);
	}

	public function __toString()
	{
		return $this->toJSON();
	}
}