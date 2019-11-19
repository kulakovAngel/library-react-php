<?php
use \Firebase\JWT\JWT;
require_once("JWT.php");

class JWTAuth {
  private const KEY = "cWt3S1tkEb";
  private $token = [];
  private $jwt;
  
  public function __construct($id, $user) {
    $this -> token = ["iss" => $_SERVER["SERVER_NAME"],
                    "exp" => time() + 3600]; //один час
    $this -> token['id'] = $id;
    $this -> token['user'] = $user;
    $this -> jwt = JWT::encode($this -> token, self::KEY);
  }
  
  public function get() {
    return $this -> jwt;
  }
  
  public static function decode($jwt) {
    $decoded = JWT::decode($jwt, self::KEY, array('HS256'));
    return $decoded;
  }
  
  public static function verify($jwt) {
    $decoded = JWT::decode($jwt, self::KEY, array('HS256'));
//    $id = $decoded -> id;
//    $name = $decoded -> name;
    return $decoded;
  }
}