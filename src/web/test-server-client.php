<?php

$config[ 'pclog' ][ 'version' ][ 'build' ] = 'dev';

require_once __DIR__ . '/../include/application.php';

main( new DateTime() );

function main( $date ) {

  $raise = read_get( 'raise' );

  switch ( $raise ) {

    case 'assert' :

      /*
      $handler = assert_options( ASSERT_CALLBACK );

      trigger_error( $handler );
      */

      //assert( false );
      assert( false );

      break;

    case 'error' :

      trigger_error( 'test error' );

      break;

    case 'exception' :

      $prev = new Exception( 'previous...' );

      throw new Exception( 'test exception', 0, $prev );

      break;

    case 'http' :

      header( 'just: testing' );

      exit_500( 'just testing' );

      break;

  }

  render_head();
  render_menu();
  render_foot();

}

function render_friendly_error( $code, $issue_number = null ) {

  render_head();
  render_menu();
  echo "<p>Error: $code</p>";
  if ( $issue_number ) {
    echo "<p>Issue: $issue_number</p>";
  }
  render_foot();

}

function read_get( $var ) {

  if ( array_key_exists( $var, $_GET ) ) { return $_GET[ $var ]; }

  return null;

}

/*
function render_head( $title_html = 'Test Server Client' ) {
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $title_html ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
<?php

}
*/

function render_menu() {
?>
    <p>
      <a href="./">Home</a> |
      <a href="?raise=assert">Assert</a> |
      <a href="?raise=error">Error</a> |
      <a href="?raise=exception">Exception</a> |
      <a href="?raise=http">HTTP</a>
    </p>
<?php
}

/*
function render_foot() {
?>
  </body>
</html>
<?php
}
*/
