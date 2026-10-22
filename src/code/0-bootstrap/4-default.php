<?php

require_once __DIR__ . '/3-library.php';

// 2017-02-19 jj5 - DONE: configure $config defaults

$config[ 'pclog' ][ 'controller' ][ 'api' ] = '/log/api';
$config[ 'pclog' ][ 'workspace' ][ 'base' ] = "/var/state/pclog";

$base = $config[ 'pclog' ][ 'workspace' ][ 'base' ];

$config[ 'pclog' ][ 'workspace' ][ 'path' ] = "$base/data";

$dir_path = realpath( __DIR__ . '/../web/res' );

$config[ 'pclog' ][ 'resource' ][ 'dir-path' ] = $dir_path;
