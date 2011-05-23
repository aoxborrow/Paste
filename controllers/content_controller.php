<?php

// default content controller
class content_controller extends template_controller {


	public function __call($method, $args) {

		// decipher content request
		$request = empty(Pastefolio::$current_uri) ? array('index') : explode('/', Pastefolio::$current_uri);

		// single level, section is root and page is request
		if (count($request) == 1) {

			$this->current_section = NULL;
			$this->current_page = $request[0];

		// set section and page
		} elseif (count($request) == 2) {

			$this->current_section = $request[0];
			$this->current_page = $request[1];

		// multiple levels deep
		} else {

			echo "I don't know how to handle this request:";
			die(print_r($request));

		}

		// TODO: check if index page has content, show it, otherwise redirect to first page
		//Router::redirect('/'.$section.'/index');
		//$this->template->content .= Page::factory($section);

		// ghetto breadcrumb
		$this->template->content = '<p><b>'.(($this->current_section !== NULL) ? $this->current_section.' / ' : '').$this->current_page.'</b></p>';

		$page = Page::find(array('name' => $this->current_page));

		if ($page === FALSE) {

			// trigger 404 message
			return $this->error_404();

		} else {

			$this->template->title = $page->title.' - '.$this->template->title;
			$this->template->content .= $page;

		}

	}

	public function all($section) {

		$output = '<h1>All Pages of <b>'.$section.'</b></h1>';

/*
		foreach (Content::load_section($section) as $name) {
			$output .= $name.'<br/>';
		}*/

		$this->template->content = $output;

	}

	public function projects__call($name, $args) {

		// no project name set
		if (empty($name) OR $name == 'index') {

			// get first project name
			$first = Menu::first_project();

			// redirect to first project
			Router::redirect('/projects/'.$first);

		}

		// set current image, or default to 1
		$current_image = (! empty($args[0])) ? $args[0] : 1;

		// instantiate project model
		$project = Project::factory($name, $current_image);

		// ensure valid project name and image
		if ($project !== FALSE) {

			// render project in template
			$this->template->content = $project->render();

		} else {

			// trigger 404 message
			return $this->error_404();

		}
	}


	public function convert() {

		foreach (Content::list_dir() as $name) {
			$project = Content::load($name);
			$project->name = $name;
			//$project['description'] = stripslashes($project['description']);
			//$json = self::json_readable_encode($project);
			//echo $json."\n";
			//file_put_contents(APPPATH.'views/newwork/menu.yaml', $yaml);

			$html = self::convert2html($project);
			file_put_contents(APPPATH.'views/projects_html/'.$name.'.html', $html);

		}

		exit;

	}

	public static function convert2html($p) {

		$html = '';

		for ($i = 1; $i <= $p->numpics; $i++) {

			$html .= '<img src="/assets/images/projects/'.$p->name.$i.$p->extension.'" alt="This is the caption for image '.$i.'">'."\n";
		}
		$html .= "\n";
		$html .= '<h1>'.$p->title."</h1>\n";
		$html .= '<h2>'.$p->subtitle."</h2>\n";
		$html .= str_replace('</p>', "</p>\n\n", $p->description);

		return $html;


	}

}