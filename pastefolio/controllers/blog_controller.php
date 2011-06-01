<?php

class blog_controller extends template_controller {

	// blog content
	// public $blog_content;

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

		foreach ($this->blog->posts(array('id' => $id)) as $post) {

			$this->template->content .= $this->_draw($post);

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
		foreach ($this->blog->posts(array('start' => $start, 'num' => $this->page_limit)) as $post) {

			$this->template->content .= $this->_draw($post);

		}
	}

	// TODO: deal with blog template and content better, this is gross
	public function _render() {

		// get site template
		$site_template = file_get_contents(realpath(TEMPLATEPATH.$this->site_template));

		// get blog template
		$blog_template = file_get_contents(realpath(TEMPLATEPATH.$this->blog_template));

		$site_template = str_replace('{{{content}}}', $blog_template, $site_template);

		$page = Page::find(array('name' => 'notes'));
		$page->nextpage_url = $this->next_url();
		$page->prevpage_url = $this->prev_url();
		$page->content = $this->template->content;


		// $this->template->content .= new Mustache($blog_template, $page);

		$this->template = new Mustache($site_template, $page);


		/*$this->template->content .= new Mustache($blog_template,
			array('blog_content' => $this->blog_content,
				  'next_url' => $this->next_url(),
				  'prev_url' => $this->prev_url(),
			));*/


		return $this->template->render();


	}

	public function archive() {

		$this->template->content = '<h1>Archive (last 50 posts)</h1>';

		// get post archive template
		$archive_template = file_get_contents(realpath(TEMPLATEPATH.$this->archive_template));

		foreach ($this->blog->posts(array('num' => 50)) as $post) {

			$post = new Mustache($archive_template, $post);
			$this->template->content .= $post;

		}

	}


}