<?php

require_once __DIR__ . '/5-config.php';

mud_define_version( 'PCLOG' );

// 2017-02-21 jj5 - DONE: some phpbom compatibility stuff
define( 'RES_DIR', mud_get_config( [ 'pclog', 'resource', 'dir-path' ] ) );
define( 'URL_BASE', mud_get_config( [ 'pclog', 'url', 'base' ] ) );


if ( ! mud_get_config( [ 'pclog', 'resource', 'url-path' ] ) ) {

  fail( 'Setting pclog.resource.url-path must be specified.' );

}

if ( ! is_dir( mud_get_config( [ 'pclog', 'resource', 'dir-path' ] ) ) ) {

  fail(
    'Invalid resource directory path.',
    [ 'dir-path' => mud_get_config( [ 'pclog', 'resource', 'dir-path' ] ) ]
  );

}
