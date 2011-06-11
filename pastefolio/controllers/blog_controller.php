<?php

class blog_controller extends template_controller {

	// instance of blog driver
	public $blog;

	// current page of posts
	public $page = -1;

	// number of posts per page
	public $page_limit = 4;

	// individual blog post template
	public $blog_page_template = 'blog_page';

	// individual blog post template
	public $blog_post_template = 'blog_post';

	// archive listing template
	public $blog_archive_template = 'blog_archive';

	// archive listing template
	public $blog_post_archive_template = 'blog_post_archive';

	public function __construct() {

		parent::__construct();

		// instantiate blog driver
		$this->blog = new Tumblr('pitchforkreviewsreviews');

	}

	public function index() {

		return $this->page();

	}

	public function next_page() {

		if ($this->page > 0) {
			return '/notes/page/'.($this->page + 1);
		}

		return FALSE;

	}

	public function prev_page() {

		if ($this->page > 1) {
			return '/notes/page/'.($this->page - 1);
		}

		return FALSE;

	}

	public function post($id) {

		// hack to get menu highlighted
		Pastefolio::$current_uri = 'notes';

		// blog page template
		$this->template->partial($this->blog_page_template);
		$this->template->page = Content::find(array('name' => 'notes'));

		// get post based on id
		$post = $this->blog->post(array('id' => $id));

		// render single blog post
		$this->template->page->content = $this->_draw_post($post);

	}

	// TODO: deal with post_template better
	public function _draw_post($post) {

		return new Template($this->blog_post_template, $post);

	}


	// draw page of blog posts
	public function page($page = 1) {

		// hack to get menu highlighted
		Pastefolio::$current_uri = 'notes';

		// blog page template
		$this->template->partial($this->blog_page_template);
		$this->template->page = Content::find(array('name' => 'notes'));

		// don't allow negative pages
		$this->page = ($page < 1) ? 1 : $page;

		// compute starting post for current page
		$start = (($this->page-1) * $this->page_limit);

		// configure blog driver with start and limit
		foreach ($this->blog->posts(array('start' => $start, 'num' => $this->page_limit)) as $post) {

			// render blog posts
			$this->template->page->content .= $this->_draw_post($post);

		}

		$this->template->page->next_page = $this->next_page();
		$this->template->page->prev_page = $this->prev_page();


	}

	public function archive() {

		$this->template->partial($this->blog_archive_template);
		$this->template->page = Content::find(array('name' => 'archive', 'section' => 'notes'));

		// fetch last 50 posts (max tumblr allows)
		foreach ($this->blog->posts(array('num' => 50)) as $post) {

			// render each post with the archive template
			$this->template->page->content .= new Template($this->blog_post_archive_template, $post);

		}


	}


}