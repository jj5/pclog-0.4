<?php

/*
assert_options( ASSERT_CALLBACK, 'handle_assert_failure' );
set_error_handler( 'handle_error', E_ALL | E_STRICT );
set_exception_handler( 'handle_exception' );

function handle_assert_failure(
  $file,
  $line,
  $assertion,
  $description = null
) {

  exit_500( "Assertion violated: $file: $line" );

}

function handle_error(
  $errno,
  $errstr,
  $errfile,
  $errline,
  $errcontext
) {

  exit_500( "Error [$errno]: $errstr: $errfile: $errline" );

}

function handle_exception( $ex ) {

  $message = $ex->getMessage();
  $file = $ex->getFile();
  $line = $ex->getLine();

  exit_500( "Exception: $message: $file: $line" );

}
*/

// 2018-09-14 jj5 - for pclog, consider something like:
//* 'host' = computer name (e.g. fortitude-test)
//* 'name' = HTTP HOST (e.g. log.blackbrick.com.au)


main();

function main() {

  //error_log( serialize( $GLOBALS ) );

  process_request( $msts );

  echo $msts;

}

function process_request( &$msts ) {

  $error_reporting = error_reporting();

  try {

    error_reporting( 0 );

    try_process_request( $msts );

    error_reporting( $error_reporting );

  }
  catch ( Throwable $ex ) {

    // we tried

    try {

      error_log( 'Error submitting error to pclog.' );

      error_log( $ex->getMessage() );

    }
    catch ( Throwable $ignore ) { ; }

    error_reporting( $error_reporting );

    //exit_500( 'Error processing request.' );

  }
}

function try_process_request( &$msts ) {

  if ( ! mud_is_post() ) { fail( "Only HTTP POST supported." ); }

  $now = pclog_date();
  $utc = pclog_date_utc();

  static $types = [ 'meta' => 1, 'data' => 2 ];

  // 2017-02-21 jj5 - NOTE: forms format is: environment-issue-version

  // 2017-02-21 jj5 - NOTE: supported environments are:
  //* browser: web browsers
  //* php: PHP code

  // 2017-02-21 jj5 - NOTE: supported issues are:
  //* ex: unhandled exception
  //* http: HTTP status (e.g. 404, 500, etc.)
  //* assert: violated assertion
  //* err: envirnment error

  /* 2017-03-08 jj5 - client's can nominate any form now...
  static $forms = [
    'web-ex-1' => 1,
    'php-http-1' => 1,
    'php-ass-1' => 1,
    'php-err-1' => 1,
    'php-ex-1' => 1,
    'dit-ex-1' => 1
  ];
  */

  $action_spec = get_path_info();

  $action = explode( '/', $action_spec );

  if ( count( $action ) !== 7 ) {

    fail( "Invalid action '$action_spec'." );

  }

  $command = $action[ 1 ];
  $type = $action[ 2 ];
  $form = $action[ 3 ];
  $uuid = $action[ 4 ];
  $msts = intval( $action[ 5 ] );
  $client = $action[ 6 ];

  //error_log( 'action[5]: ' . $action[ 5 ] );
  //error_log( 'type: ' . $type );
  //error_log( 'action: ' . $action_spec );
  //error_log( 'msts-1: ' . $msts );

  if ( $type === 'meta' ) { $msts = get_timestamp(); }

  //error_log( 'msts-2: ' . $msts );

  if ( $command !== 'submit' ) {

    fail( "Invalid command '$command'." );

  }

  if ( ! array_key_exists( $type, $types ) ) {

    fail( "Invalid type '$type'." );

  }

  /*
  if ( ! array_key_exists( $form, $forms ) ) {

    fail( "Invalid form '$form'." );

  }
  */

  // 2017-02-21 jj5 - TODO: validate UUID
  if ( ! preg_match( '/^[0-9a-f-]{36}$/', $uuid ) ) {

    fail( "Invalid UUID '$uuid'." );

  }

  if ( ! preg_match( '/^\d{13}$/', $msts ) ) {

    fail( "Invalid msts '$msts'." );

  }

  // 2017-02-21 jj5 - TODO: should probably validate $client input

  $text = trim( file_get_contents( 'php://input' ) );

  //error_log( 'json text: ' . serialize( substr( $text, 0, 20 ) ) );

  if ( strlen( $text ) === 0 ) {

    global $HTTP_RAW_POST_DATA;

    //error_log( 'POST data: ' . $HTTP_RAW_POST_DATA );

    exit;

  }

  $data = json_decode( $text, true );

  if ( empty( $data ) ) {

    return log_fail( $type, $form, $uuid, $msts, $text, $data );

  }

  $data[ 'msts' ] = $msts;

  // 2017-05-29 jj5 - DONE: if we got this from a browser log the client IP...
  if ( strpos( $form, 'web-' ) === 0 ) {

    $data[ 'addr' ] = pclog_addr();
    $data[ 'agnt' ] = pclog_agnt();

  }

  // 2017-05-29 jj5 - DONE: if user is 'false' make it null...
  if ( isset( $data[ 'user' ] ) && $data[ 'user' ] === false ) {

    $data[ 'user' ] = null;

  }


  //error_log( 'data keys: ' . implode( ',', array_keys( $data ) ) );

  if ( ! isset( $data[ 'data' ] ) ) {

    //throw new Exception( '$data missing "data".' );
    $data[ 'data' ] = [];

  }

  //error_log( 'data[data] type: ' . gettype( $data[ 'data' ] ) );

  apply_time( $data, $msts );

  apply_info( $data, $client );

  switch ( $type ) {

    case 'meta' :

      return log_meta( $type, $form, $uuid, $msts, $text, $data );

    case 'data' :

      return log_data( $type, $form, $uuid, $msts, $text, $data );

    default :

      fail( "Unsupported type '$type'." );

  }
}

