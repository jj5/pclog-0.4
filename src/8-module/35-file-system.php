<?php

function read_pclog_data( &$log_dir, &$dates, &$meta_files, &$data_files ) {

  $log_dir = get_log_dir();
  $dates = [];
  $meta_files = [];
  $data_files = [];

  foreach ( get_subdirs( $log_dir ) as $year ) {

    foreach ( get_subdirs( $year ) as $month ) {

      foreach ( get_subdirs( $month ) as $day ) {

        foreach ( get_files( $day ) as $file ) {

          $right = substr( $file, strlen( $log_dir ) + 1 );

          $parts = explode( '/', $right );

          $date = "{$parts[0]}-{$parts[1]}-{$parts[2]}";
          $file = $parts[ 3 ];
          $file_parts = explode( '-', $file );
          $file_time = $file_parts[ 0 ];
          $file_uuid = $file_parts[ 1 ];
          $file_type_code = $file_parts[ 2 ];
          $file_type = $file_parts[ 3 ];

          if ( $file_type === 'meta.json' ) {
            $meta_files[ $date ][] = $file;
          }
          else {
            $data_files[ $date ][] = $file;
          }
        }
      }
    }
  }

  $dates = array_keys( $meta_files );

}

function get_log_dir() {

  global $config;

  $workspace_base = $config[ 'pclog' ][ 'workspace' ][ 'base' ];
  $workspace_path = $config[ 'pclog' ][ 'workspace' ][ 'path' ];

  if ( ! is_dir( $workspace_base ) ) {

    exit_500( "Invalid workspace base '$workspace_base'." );

  }

  if ( ! is_dir( $workspace_path ) ) {

    mkdir( $workspace_path );

  }

  if ( ! is_dir( $workspace_path ) ) {

    exit_500( "Invalid workspace path '$workspace_path'." );

  }

  return $workspace_path;

}

function get_subdirs( $path ) {

  $dir = dir( $path );
  $result = [];

  while ( false !== ( $entry = $dir->read() ) ) {

    if ( $entry === '.' || $entry === '..' ) { continue; }

    $new_path = "$path/$entry";

    if ( is_dir( $new_path ) ) { $result[] = $new_path; }

  }

  $dir->close();

  sort( $result );

  return $result;

}

function get_files( $path ) {

  $dir = dir( $path );
  $result = [];

  while ( false !== ( $entry = $dir->read() ) ) {

    if ( $entry === '.' || $entry === '..' ) { continue; }

    $new_path = "$path/$entry";

    if ( is_dir( $new_path ) ) { continue; }

    $result[] = $new_path;

  }

  $dir->close();

  sort( $result );

  return $result;

}

/*
function res( $path ) {

  global $config;

  $base = $config[ 'pclog' ][ 'resource' ][ 'base' ];
  $root = $config[ 'pclog' ][ 'resource' ][ 'root' ];

  $file_path = "$root/$path";

  if ( !file_exists( $file_path ) ) {

    throw new Exception( "Invalid resource path '$file_path'." );

  }

  return "$base/$path";

}
*/
