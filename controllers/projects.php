<?php

class projects_controller extends template_controller {

	public function __call($name, $args) {

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

	public function all() {

		$output = '<h1>All Work</h1>';

		foreach (Content::list_dir() as $name) {
			$output .= Project::factory($name)->render();
		}

		$this->template->content = $output;

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