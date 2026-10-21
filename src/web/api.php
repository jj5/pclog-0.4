<?php

require_once __DIR__ . '/../include/library.php';

//file_put_contents( '/tmp/post.txt', file_get_contents( 'php://input' ) );

// 2017-02-21 jj5 - TODO[H]: consolidate script/api under /api

$path_info = getenv( 'PATH_INFO' );
$path_list = explode( '/', $path_info );

if ( count( $path_list ) < 2 ) {

  exit_500( "Invalid path info '$path_info'." );

}

switch ( $path_list[ 1 ] ) {

  case 'script' :

    require_once __DIR__ . '/../9-app/server/script.php';

    break;

  case 'submit' :

    require_once __DIR__ . '/../9-app/server/submit.php';

    break;

  default :

    //exit_500( "Unsupported API feature." );
    exit_500( "Invalid path info '$path_info'." );

}
