<?php

namespace tea;

class ActionView {
	private $_action;

	public function __construct(Action $action) {
		$this->_action = $action;
	}

	public function action() {
		return $this->_action;
	}

	public function set($name, $value) {
		$this->_action->data->$name = $value;
	}

	public function show() {
		$viewName = $this->_action->view();
		$name = $this->_action->name();
		$parent = $this->_action->parent();
		$view = (strlen($viewName) == 0) ? $name : $viewName;
		$viewFile = TEA_APP . "/views" . $parent . "/" . $view . ".php";

		if (!is_file($viewFile)) {
			return;
		}


		//注入资源


		extract((array)$this->_action->data);
		require $viewFile;
	}
}

?>