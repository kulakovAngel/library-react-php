<?php
require_once( 'consts.php' );
require_once( 'libs/rb.php' );

R::setup( "mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PASSWORD );
R::freeze( true );
if ( !R::testConnection() ) {
  http_response_code(403);
  exit( json_encode( ['error' => 'No connection to data base'] ) );
}