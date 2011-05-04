<?php

class Work_Controller extends Template_Controller {
	
	public function __construct() {
		
		parent::__construct();
		
		require_once(__DIR__.'/../libraries/yaml/lib/sfYaml.php');		
		
	}
		
	public function index() {
		
		$output = '<h1>All Work</h1>';
		
		$works = array();
	
		if ($handle = opendir(APPPATH.'views/work')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' AND $file != '..') { 
				 	$works[] = sfYaml::load(APPPATH.'views/work/'.$file);
				}
			}
			closedir($handle);			
		}

	    foreach ($works as $work) {
			$output .= $this->_draw($work);
	    }

	    echo $output;
		
		
	}
	
	public function show($work_id, $pg = 1) {
		
		if ($pg > 1)
			echo "<b>Page $pg</b>";
				
		$work = sfYaml::load(APPPATH.'views/work/'.$work_id.'.yaml');	
		echo $this->_draw($work);
	}
	
	private function _draw($work) {

        $output = '<a href="/work/'.$work['work_id'].'"><h3>'.$work['title'].'</h3></a>';
        $output .= '<p>'.$work['description'].'</p><br/>';
		return $output;
		
	}
	
	
	protected function _load_work_yaml() {

		$works = array();
		
		if ($handle = opendir(APPPATH.'/views/work')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' AND $file != '..') { 
				 	$works[] = Spyc::YAMLLoad(APPPATH.'/views/work/'.$file);
				}
			}
			closedir($handle);			
		}
		
		//die(var_dump($works));
		return $works;
		
	}

	public function _action_index($work_id = 'cover')
	{
		
		// $this->curr_id = Arr::get($this->request->query(), 'id', 'cover');

		$this->curr_id = $work_id;

		$this->categories = array("Applications", "Websites", "Design");
		$this->work = array(array(), array(), array());
			
		$works = $this->_load_work_big_yaml();
		
		foreach ($works as $work_array) {
			
			$cat = $work_array['category'];
			
			if ($work_array['visible']) {
			
				$this->work[$cat][] = $work_array;

				if ($work_array['work_id'] == $this->curr_id) {
					$this->curr_work = $work_array; 
					$this->curr_cat = $cat;
					$this->curr_index = count($this->work[$cat])-1;
				}
			}

		}
		
		$this->work_images = array();
		for ($i=1; $i<=$this->curr_work['numpics']; $i++) {
			$this->work_images[] = '/assets/images/'.$this->curr_id.$i.$this->curr_work['extension'];

		}
		
		$t = new View('template');
		$t->categories = $this->categories;
		$t->work = $this->work;
		$t->work_images = $this->work_images;
		
		$t->curr_work = $this->curr_work;
		$t->curr_id = $this->curr_id;
		$t->curr_cat = $this->curr_cat;
		$t->curr_index = $this->curr_index;
		
		$t->getNextProject = $this->getNextProject();
		$t->getPrevProject = $this->getPrevProject();
		$t->drawProjects = $this->drawProjects();
				
		$this->response->body($t);
		
		
	}
	
	public function getNextProject() {

		if ($this->curr_cat == count($this->categories)-1 && $this->curr_index == count($this->work[$this->curr_cat])-1) { // last category, last project
			return '/work/'.$this->work[0][0]['work_id'];
		} else if ($this->curr_index == count($this->work[$this->curr_cat])-1) { //last project in category
			return '/work/'.$this->work[($this->curr_cat+1)][0]['work_id'];
		} else {
			return '/work/'.$this->work[$this->curr_cat][($this->curr_index+1)]['work_id'];
		}
	} 
	public function getPrevProject() {
		
		if ($this->curr_cat == 0 && $this->curr_index == 0) { // first category, first project
			return '/work/'.$this->work[2][(count($this->work[2])-1)]['work_id'];
		} else if ($this->curr_index == 0) { //first project in category
			$prev_cat = $this->curr_cat-1;
			$prev_cat_last = count($this->work[$prev_cat])-1;
			return '/work/'.$this->work[$prev_cat][$prev_cat_last]['work_id'];
		} else {
			return '/work/'.$this->work[$this->curr_cat][($this->curr_index-1)]['work_id'];
		}
	}

	public function drawProjects() {

		$m = '<ul class="folio">'."\n";
		for ($c=0; $c<count($this->categories); $c++) { 
			$m .= "\t\t".'<li class="category">'.$this->categories[$c]."\n";
			$m .= "\t\t\t".'<ul>'."\n";
			for ($i=0; $i<count($this->work[$c]); $i++) { 
				$mname = $this->work[$c][$i]['menuname'];
				if ($this->work[$c][$i]['work_id'] == $this->curr_id) { 
					$w = '<li class="folio_xlight" id="current_project">&bull; '.$mname."</li>\n"; 
				} else { 
					$w = '<li><a title="'.$mname.'" href="/work/'.$this->work[$c][$i]['work_id'].'">'.$mname."</a></li>\n"; 
				}
				if ($mname != "Cover") { $m .= "\t\t\t\t".$w; }
			}  
			$m .= "\t\t\t</ul></li>\n";
		}
		$m .= "\t\t</ul>\n";

		return $m;

	}
	

	
}