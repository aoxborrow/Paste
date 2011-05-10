<?php

class Work_Controller extends Template_Controller {
	
	public function index() {
		
		// get first project name
		$first = Menu::first_project();
		
		// redirect to first project
		self::redirect('/work/'.$first);
	
	}

	
	public function show($name, $current_image = 1) {
		
		$this->template->current_page = $name;
		
		$this->template->content = HTMLProject::factory($name, $current_image)->render();

	}
	
	public function all() {
		
		$output = '<h1>All Work</h1>';
		
		foreach (Storage::list_dir() as $name) {
			$output .= Project::factory($name)->render();
		}		

		$this->template->content = $output;		
		
	}
		
	public function convert() {
				
		foreach (Storage::list_dir() as $name) {
			$project = Storage::load($name);
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
		
		/*
		<img src="/assets/images/projects/ifloorplan1.gif" alt="This is the caption for image 1.">
		<img src="/assets/images/projects/ifloorplan2.gif" alt="This is the caption for image 2.">
		<img src="/assets/images/projects/ifloorplan3.gif" alt="This is the caption for image 3.">

		<h1>iFloorPlan</h1>
		<h2>Flash/PHP/SQL Development</h2>
		<p>Interactive floorplans allow new home buyers to customize their home before it is built. iFloorPlan lets buyers visualize and manipulate structural options, electrical wiring and furniture placement. Home builders may then use these personalized floorplans as a starting guide for the construction process.</p>

		<p>iFloorPlan has evolved through years of development into quite a sophisticated and flexible application, and is currently in use by dozens of the nation''s top builders. The latest version (4.0) introduces a completely new administration and production backend.</p>

		<p class="launch"><a title="View Demo" href="http://ifloorplan.com/ifp/plan.php?plan=1:2400OH" rel="external">VIEW DEMO</a></p>
		<p class="launch"><a title="Visit Ifloorplan.com" href="http://ifloorplan.com" rel="external">VISIT iFLOORPLAN.COM</a></p>
		*/	
		
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
	
	public static function json_readable_encode($in, $indent = 0, $from_array = false)
	{
		$_myself = __METHOD__;
		$_escape = function ($str)
		{
			return preg_replace("!([\b\t\n\r\f\"\\'])!", "\\\\\\1", $str);
		};

		$out = '';

		foreach ($in as $key=>$value)
		{
			$out .= str_repeat("\t", $indent + 1);
			$out .= "\"".$_escape((string)$key)."\": ";

			if (is_object($value) || is_array($value))
			{
				$out .= "\n";
				$out .= self::$_myself($value, $indent + 1);
			}
			elseif (is_bool($value))
			{
				$out .= $value ? 'true' : 'false';
			}
			elseif (is_null($value))
			{
				$out .= 'null';
			}
			elseif (is_string($value))
			{
				$out .= "\"" . $_escape($value) ."\"";
			}
			else
			{
				$out .= $value;
			}

			$out .= ",\n";
		}

		if (!empty($out))
		{
			$out = substr($out, 0, -2);
		}

		$out = str_repeat("\t", $indent) . "{\n" . $out;
		$out .= "\n" . str_repeat("\t", $indent) . "}";

		return $out;
	}
	
}