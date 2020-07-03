<?php

class FunctionsWrapper {
	// constructor
	function __construct() {
		// construct
    }

    public function number_format ($number, $decimals = 0) {
		return number_format($number, $decimals);
    }
}