<?php

class Work_Controller extends Template_Controller {
	
	public function index() {
		
		// get first project name
		$first = Menu::first_project();
		
		// redirect to first project
		self::redirect('/work/'.$first);
	
	}

	
	public function show($name, $page = 1) {
		
		$this->template->current_page = $name;
		
		$project = Project::factory($name);
		$project->page = $page;
				
		$this->template->content = $project->render();	

	}
	
	public function _all_index() {
		
		$output = '<h1>All Work</h1>';
			
		foreach (Project::all_projects() as $project) {
			$output .= $project->render();
		}

	    $this->template->content = $output;		
	    
	}
		
	public function convert() {
		
		/*
		---
		work_id: ifloorplan
		category: 0
		visible: 1
		index: 2
		menuname: iFloorPlan
		title: iFloorPlan
		launchtext: VISIT iFLOORPLAN.COM
		launchurl: http://ifloorplan.com
		description: |
		  <p>Interactive floorplans allow new home buyers to customize their home before it is built. iFloorPlan lets buyers visualize and manipulate structural options, electrical wiring and furniture placement. Home builders may then use these personalized floorplans as a starting guide for the construction process.</p>
		  <p>iFloorPlan has evolved through years of development into quite a sophisticated and flexible application, and is currently in use by dozens of the nation's top builders. The latest version (4.0) introduces a completely new administration and production backend.</p>

		  <p class="launch"><a title="View Demo" href="http://ifloorplan.com/ifp/plan.php?plan=1:2400OH" rel="external">VIEW DEMO</a></p>
		numpics: 3
		subtitle: Flash/PHP/SQL Development
		extension: .gif
		color: 004D90
		*/
		
		
		// launchtext: VISIT iFLOORPLAN.COM
		// launchurl: http://ifloorplan.com
		// <p class="launch"><a title="View Demo" href="http://ifloorplan.com/ifp/plan.php?plan=1:2400OH" rel="external">VIEW DEMO</a></p>
	  
		$output = '<h1>Converting</h1>';
		
		$works = array();
	
		if ($handle = opendir(APPPATH.'views/work')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' AND $file != '..') { 
				 	$works[] = sfYaml::load(APPPATH.'views/work/'.$file);
				}
			}
			closedir($handle);			
		}
		
		echo '<br/><pre>';
		
		$menu = array("Applications" => array(), "Websites" => array(), "Design" => array());
		$categories = array("Applications", "Websites", "Design");	  	

	    foreach ($works as $work) {

			$cat = $categories[$work['category']];
			
			$menu[$cat][$work['work_id']] = $work['menuname'];
						
	    }
	
		print_r($menu);
		
		$yaml = sfYaml::dump($menu, 2);
		file_put_contents(APPPATH.'views/newwork/menu.yaml', $yaml);	

	    echo '</pre>';

		
	}
	
}