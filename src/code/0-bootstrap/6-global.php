<?php

require_once __DIR__ . '/5-config.php';

mud_define_version( 'PCLOG' );

mud_define_error( 'APP_ERR_MISSING_PCLOG_RESOURCE_URL_PATH', 'setting pclog.resource.url-path must be specified.' );
mud_define_error( 'APP_ERR_MISSING_PCLOG_RESOURCE_DIR_PATH', 'setting pclog.resource.dir-path must be specified.' );
mud_define_error( 'APP_ERR_INVALID_PCLOG_RESOURCE_DIR_PATH', 'invalid resource directory path.' );


// 2017-02-21 jj5 - DONE: some phpbom compatibility stuff
define( 'RES_DIR', mud_get_config( [ 'pclog', 'resource', 'dir-path' ] ) );
define( 'URL_BASE', mud_get_config( [ 'pclog', 'url', 'base' ] ) );

(function(){

  $url_path = mud_get_config( [ 'pclog', 'resource', 'url-path' ] );

  if ( ! $url_path ) {

    mud_fail( APP_ERR_MISSING_PCLOG_RESOURCE_URL_PATH );

  }

  $dir_path = mud_get_config( [ 'pclog', 'resource', 'dir-path' ] );

  if ( ! $dir_path ) {

    mud_fail( APP_ERR_MISSING_PCLOG_RESOURCE_DIR_PATH );

  }

  if ( ! is_dir( strval( $dir_path ) ) ) {

    mud_fail(
      APP_ERR_INVALID_PCLOG_RESOURCE_DIR_PATH,
      [ 'dir-path' => $dir_path ]
    );

  }

})();
