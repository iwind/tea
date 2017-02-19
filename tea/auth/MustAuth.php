<?php

namespace tea\auth;

use tea\Tea;

class MustAuth extends ShouldAuth {
	public static function newForParam($param) {
		session_init();

		$auth = parent::newForParam($param);
		if (!$auth->validate()) {
			$auth->onFail();

			throw new Exception("You should authenticate first");
		}
		return $auth;
	}
}

?>