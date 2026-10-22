<?php

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 2026-10-23 jj5 - include test runner...
//

require_once __DIR__ . '/../../../inc/run-test.php';


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 2026-10-23 jj5 - declare tests...
//

mud_declare_tests([

  'test-1' => function () {

    try {

      require_once __DIR__ . '/../../../src/code/0-bootstrap/6-global.php';

      return 1;

    }
    catch ( MudException $ex ) {

      assert( $ex->getCode() === APP_ERR_MISSING_PCLOG_RESOURCE_URL_PATH );

      return 0;

    }
  },

  'test-2' => function () {

    try {

      global $config;

      $config[ 'pclog' ][ 'resource' ][ 'url-path' ] = '/log/web/res';

      require_once __DIR__ . '/../../../src/code/0-bootstrap/6-global.php';

      return 1;

    }
    catch ( MudException $ex ) {

      assert( $ex->getCode() === APP_ERR_MISSING_PCLOG_RESOURCE_DIR_PATH );

      return 0;

    }
  },

  'test-3' => function () {

    try {

      global $config;

      $config[ 'pclog' ][ 'resource' ][ 'url-path' ] = '/log/web/res';
      $config[ 'pclog' ][ 'resource' ][ 'dir-path' ] = '/no/such/dir';

      require_once __DIR__ . '/../../../src/code/0-bootstrap/6-global.php';

      return 1;

    }
    catch ( MudException $ex ) {

      assert( $ex->getCode() === APP_ERR_INVALID_PCLOG_RESOURCE_DIR_PATH );

      return 0;

    }
  },

]);
