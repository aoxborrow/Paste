<?php

namespace Paste;

// debug lib for testing and benchmarking
class Debug {

	public $benchmark_start;

	public function bench() {

		// start benchmark
		$this->benchmark_start = microtime(TRUE);
		
		echo '<pre>';


		for ($i = 0; $i < 1000; $i++) {

			/*
			// 100x = .775
			// 1000x = 7.3575
			echo 'benchmarking content db #'.$i."\n";
			$c = Content::load_section(Paste::$content_path);
			*/

			// 100x = .145
			// 1000x = 1.3808
			echo 'benchmarking content hash #'.$i."\n";
			$c = Content::content_hash(Paste::$content_path);
			clearstatcache();

		}
		
		echo '</pre>';

		// stop benchmark, get execution time
		echo number_format(microtime(TRUE) - $this->benchmark_start, 4);

	}
}