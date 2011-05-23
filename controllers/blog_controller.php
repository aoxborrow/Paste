<?php

class blog_controller extends template_controller {

	public function index() {

		return $this->page();

	}

	public function page() {

		/*<iframe src="http://assets.tumblr.com/iframe.html?9&amp;src=http%3A%2F%2Fjustgoodtats.com%2Fpost%2F4627183324%2Fjim-sylvia&amp;pid=4627183324&amp;rk=MMsfjC2m&amp;lang=en_US&amp;name=justgoodtattoos&amp;brag=0" scrolling="no" width="330" height="25" frameborder="0" style="position:absolute; z-index:1337; top:0px; right:0px; border:0px; background-color:transparent; overflow:hidden;" id="tumblr_controls"></iframe>*/

		$this->template->content .= '<iframe src="http://assets.tumblr.com/iframe.html?9&amp;src=http%3A%2F%2Fjustgoodtats.com%2F&amp;lang=en_US&amp;name=justgoodtattoos&amp;brag=0" scrolling="no" width="330" height="25" frameborder="0" style="position:absolute; z-index:1337; top:0px; right:0px; border:0px; background-color:transparent; overflow:hidden;" id="tumblr_controls"></iframe>';
		$this->template->content .= '<script type="text/javascript" src="http://blog.cremesyndicate.com/js?num=1"></script><h1>Blog Controller</h1>';

	}

	public function archive() {

		// mustache not really needed for these static pages
		$this->template->content = '<h1>Archive</h1>';

	}


}