function log_meta( $type, $form, $uuid, $msts, $text, &$data ) {

  write_json_log( $type, $uuid, $msts, $data, $file_name );

  send_email( $data, $file_name );

}

function log_data( $type, $form, $uuid, $msts, $text, &$data ) {

  write_json_log( $type, $uuid, $msts, $data );

}

function send_email( $data, $file_name ) {

  try {

    // 2019-11-10 jj5 - send email notification if not DEBUG/DEV...

    $debug = $data[ 'flag' ][ 'DEBUG' ] ?? false;
    $dev   = $data[ 'flag' ][ 'DEV'   ] ?? false;

    if ( $debug || $dev ) { return; }

    if ( function_exists( 'filter_email' ) ) {

      if ( filter_email( $data ) ) { return; }

    }

    global $config;

    $email = $config[ 'pclog' ][ 'notify' ][ 'email' ] ?? null;

    if ( ! $email ) { return; }

    $url_base = $config[ 'pclog' ][ 'url' ][ 'base' ] ?? null;

    if ( ! $url_base ) { return; }

    $from = $config[ 'pclog' ][ 'notify' ][ 'from' ] ?? null;

    if ( ! $from ) {

      $hostname = trim( `hostname -f` );

      $from = "pclog@$hostname";

    }

    $host = $data[ 'host' ] ?? null;
    $slug = $data[ 'slug' ] ?? null;
    $form = $data[ 'form' ] ?? null;

    $mesg =
      $data[ 'data' ][ 'exception' ][ 'message' ] ??
      $data[ 'data' ][ 'error' ][ 'message' ] ??
      $data[ 'data' ][ 'state' ][ 'context' ][ 'message' ] ??
      $data[ 'data' ][ 'state' ][ 'message' ] ??
      null;

    $report = trim( $data[ 'data' ][ 'report' ] ?? '' );

    unset( $data[ 'data' ][ 'report' ] );

    $subject = "[PCLOG] $host: $slug: $form: $mesg";
    $message =
      $url_base . '/?file=' . $file_name . "\n\n" .
      $report . "\n\n" .
      json_encode(
        $data,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
      );

    $headers =
      "From: $from" . "\r\n" .
      'X-Mailer: PHP/' . phpversion();

    mail( $email, $subject, $message, $headers );

  }
  catch ( Throwable $ex ) {

  }

}

