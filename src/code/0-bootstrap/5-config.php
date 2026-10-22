<?php

require_once __DIR__ . '/4-default.php';

if ( ! mud_is_test() ) {

  if ( file_exists( PCLOG_CONFIG_PATH ) ) { require_once PCLOG_CONFIG_PATH; }

  if ( file_exists( PCLOG_CONFIG_HOST ) ) { require_once PCLOG_CONFIG_HOST; }

}
