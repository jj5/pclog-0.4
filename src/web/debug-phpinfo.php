<?php

(function( $debug_config ) {
  if ( ! file_exists( $debug_config ) ) { return; }
  require_once $debug_config;
  if ( defined( 'DEBUG' ) && DEBUG ) { phpinfo(); }
})( __DIR__ . '/../../debug.php' );
