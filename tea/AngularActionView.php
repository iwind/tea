<?php

namespace tea;

class AngularActionView extends ActionView {
	public function show() {
		$data = $this->action()->data;
		$module = $this->action()->module();
		$parent = $this->action()->parent();
		$viewName = $this->action()->view();
		$base = TEA_URL_BASE;
		$dispatcher = TEA_URL_DISPATCHER;
		$actionParam = TEA_ENABLE_ACTION_PARAM ? "true" : "false";

		$json = htmlspecialchars_decode(json_encode($data));
		$data->tea = (object)[
			"inject" => "<script type=\"text/javascript\">\n window.TEA = { 
	\"ACTION\": {
		\"data\":{$json},
		\"base\":\"{$base}{$dispatcher}\",
		\"module\":\"{$module}\",
		\"parent\":\"{$parent}\",
		\"actionParam\": {$actionParam}
	}	
}; \n</script>
<script type=\"text/javascript\" src=\"" . TEA_URL_BASE . "/js/angular.min.js\"></script>
<script type=\"text/javascript\" src=\"" . TEA_URL_BASE . "/js/tea-angular.js\"></script>
<script type=\"text/javascript\" src=\"" . u("__resource__{$parent}") . "/{$viewName}.js\"></script>
<link type=\"text/css\" rel=\"stylesheet\" href=\"" . u("__resource__{$parent}") . "/{$viewName}.css\" media=\"all\"/>
"
		];

		parent::show();
	}
}

?>