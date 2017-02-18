<?php

namespace tea;

class Request {
	private $_params = [];

	public function __construct() {
		//合并参数
		if (isset($_GET) && is_array($_GET)) {
			$this->_params = $_GET;
		}
		if (isset($_POST) && is_array($_POST) && !empty($_POST)) {
			$this->_params = array_merge($this->_params, $_POST);
		}
	}

	public function param($param, $value = TEA_NIL) {
		if ($value !== TEA_NIL) {
			$this->_params[$param] = $value;
		}
		return $this->_params[$param] ?? null;
	}

	public function params() {
		return $this->_params;
	}
}

?>