<?php

// debug controller for testing and benchmarking
class debug_controller {

	public $benchmark_start;

	public function __construct() {

		echo '<pre>';

	}

	public function index() {

		// start benchmark
		$this->benchmark_start = microtime(TRUE);


		for ($i = 0; $i < 1000; $i++) {

			/*
			// 100x = .775
			// 1000x = 7.3575
			echo 'benchmarking content db #'.$i."\n";
			$c = Content::load_section(CONTENTPATH);
			*/

			// 100x = .145
			// 1000x = 1.3808
			echo 'benchmarking content hash #'.$i."\n";
			$c = Content::content_hash(CONTENTPATH);
			clearstatcache();

		}

	}

	public function _render() {

		echo '</pre>';

		// stop benchmark, get execution time
		echo number_format(microtime(TRUE) - $this->benchmark_start, 4);

	}

}