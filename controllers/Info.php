<?php

class Info_Controller extends Template_Controller {

	public function __call($method, $args) {
		
		echo '<h1>Info</h1>';
		echo '<p>My name is Aaron.</p>';
	}
	
}