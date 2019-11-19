<?php
require_once('helper.php');
require_once('db.php');

$ROUTE = mb_strtolower($_SERVER['REQUEST_URI']);
$METHOD = $_SERVER['REQUEST_METHOD'];
$POST_DATA = file_get_contents('php://input', true);
$POST = json_decode($POST_DATA);

$ROUTE_ARR = explode('/', $ROUTE);

if( !$ROUTE_ARR[0] ) array_shift($ROUTE_ARR);
//if( strripos(end($ROUTE_ARR), '?') )
//  array_pop($ROUTE_ARR);

//var_dump($ROUTE_ARR);
//echo $ROUTE . $METHOD;

if ( $ROUTE_ARR[0] === 'api' ) {
  if ( $ROUTE_ARR[1] === 'auth' ) {
    
  } else {
    $TABLE = $ROUTE_ARR[1];
    $ID = $ROUTE_ARR[2];
    
    switch ( $METHOD ) {
      case 'GET':
        if ( isset($ID) ) {
          
          $response = R::load($TABLE, $ID);
          
        } else {
          
          $offset = 0;
          $amount = 100;
          $result = R::findAll($TABLE, 'ORDER BY id ASC LIMIT :offset, :count',
                     ['offset' => 0, 'count' => 10]);
          $response = R::exportAll( $result );
          
        }
        break;
      case 'POST':
        $t = R::dispense($TABLE);
        break;
    }
  }
  R::close();
  echo json_encode($response);
} else {
  http_response_code(400); //Bad Request
  echo(json_encode(['error' => 'How did you get here???((' . $ROUTE]));
}
//  switch ($ROUTE_ARR[1]) {
//    case 'book':
//      if ($ROUTE_ARR[2] && $METHOD === 'GET') {
////api/book/:1
//        $sql = "SELECT * FROM book WHERE id='{$ROUTE_ARR[2]}'";
//        $result = $db -> query($sql);
//        $selection = $result -> fetch_assoc();
//        echo json_encode($selection);
//
//      } else {
////api/book
//        switch ($METHOD) {
//          case 'GET':
//            $sql = "SELECT * FROM book";
//            $result = $db -> query($sql);
//            $selection = [];
//            while ($row = $result -> fetch_assoc()) {
//              $selection[] = $row;
//            }
//            echo json_encode($selection);
//            break;
//          case 'POST':
//            $title = $POST -> title;
//            $author = $POST -> author;
//            $total = $POST -> total;
//            $available = $POST -> available;
//            $sql = "INSERT INTO book(title, author, total, available)
//            VALUES ('$title','$author',$total,$available)";
//            $db -> query($sql);
//            echo "Successfull added: $title!";
//            var_dump($db -> error);
//            break;
//          case 'DELETE':
//            echo 'Delete the book';
//            break;
//          case 'PUT':
//            echo 'Update the book';
//            break;
//          default:
//            http_response_code(400); //Bad Request
//            exit('Wrong METHOD');
//        }
//      }
//      break;
//    case 'librarians':
//      echo 'librarians';
//      break;
//    case 'readers':
//      echo 'readers';
//      break;
//    case 'visits':
//      echo 'visits';
//      break;
//    default:
//      http_response_code(400); //Bad Request
//      exit('How did you get here???((' . $ROUTE);
//  }
//} else {
//  http_response_code(400); //Bad Request
//  exit('How did you get here???((' . $ROUTE);
//}
//if ($req_method === 'GET') {
//  if ($route === 'books') {
//    $sql = 'SELECT * FROM book';
//  }
//
//  if ($route === 'librarians') {
//    $sql = 'SELECT * FROM librarians';
//  }
//
//  if ($route === 'readers') {
//    $sql = 'SELECT * FROM readers';
//  }
//
//  if ($route === 'visits') {
//    $sql = 'SELECT reader.name, book.title, librarian.name, visit.event_date
//  FROM visit
//
//  INNER JOIN reader
//  ON reader.id = visit.id_reader
//
//  INNER JOIN book
//  ON book.id = visit.id_book
//
//  INNER JOIN librarian
//  ON librarian.id = visit.id_librarian
//
//  WHERE visit.id > 0;';
//  }
//} else {
//  $sql = "SET AUTOCOMMIT=0;
//START TRANSACTION;
//
//INSERT INTO visit(id_reader, id_book, id_librarian, event_date) VALUES
//(2, 8, 1, '2019-11-05');
//
//UPDATE book
//SET available = available - 1
//WHERE id = 8;
//
//COMMIT;"
//}
//
//
//$res = $db -> query($sql);
//
//
//$selection = [];
//while ($row = $res -> fetch_assoc() ) {
//  $selection[] = $row;
//}
//
////echo json_encode($selection);
//
//echo '<ol>';
//for ($i = 0; $selection[$i]; $i++) {
//  echo '<li><ul>';
//  echo '<li>'.$selection[$i]['id'].'</li>';
//  echo '<li>'.$selection[$i]['title'].'</li>';
//  echo '<li>'.$selection[$i]['author'].'</li>';
//  echo '<li>'.$selection[$i]['total'].'</li>';
//  echo '<li>'.$selection[$i]['availible'].'</li>';
//  echo '</ul></li>';
//}
//echo '</ol>';