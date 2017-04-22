<?php

namespace tea;

class Must {
	private static $_instance;
	private $_shouldThrow = false;

	public static function new () {
		return self::shared();
	}

	public static function shared () {
		if (self::$_instance == null) {
			self::$_instance = new static;
		}
		return self::$_instance;
	}

	private function __construct() {
		$this->_shouldThrow = (get_called_class() == self::class);
	}

	public function int(&$var, $min = null, $max = null) {
		$var = intval($var);

		if (is_int($min) && $var < $min) {
			$var = $min;
			$this->_throw();
		}

		if (is_int($max) && $var > $max) {
			$var = $max;
			$this->_throw();
		}

		return $this;
	}

	public function float(&$var, $min = null, $max = null) {
		$var = floatval($var);

		if (is_float($min) && $var < $min) {
			$var = $min;
			$this->_throw();
		}

		if (is_float($max) && $var > $max) {
			$var = $max;
			$this->_throw();
		}

		return $this;
	}

	public function double(&$var, $min = null, $max = null) {
		$var = doubleval($var);

		if (is_double($min) && $var < $min) {
			$var = $min;
			$this->_throw();
		}

		if (is_double($max) && $var > $max) {
			$var = $max;
			$this->_throw();
		}

		return $this;
	}

	public function bool(&$var) {
		$var = boolval($var);
		return $this;
	}

	public function string(&$var, $default = "") {
		$var = strval($var);
		if (strlen($var) == 0) {
			$var = $default;
		}
		return $this;
	}

	public function array(&$var) {
		if (!is_array($var)) {
			$var = [];
			$this->_throw();
		}
		return $this;
	}

	public function pieces(&$var) {
		$var = strval($var);
		if (is_empty($var)) {
			$var = [];
			return $this;
		}

		$var = preg_split("/\\s*,\\s*/", $var);

		return $this;
	}

	public function ids(&$var) {
		if (!is_array($var)) {
			$var = strval($var);
			if (is_empty($var)) {
				$var = [];
				return $this;
			}

			$var = preg_split("/\\s*,\\s*/", $var);
		}

		$var = array_values(array_unique(
			array_filter(array_map("intval", $var), function ($value) {
				return $value > 0;
			})
		));

		return $this;
	}

	private function _throw() {
		if ($this->_shouldThrow) {
			throw new Exception("found a wrong param value");
		}
	}
}

?>