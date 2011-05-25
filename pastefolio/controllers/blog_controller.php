<?php

class blog_controller extends template_controller {

	// instance of blog driver
	public $blog;

	// individual blog post template
	public $post_template = 'post.mustache';

	// archive listing template
	public $archive_template = 'post_archive.mustache';

	public function __construct() {

		parent::__construct();

		// instantiate blog driver
		$this->blog = new Tumblr('pitchforkreviewsreviews');

	}

	public function index() {

		// get post template
		$post_template = file_get_contents(realpath(TEMPLATEPATH.$this->post_template));

		// $this->blog->params(array('num' => 3));

		foreach ($this->blog->posts() as $post) {

			$this->template->content .= '<b>'.$post['id'].'</b><br/>';

			//$post = new Mustache($post_template, $post);
			//$this->template->content .= $post->render();
			$this->template->content .= new Mustache($post_template, $post);
			//$this->template->content .= print_r($post, TRUE);

			$this->template->content .= '<br/><br/>';
		}

		/*
		$this->template->content .= '<iframe src="http://assets.tumblr.com/iframe.html?9&amp;src=http%3A%2F%2Fjustgoodtats.com%2F&amp;lang=en_US&amp;name=justgoodtattoos&amp;brag=0" scrolling="no" width="330" height="25" frameborder="0" style="position:absolute; z-index:1337; top:0px; right:0px; border:0px; background-color:transparent; overflow:hidden;" id="tumblr_controls"></iframe>';
		$this->template->content .= '<script type="text/javascript" src="http://blog.cremesyndicate.com/js"></script><h1>Blog Controller</h1>';
		*/

	}

	public function page($num = 1) {

		$this->template->content = 'Blog Page #'.$num;

	}

	public function archive() {

		$this->template->content = '<h1>Archive (last 50 posts)</h1>';

		// get post archive template
		$archive_template = file_get_contents(realpath(TEMPLATEPATH.$this->archive_template));

		$this->blog->params(array('num' => 50));

		foreach ($this->blog->posts() as $post) {

			$post = new Mustache($archive_template, $post);
			$this->template->content .= $post;

		}

	}


}