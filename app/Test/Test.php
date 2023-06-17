<?php
namespace App\Test;
class Test {
	public string $test;

	public function __construct($test){
		$this->test = $test;
	}

	public function getTestString(){
		echo $this->test;
	}
}