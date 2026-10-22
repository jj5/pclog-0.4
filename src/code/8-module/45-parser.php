<?php

function get_mesg( $data, &$form = null ) {

  $form = read_ad( 'form', $data );

  switch ( $form ) {

    case 'php-notice-1' :

      $mesg = $data[ 'data' ][ 'notice' ][ 'message' ];
      break;

    case 'php-ass-1' :

      $mesg = $data[ 'data' ][ 'assertion' ][ 'description' ];
      break;

    case 'php-err-1' :

      $mesg = $data[ 'data' ][ 'error' ][ 'message' ];
      break;

    case 'php-ex-1' :

      $mesg = $data[ 'data' ][ 'exception' ][ 'message' ];
      break;

    case 'php-http-1' :

      $mesg = "HTTP: {$data[ 'data' ][ 'http_error' ][ 'code' ]}";
      break;

    case 'web-ex-1' :

      $mesg = $data[ 'data' ][ 'exception' ][ 'message' ];
      break;

    default :

      if ( isset( $data[ 'data' ][ 'state' ][ 'message' ] ) ) {

        $mesg = $data[ 'data' ][ 'state' ][ 'message' ];

      }
      else {

        $mesg = null;

      }

      break;

  }

  return $mesg;

}

function read_log_file( $file ) {

  $file_info = parse_log_file( $file );
  $path = $file_info[ 'path' ];

  $json_data = json_decode( file_get_contents( $path ), true );

  /*
  $bcap =
    isset( $json_data[ 'agnt' ] ) ?
    get_browser( $json_data[ 'agnt' ], true ) :
    null;
  */

  $bcap = null;

  if ( isset( $json_data[ 'agnt' ] ) && function_exists ( 'get_browser' ) ) {

    $bcap = get_browser( $json_data[ 'agnt' ], true );

  }


  $msts = read_ad( 'msts', $json_data, 0 );

  $tsec = intval( round( $msts / 1000.0 ) );

  //$date = php_tsec_format( $time, 'D, j M Y' );
  //$time = php_tsec_format( $time, 'g:i:s A' );

  $date = php_tsec_format( $tsec, 'Y-m-d' );
  $time = php_tsec_format( $tsec, 'H:i:s' );

  $spec_data = [
    'mesg' => get_mesg( $json_data ),
    //'dnic' => $date,
    //'tnic' => $time,
    'date' => $date,
    'time' => $time,
    'file' => $file,
    'tsec' => $tsec,
  ];

  if ( $bcap ) {

    $spec_data[ 'brow' ] = [
      'browser' =>  read_ad( 'browser', $bcap ),
      //'version' =>  read_ad( 'comment', $bcap ),
      'version' =>  read_ad( 'version', $bcap ),
      'platform' => read_ad( 'platform', $bcap ),
      'device' =>  read_ad( 'device_name', $bcap ),
      'pointer' =>  read_ad( 'device_pointing_method', $bcap ),
    ];

  }
  else {

    $spec_data[ 'brow' ] = [];

  }

  $file_data = array_merge( $spec_data, $json_data );

  $file_data[ 'stat' ] = $file_info;

  if ( $bcap ) {

    $file_data[ 'bcap' ] = $bcap;

  }

  return $file_data;

}

function parse_log_file( $file ) {

  $parts = explode( '-', $file );
  $tsec = intval( $parts[ 0 ] / 1000 );
  $datetime = date( 'Y-m-d H:i:s', $tsec );
  $type = explode( '.', $parts[ 3 ] )[ 0 ];

  $log_dir = get_log_dir();
  $date_path = date( 'Y/m/d', $tsec );

  $meta_file = "{$parts[ 0 ]}-{$parts[ 1 ]}-1-meta.json";
  $data_file = "{$parts[ 0 ]}-{$parts[ 1 ]}-2-data.json";
  $fail_file = "{$parts[ 0 ]}-{$parts[ 1 ]}-2-data.fail";

  $path = "$log_dir/$date_path/$file";
  $meta_path = "$log_dir/$date_path/$meta_file";
  $data_path = "$log_dir/$date_path/$data_file";
  $fail_path = "$log_dir/$date_path/$fail_file";

  $size = filesize( $path );

  if ( !file_exists( $meta_path ) ) {
    $meta_file = null;
    $meta_path = null;
    $meta_size = 0;
  }
  else {
    $meta_size = filesize( $meta_path );
  }

  if ( !file_exists( $data_path ) ) {
    $data_file = null;
    $data_path = null;
    $data_size = 0;
  }
  else {
    $data_size = filesize( $data_path );
  }

  if ( !file_exists( $fail_path ) ) {
    $fail_file = null;
    $fail_path = null;
    $fail_size = 0;
  }
  else {
    $fail_size = filesize( $fail_path );
  }



  return [
    'datetime' => $datetime,
    'file' => $file,
    'path' => $path,
    'size' => $size,
    'type' => $type,
    'tsec' => $tsec,
    'meta_file' => $meta_file,
    'meta_path' => $meta_path,
    'meta_size' => $meta_size,
    'data_file' => $data_file,
    'data_path' => $data_path,
    'data_size' => $data_size,
    'fail_file' => $fail_file,
    'fail_path' => $fail_path,
    'fail_size' => $fail_size
  ];

}
