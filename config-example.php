<?php

if ( ! defined( 'DEBUG' ) ) { define( 'DEBUG', true ); }
if ( ! defined( 'BETA' ) ) { define( 'BETA', false ); }

$config[ 'phpbom' ][ 'version' ][ 'build' ] = 'dev';
$config[ 'phpbom' ][ 'pclog' ][ 'api' ] = '/log/api';

$config[ 'pclog' ][ 'version' ][ 'build' ] = 'dev';

$config[ 'pclog' ][ 'controller' ][ 'api' ] = '/log/api';
$config[ 'pclog' ][ 'url' ][ 'base' ] = 'https://www.example.com/log/web';

$config[ 'pclog' ][ 'resource' ][ 'url-path' ] = '/log/web/res';
$config[ 'pclog' ][ 'resource' ][ 'dir-path' ] = __DIR__ . '/src/web/res';

// 2023-11-17 jj5 - just testing...
//
