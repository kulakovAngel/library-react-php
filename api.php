<?php
require_once('helper.php');
require_once('db.php');
require_once('php-jwt/my-auth.php');

header('Access-Control-Allow-Origin: *');
//header('Access-Control-Allow-Methods: *');
header('Content-Type: *');
//header("Content-Type: application/json");
header('Access-Control-Allow-Credentials: true');
//header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT,DELETE');

mb_internal_encoding( "UTF-8" );

$POST_DATA_JSON = file_get_contents( 'php://input', true );
$POST_DATA = json_decode( $POST_DATA_JSON );
//var_dump(json_encode($POST_DATA -> login));
$METHOD = $_SERVER[ 'REQUEST_METHOD' ];
$ROUTE = mb_strtolower( $_SERVER[ 'REQUEST_URI' ] );


$ROUTE_ARR = explode( '/', $ROUTE );
if ( !$ROUTE_ARR[0] ) array_shift( $ROUTE_ARR );
if ( !end( $ROUTE_ARR ) ) array_pop( $ROUTE_ARR );
if ( strripos( end( $ROUTE_ARR ), '?') !== false ) array_pop( $ROUTE_ARR );


$AUTH = verifyRights( $POST_DATA -> jwt );
//exit(json_decode($POST_DATA));
unset( $POST_DATA -> jwt );

$response = 'No data';

if ( $ROUTE_ARR[0] !== 'api' ) {
    http_response_code(400); //Bad Request
    exit ( json_encode( ['error' => 'API interface reqiered. How did you get here???' . $ROUTE] ) );
}

 if ( $METHOD === 'OPTIONS' ) {
     http_response_code(200);
     exit();
 }

if ( $ROUTE_ARR[1] === 'auth' ) {
    if ( $METHOD !== 'POST' ) {
        http_response_code(405); //Method Not Allowed
        exit ( json_encode( ['error' => 'Wrong method for auth' ] ) );
    } else {
        switch ($ROUTE_ARR[2]) {
            case 'signin':
                //проверка, может уже есть логин такой
                //$book  = R::find( 'book', ' rating > 4 ');
                $l = R::getCell( "SELECT (login) FROM librarian WHERE login='{$POST_DATA -> login}'" );
                if ($POST_DATA -> login === $l) {
                    http_response_code(409); //Conflict
                    exit( json_encode( ['error' => "Already exists: $l"] ) );
                } else {
                    $ph = password_hash($POST_DATA -> password, PASSWORD_DEFAULT);
                    $t = R::dispense( 'librarian' );
                    $t -> name = $POST_DATA -> name;
                    $t -> login = $POST_DATA -> login;
                    $t -> password_hash = $ph;
                    R::store( $t );
                    $response = json_encode(['message' => "User added!"]);
                    //exit(json_encode(['message' => "User added!"]));
                }
                break;

            case 'login':
                $user = R::findOne( 'librarian', "login='{$POST_DATA -> login}'");
                if ( !$user ) {
                    $user = R::findOne( 'reader', "login='{$POST_DATA -> login}'");
                }
                //exit(json_encode($user));
                $ver = password_verify($POST_DATA -> password, $user['password_hash']);
                if ($ver) {
                    $jwtO = new JWTAuth($user['id'], $user['login']);
                    $jwt = $jwtO -> get();
                    $response = json_encode( ['name' => $user['name'], 'id' => $user['id'], 'jwt' => $jwt] );
                } else {
                    http_response_code(401); //Unauthorized
                    exit( json_encode( ['error' => 'Invalid login or password'] ) );
                }
                break;
            case 'logout':
                exit( json_encode( ['message' => 'Logout successfully!'] ) );
                break;
        }
    }
  } elseif ( $ROUTE_ARR[1] === 'visit' ) {
    switch ( $METHOD ) {
      
    case 'GET':
    case 'POST':
        if ($AUTH['rights'] === 2) {
//            $t = R::dispense( 'visit' );
//            add_keys_to_obj( $t, $POST_DATA );
//            R::store( $t );
//            
//            $tt = R::load( 'book', $POST_DATA['id_book'] );
//            $a = $tt -> available;
//            $tt -> available = $a - 1;
//            R::store( $tt );
            R::exec("SET AUTOCOMMIT=0;
                    START TRANSACTION;
                    INSERT INTO visit(id_reader, id_book, id_librarian, event_date)
                    VALUES({$POST_DATA -> id_reader}, {$POST_DATA -> id_book}, {$POST_DATA -> id_librarian}, '{$POST_DATA -> event_date}');
                    
                    UPDATE book
                    SET available = available - 1
                    WHERE id = {$POST_DATA -> id_book};
                    COMMIT;");
            //exit(json_encode( ['o' => 'os'] ));
            //$response = $t;
            $response = ['message' => 'Added successfully!'];
        } else {
            http_response_code(401); //Unauthorized
            exit( json_encode( ['error' => 'No such rights'] ) );
        }
        break;
    }
  } else {
    $TABLE = $ROUTE_ARR[1];
    $ID = $ROUTE_ARR[2];
    
    switch ( $METHOD ) {
      
      case 'GET':
        if ( isset($ID) ) {
            $response = R::load( $TABLE, $ID );
        } else {
            //$result  = R::findAll( $TABLE );// - просто все
            $offset = 0;
            $amount = 100;
            $result = R::findAll( $TABLE, 'LIMIT :offset, :count', ['offset' => $offset, 'count' => $amount] );
            $response = R::exportAll( $result );
            foreach( $response as &$value ) {
                if ( isset($value['password_hash']) ) unset( $value['password_hash'] );
            }
            unset($value);
        }
        break;
        
      case 'POST':
        if ($AUTH['rights'] === 2) {
            $t = R::dispense( $TABLE );
            add_keys_to_obj( $t, $POST_DATA );
            R::store( $t );
            //$response = $t;
            $response = ['message' => 'Added successfully!'];
        } else {
            http_response_code(401); //Unauthorized
            exit( json_encode( ['error' => 'No such rights'] ) );
        }
        break;
        
      case 'PUT':
        if ($AUTH['rights'] === 2) {
            $t = R::load( $TABLE, $ID );
            add_keys_to_obj( $t, $POST_DATA );
            R::store( $t );
            //$response = $t;
            $response = ['message' => 'Changed successfully!'];
        } else {
            http_response_code(401); //Unauthorized
            exit( json_encode( ['error' => $AUTH['status']] ) );
        }
        break;
        
      case 'DELETE':
        if ($AUTH['rights'] === 2) {
            $t = R::load( $TABLE, $ID );
            R::trash( $t );
            //$response = $t;
            $response = ['message' => 'Deleted successfully!'];
        } else {
            http_response_code(401); //Unauthorized
            exit( json_encode( ['error' => $AUTH['status']] ) );
        }
        
        break;
    }
  }
  R::close();
  if (isset( $AUTH['jwt'] )) {
      $response['jwt'] = $AUTH['jwt'];
  }
  echo json_encode( $response );
  //var_dump($t);