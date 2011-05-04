<?php

class Index_Controller extends Template_Controller {

	public function index() {
		
		echo '<h1>Hello!</h1>';
		echo '<a href="/work">Work</a><br/>';
		echo '<a href="/info">Info</a>';
		
	}
	
}