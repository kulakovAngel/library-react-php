<?php

error_reporting(-1);
ini_set('display_errors', 'Off');
ini_set('error_log', $_SERVER['DOCUMENT_ROOT'].'/log.txt');
ini_set('log_errors', 'On');

function add_keys_to_obj($to, $from) {
  foreach($from as $key => $value) {
    $to -> $key = $value;
  }
}

function verifyRights($jwt) {
    $rights = 0;
    try {
        $decoded = JWTAuth::verify( $jwt );
        $login = $decoded -> user;
        $id = $decoded -> id;
        
        $user = R::findOne( 'librarian', "login='$login'");
        if ( $user ) $rights = 2;
        else {
            $user = R::findOne( 'reader', "login='$login'");
            if ( $user ) $rights = 1;
        }
        
        if ($decoded) {
            $jwtO = new JWTAuth($id, $login);
            $jwt = $jwtO -> get();
            return ['rights' => $rights, 'login' => $login, 'jwt' => $jwt, 'status' => 'Ok'];
        } else {
            return ['rights' => $rights, 'status' => 'Verification error'];
        }
    } catch (Exception $e) {
        return ['rights' => $rights, 'status' => 'Verification error'];
    }
}


