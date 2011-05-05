<?php

class Index_Controller extends Template_Controller {

	public function index() {
		
		$output = '<h1>Hello!</h1>';
		$output .= '<a href="/work">Work</a><br/>';
		$output .= '<a href="/info">Info</a>';
		
		$this->template->content = $output;
		
	}
	
}