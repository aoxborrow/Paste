<?php

class Work_Controller extends Template_Controller {
	
	public function index() {
		
		$output = '<h1>All Work</h1>';
		
		$works = array();
	
		if ($handle = opendir(__DIR__.'/../../pin/views/work')) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' AND $file != '..') { 
				 	$works[] = yaml_parse_file(__DIR__.'/../../pin/views/work/'.$file);
				}
			}
			closedir($handle);			
		}

	    foreach ($works as $work) {
			$output .= $this->_draw($work);
	    }

	    echo $output;
		
		
	}
	
	public function show($args) {
		
		$work = yaml_parse_file(__DIR__.'/../../pin/views/work/'.$args[0].'.yaml');
	
		echo $this->_draw($work);
	}
	
	private function _draw($work) {

        $output = '<h3>'.$work['title'].'</h3>';
        $output .= '<a href="/work/'.$work['work_id'].'">'.$work['work_id'].'</a>';
        $output .= '<p>'.$work['description'].'</p><br/>';
		return $output;
		
	}

	
}