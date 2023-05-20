<?php
	
	class TemplateHandler {
		private $template;

		public function __construct($templatePath) {
			$this->template = "";
			if (file_exists($templatePath)) {
				$this->template = file_get_contents($templatePath);
			}
		}

		public function prepare($values) {
			$template = $this->template;
			foreach ($values as $value) {
				$template = str_replace($value["key"], $value["value"], $template);
			}
			return $template;
		}
	}

?>