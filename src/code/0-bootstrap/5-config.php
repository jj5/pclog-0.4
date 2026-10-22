<?php

require_once __DIR__ . '/4-default.php';

// 2018-06-19 jj5 - use require not require_once on config files... if they
// get loaded early they need to be reloaded later too...

if ( file_exists( PCLOG_CONFIG_PATH ) ) { require PCLOG_CONFIG_PATH; }

if ( file_exists( PCLOG_CONFIG_HOST ) ) { require PCLOG_CONFIG_HOST; }
