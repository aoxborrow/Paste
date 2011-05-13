<?php

// draw content sections
class sections_controller extends template_controller {

	public function __call($section, $args) {

		return $this->all($section);

	}

	public function all($section) {

		$output = '<h1>All Pages of Section '.$section.'</h1>';

		foreach (Storage::load_section($section) as $name) {
			$output .= $name.'<br/>';
		}

		$this->template->content = $output;

	}

}