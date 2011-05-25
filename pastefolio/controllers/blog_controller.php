<?php

class blog_controller extends template_controller {

	// blog content
	public $blog_content;

	// instance of blog driver
	public $blog;

	// current page of posts
	public $page = -1;

	// number of posts per page
	public $page_limit = 1;

	// individual blog post template
	public $blog_template = 'blog.mustache';

	// individual blog post template
	public $post_template = 'post.mustache';

	// archive listing template
	public $archive_template = 'post_archive.mustache';

	public function __construct() {

		parent::__construct();

		// instantiate blog driver
		$this->blog = new Tumblr('pitchforkreviewsreviews');

		// get post template
		$this->post_template = file_get_contents(realpath(TEMPLATEPATH.$this->post_template));


	}

	public function index() {

		return $this->page();

	}

	public function next_url() {

		if ($this->page > 0) {
			return '/notes/page/'.($this->page + 1);
		}

		return FALSE;

	}

	public function prev_url() {

		if ($this->page > 1) {
			return '/notes/page/'.($this->page - 1);
		}

		return FALSE;

	}

	public function post($id) {

		// configure blog driver with start and limit
		$this->blog->params(array('id' => $id));

		foreach ($this->blog->posts() as $post) {

			$this->blog_content .= $this->_draw($post);

		}


	}

	// TODO: deal with post_template better
	public function _draw($post) {

		$p = '<br clear="all"><p><b><a href="/notes/'.$post['id'].'">#'.$post['id'].'</a></b></p>';

		//$post = new Mustache($post_template, $post);
		//$this->template->content .= $post->render();
		$p .= new Mustache($this->post_template, $post);
		//$this->template->content .= print_r($post, TRUE);

		$p .= '<br/><br/>';

		return $p;


	}


	// TODO: have blog page template
	// draw page of blog posts
	public function page($page = 1) {

		// don't allow negative pages
		$this->page = ($page < 1) ? 1 : $page;

		// compute starting post for current page
		$start = (($this->page-1) * $this->page_limit);

		// configure blog driver with start and limit
		$this->blog->params(array('start' => $start, 'num' => $this->page_limit));

		foreach ($this->blog->posts() as $post) {

			$this->blog_content .= $this->_draw($post);

		}
	}

	// TODO: deal with blog template and content better, this is gross
	public function _render() {

		// get blog template
		$blog_template = file_get_contents(realpath(TEMPLATEPATH.$this->blog_template));

		$this->template->content .= new Mustache($blog_template,
			array('blog_content' => $this->blog_content,
				  'next_url' => $this->next_url(),
				  'prev_url' => $this->prev_url(),
			));


		return $this->template->render();


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