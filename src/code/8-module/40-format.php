<?php
/*
function format_xx( $value ) {

  $value = intval( $value );

  if( $value < 10 ) { return "0$value"; }

  return "$value";

}

// 2017-02-19 jj5 - SEE: formatBytes from StackOverflow:
//  http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes#2510459

*/

/*
function format_date( $date ) {

  $date = date_create( $date );

  return date_format( $date, 'D, j M Y' );

}
*/

function format_file( $file ) {

  $file_info = parse_log_file( $file );

  $datetime = $file_info[ 'datetime' ];
  $meta_path = $file_info[ 'meta_path' ];

  $json = file_get_contents( $meta_path );
  $data = json_decode( $json, true );
  $form = read_ad( 'form', $data, 'missing' );

  list( $time, $issue ) = explode( '-', $file );

  //return "$date ($type): $form";
  return "$datetime: $form: #$issue";

}
