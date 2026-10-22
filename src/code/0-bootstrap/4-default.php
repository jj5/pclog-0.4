<?php

require_once __DIR__ . '/3-library.php';

if ( ! mud_is_test() ) {

  // 2026-10-23 jj5 - we don't apply the default config when running tests so that we can test different configurations
  // without interference.

  app_apply_default_config();

}

function app_apply_default_config() {

  global $config;

  // 2026-10-23 jj5 - NOTE: we don't force-reset this config because it doesn't buy us anything atm.
  //$config = [];

  $config[ 'pclog' ][ 'controller' ][ 'api' ] = '/log/api';
  $config[ 'pclog' ][ 'workspace' ][ 'base' ] = "/var/state/pclog";

  $base = $config[ 'pclog' ][ 'workspace' ][ 'base' ];

  $config[ 'pclog' ][ 'workspace' ][ 'path' ] = "$base/data";

  $dir_path = realpath( __DIR__ . '/../web/res' );

  $config[ 'pclog' ][ 'resource' ][ 'dir-path' ] = $dir_path;

}
