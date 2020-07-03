<?php

class UniqueCodeGenerator {

    private $db;
    private $pg;

    /*constructor*/
	function __construct() {
        require_once 'dbHandler.php';
        require_once 'passwordGenerator.php';
        $this->db = new DbHandler();
        $this->pg = new PasswordGenerator();
    }

	/**
	* function logs an admin action
	* @return true or error
	*/
    public function generateCode($length, $table, $col, $cap = false) {
        // generate new code
        $newcode = $this->pg->randomAlphaNumericPassword($length);

        if($cap) {
            $newcode = strtoupper($newcode);
        }
        // check if the code is already in the database
        $code_exists = $this->db->getOneRecord("SELECT 1 FROM $table WHERE $col = '$newcode'");

        if($code_exists) {
            // generate another one
            $this->generateCode($length, $table, $col, $cap);
        } else {
            return $newcode;
        }
    }
}