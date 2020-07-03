<?php

class PasswordGenerator {
	/*constructor*/
	function __construct() {}

	/**
	* function generates a random password
	* @return generated password
	*/
    public function randomPassword($len = 12) {
      $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNPQRSTUWXYZ0123456789!@#$%_";
      $pass = array(); //remember to declare $pass as an array
      $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
      for ($i = 0; $i < $len; $i++) {
          $n = rand(0, $alphaLength);
          $pass[] = $alphabet[$n];
      }
      return implode($pass); //turn the array into a string
    }

    /**
  * function generates a random password
  * @return generated password
  */
    public function randomAlphaNumericPassword($len = 12) {
      $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNPQRSTUWXYZ0123456789";
      $pass = array(); //remember to declare $pass as an array
      $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
      for ($i = 0; $i < $len; $i++) {
          $n = rand(0, $alphaLength);
          $pass[] = $alphabet[$n];
      }
      return implode($pass); //turn the array into a string
    }

    /**
    * function generates a random numeric password
    * @return generated numeric password
    */
    public function randomNumericCode($len = 7) {
      $alphabet = "0123456789";
      $pass = array(); //remember to declare $pass as an array
      $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
      for ($i = 0; $i < $len; $i++) {
          $n = rand(0, $alphaLength);
          $pass[] = $alphabet[$n];
      }
      return implode($pass); //turn the array into a string
    }
}