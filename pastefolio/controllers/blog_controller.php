<?php

class blog_controller extends template_controller {

	// instance of blog driver
	public $blog;

	// current page of posts
	public $blog_page = -1;

	// number of posts per page
	public $page_limit = 4;

	// individual blog post template
	public $blog_post_partial = 'blog_post';

	// archive listing template
	public $blog_post_archive_partial = 'blog_post_archive';

	public function __construct() {

		parent::__construct();

		// instantiate blog driver
		$this->blog = new Tumblr('pitchforkreviewsreviews');

	}

	public function index() {

		return $this->page();

	}

	public function next_page() {

		if ($this->blog_page > 0) {
			return '/notes/page/'.($this->blog_page + 1);
		}

		return FALSE;

	}

	public function prev_page() {

		if ($this->blog_page > 1) {
			return '/notes/page/'.($this->blog_page - 1);
		}

		return FALSE;

	}

	public function post($id) {

		// hack to get menu highlighted
		Pastefolio::$current_uri = 'notes';

		// load content page
		$this->page = Content::find(array('name' => 'post', 'section' => 'notes'));

		// get post based on id
		$post = $this->blog->post(array('id' => $id));

		// set page title from post
		$this->page->title = $post['title'];

		// render single blog post
		$this->page->content = Template::factory($this->blog_post_partial)->render($post);

	}

	// draw page of blog posts
	public function page($page = 1) {

		// hack to get menu highlighted
		Pastefolio::$current_uri = 'notes';

		// load content page
		$this->page = Content::find(array('name' => 'notes'));

		// don't allow negative pages
		$this->blog_page = ($page < 1) ? 1 : $page;

		// compute starting post for current page
		$start = (($this->blog_page-1) * $this->page_limit);

		// configure blog driver with start and limit
		foreach ($this->blog->posts(array('start' => $start, 'num' => $this->page_limit)) as $post) {

			// render blog posts
			$this->page->content .= Template::factory($this->blog_post_partial)->render($post);

		}

		// set page title
		$this->page->title = $this->page->title.', Page '.$this->blog_page;

		// setup prev/next links
		$this->page->next_page = $this->next_page();
		$this->page->prev_page = $this->prev_page();

	}

	public function archive() {

		// load content page
		$this->page = Content::find(array('name' => 'archive', 'section' => 'notes'));

		// fetch last 50 posts (max tumblr allows)
		foreach ($this->blog->posts(array('num' => 50)) as $post) {

			// render each post with the archive template
			$this->page->content .= Template::factory($this->blog_post_archive_partial)->render($post);

		}


	}


}