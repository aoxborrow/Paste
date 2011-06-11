<?php

class blog_controller extends template_controller {

	// instance of blog driver
	public $blog;

	// current page of posts
	public $page = -1;

	// number of posts per page
	public $page_limit = 1;

	// individual blog post template
	public $blog_template = 'blog';

	// individual blog post template
	public $post_template = 'post';

	// archive listing template
	public $archive_template = 'post_archive';

	public function __construct() {

		parent::__construct();

		$name = (Pastefolio::$method == 'archive') ? 'archive' : 'notes';

		// setup page model
		$this->template->model = Content::find(array('name' => $name));

		// instantiate blog driver
		$this->blog = new Tumblr('pitchforkreviewsreviews');

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

		// cache key
		$cache_key = 'blog_post_'.$id;

		// check for cached content
		$content = Cache::instance()->get($cache_key);

		if (empty($content)) {

			foreach ($this->blog->posts(array('id' => $id)) as $post) {

				// render single blog post
				$content .= $this->_draw($post);

			}

			Cache::instance()->set($cache_key, $content);

		}

		$this->template->model->content = $content;

	}

	// TODO: deal with post_template better
	public function _draw($post) {

		return new Template($this->post_template, $post);

	}


	// draw page of blog posts
	public function page($page = 1) {

		// don't allow negative pages
		$this->page = ($page < 1) ? 1 : $page;

		// compute starting post for current page
		$start = (($this->page-1) * $this->page_limit);

		// cache key
		$cache_key = 'blog_page_'.$this->page;

		// check for cached content
		$content = Cache::instance()->get($cache_key);

		if (empty($content)) {

			// configure blog driver with start and limit
			foreach ($this->blog->posts(array('start' => $start, 'num' => $this->page_limit)) as $post) {

				// render blog posts
				$content .= $this->_draw($post);

			}

			// add content to cache
			Cache::instance()->set($cache_key, $content);

		}

		$this->template->model->content = $content;
		$this->template->model->nextpage_url = $this->next_url();
		$this->template->model->prevpage_url = $this->prev_url();

	}

	// TODO: deal with archive render better, this is gross
	public function _render() {

		$this->template->combine($this->blog_template);

		return parent::_render();

	}

	public function archive() {

		// use archive template
		$this->blog_template = 'blog_archive';

		// cache key
		$cache_key = 'blog_archive';

		// check for cached content
		$content = Cache::instance()->get($cache_key);

		if (empty($content)) {

			// fetch last 50 posts (max tumblr allows)
			foreach ($this->blog->posts(array('num' => 50)) as $post) {

				// render each post with the archive template
				$content .= new Template($this->archive_template, $post);

			}

			// add content to cache
			Cache::instance()->set($cache_key, $content);

		}

		$this->template->model->content = $content;

	}


}