<?php

require_once __DIR__ . '/5-config.php';

define_version( 'PCLOG' );

// 2017-02-21 jj5 - DONE: some phpbom compatibility stuff
define( 'RES_DIR', get_config( [ 'pclog', 'resource', 'dir-path' ] ) );
define( 'URL_BASE', get_config( [ 'pclog', 'url', 'base' ] ) );


if ( ! get_config( [ 'pclog', 'resource', 'url-path' ] ) ) {

  fail( 'Setting pclog.resource.url-path must be specified.' );

}

if ( ! is_dir( get_config( [ 'pclog', 'resource', 'dir-path' ] ) ) ) {

  fail(
    'Invalid resource directory path.',
    [ 'dir-path' => get_config( [ 'pclog', 'resource', 'dir-path' ] ) ]
  );

}
