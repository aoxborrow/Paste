<?php

class Info_Controller extends Template_Controller {

	public function __call($method, $args) {
		
		$this->template->content = '<h1>Info</h1>';
		$this->template->content .= '<p>My name is Aaron.</p>';
	}
	
}