function write_json_log( $type, $uuid, $msts, &$data, &$file_name = null ) {

  $path = get_file_path( $type, $uuid, $msts, 'json', $file_name );

  $json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) .
    "\n";

  file_put_contents( $path, $json );

}

function log_fail( $type, $form, $uuid, $msts, $text, &$data ) {

  $path = get_fail_path( $type, $uuid, $msts );

  file_put_contents( $path, $text );

}

function get_fail_path( $type, $uuid, $msts = null ) {

  return get_file_path( $type, $uuid, $msts, 'fail' );

}

function get_file_path(
  $type,
  $uuid,
  $msts,
  $extension = 'json',
  &$file_name = null
) {

  $log_dir = get_log_dir();
  $date_path = pclog_date()->format( 'Y/m/d' );

  $dir = "$log_dir/$date_path";

  if ( ! is_dir( $dir ) ) {

    mkdir( $dir, 0777, true );

  }

  if ( ! is_dir( $dir ) ) {

    fail( "Invalid directory '$dir'." );

  }

  $hash = substr( $uuid, 0, 8 );
  $type_code = get_type_code( $type );

  $file_name = "$msts-$hash-$type_code-$type.$extension";

  return "$log_dir/$date_path/$file_name";

}

function get_timestamp() {

  return intval( round( microtime( true ) * 1000 ) );

}

function get_type_code( $type ) {

  static $codes = [ 'meta' => 1, 'data' => 2 ];

  return $codes[ $type ];

}

function apply_time( &$data, $msts ) {

  $now = pclog_date();
  $utc = pclog_date_utc();

  if ( ! array_key_exists( 'time', $data[ 'data' ] ) ) {

    $data[ 'data' ][ 'time' ] = [];

  }

  $time = &$data[ 'data' ][ 'time' ];

  $time[ 'msts' ] = $msts;
  $time[ 'date' ] = $now->format( 'Y-m-d' );
  $time[ 'time' ] = $now->format( 'H:i:s' );
  $time[ 'server' ] = $now->format( 'D M d Y H:i:s \G\M\TO (T)' );
  $time[ 'local' ] = $now->format( 'c' );
  $time[ 'utc' ] = $utc->format( 'c' );
  $time[ 'dnic' ] = $now->format( 'D, j M Y' );
  $time[ 'tnic' ] = $now->format( 'g:i A' );

}

function apply_info( &$data, $client ) {

  $data[ 'info' ][ 'client' ] = $client;
  $data[ 'info' ][ 'server' ] = PCLOG_SLUG;

  // 2019-11-10 jj5 - this moved to client code...
  /*
  $net_vars = [
    'REMOTE_ADDR',
    'REMOTE_PORT',
    'REQUEST_SCHEME'
  ];

  foreach ( $net_vars as $net_var ) {

    $data[ 'info' ][ strtolower( $net_var ) ] = getenv( $net_var );

  }

  $server_keys = array_keys( $_SERVER );

  foreach ( $server_keys as $server_key ) {

    if ( strpos( $server_key, 'HTTP_' ) !== 0 ) { continue; }

    $data[ 'info' ][ strtolower( $server_key ) ] = $_SERVER[ $server_key ];

  }
  */

}

function render_page() {
?>
<html>
  <head>
    <title>API controller</title>
  </head>
  <body>
<form method="POST">
  <textarea name="payload"></textarea>
  <button>Submit</button>
</form>
  </body>
</html>
<?php
}
