<?php

// project view model
class HTMLProject extends Project {
	
	public $extension = 'jpg';
	
	public $content = '';
	
	public $images = array();
	
	// factory for chaining methods
	public static function factory($name = NULL, $current_image = 1) {

		$project = new HTMLProject($name, $current_image);
		
		return $project->load();

	}
	
	// load single project data
	public function load() {
		
		if (FALSE === $html = @file_get_contents(APPPATH.'views/projects/'.$this->name.'.html')) {
			return FALSE;
		}
		
		$dom = DOMDocument::loadHTML($html);
		
		// count number of image tags
		$imgs = $dom->getElementsByTagName('img');
		
		// TODO: should just delete the other image tags and leave the current one based on current_image
		
		$i = 1;
		foreach ($imgs as $img) {
			$this->images[$i] = array(
				'src' => $img->getAttribute('src'),
				'alt' => $img->getAttribute('alt'),
				'height' => $img->getAttribute('height'),
				'width' => $img->getAttribute('width'),
			);
			$i++;
		}

		$this->extension = pathinfo($this->images[$this->current_image]['src'], PATHINFO_EXTENSION);		
		$this->caption = $this->images[$this->current_image]['alt'];		
		
		$this->content = self::strip_only_tags($html, 'img');
		
		$this->num_images = count($this->images);
		
		return $this;
		
	}
	
	public static function strip_only_tags($str, $tags, $stripContent=false) {
		$content = '';
		if(!is_array($tags)) {
			$tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array($tags));
			if(end($tags) == '') array_pop($tags);
		}
		foreach($tags as $tag) {
			if ($stripContent)
				 $content = '(.+</'.$tag.'(>|\s[^>]*>)|)';
			 $str = preg_replace('#</?'.$tag.'(>|\s[^>]*>)'.$content.'#is', '', $str);
		}
		return $str;
	}
	
}