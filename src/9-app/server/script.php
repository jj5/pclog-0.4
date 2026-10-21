<?php

if ( ! is_get() ) { exit_500( "Only HTTP GET supported." ); }

$path_info = get_path_info();

if ( $path_info === '/script/browser.js' ) {

  render_browser_js();

}
else {

  exit_404();

}

function render_browser_js() {

  global $config;

  if ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] === 'on' ) {

    $scheme = 'https://';

  }
  else {

    $scheme = 'http://';

  }

  $host = $_SERVER[ 'HTTP_HOST' ];

  $api_path = $config[ 'pclog' ][ 'controller' ][ 'api' ];

  $submit_endpoint = "{$scheme}{$host}{$api_path}";

  header( 'Content-Type: application/javascript;charset=UTF-8' );

  render_script( __DIR__ . '/../client/browser.js' );

  //$user = phpbom_user();
  $user = pclog_user() ?? '';

  if (
    strpos( $user, "'" ) !== false ||
    strpos( $user, '\\' ) !== false
  ) {

    exit_500( "Invalid username '$user'." );

  }

  $user = $user ? json_encode( $user ) : 'null';

  echo "\n";

  echo "var pclog_submit_endpoint = " .
    json_encode( $submit_endpoint, JSON_UNESCAPED_SLASHES ) . ";\n";

  echo "var pclog_client = APP_SLUG;\n";
  echo "var pclog_user = $user;\n";
  echo "\n";

}

function render_script( $path ) {

  readfile( $path );

  echo "\n\n";

}

// 2017-02-23 jj5 - DONE: host setting now detected client-side
