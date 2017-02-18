<?php

namespace tea;

class VueActionView extends ActionView {
	public function show() {
		$data = $this->action()->data;
		$parent = $this->action()->parent();
		$viewName = $this->action()->view();

		$json = htmlspecialchars_decode(json_encode($data));
		$data->tea = (object)[
			"inject" => "<script type=\"text/javascript\">\n window.TEA = { 
	\"ACTION\": {
		\"data\":{$json}
	}	
}; \n</script>
<script type=\"text/javascript\" src=\"/js/vue.min.js\"></script>
<script type=\"text/javascript\" src=\"/js/tea-vue.js\"></script>
<script type=\"text/javascript\" src=\"/__resource__{$parent}/{$viewName}.js\"></script>
<link rel=\"stylesheet\" href='/__resource__{$parent}/{$viewName}.css'/>
"
		];

		parent::show();
	}
}

?>