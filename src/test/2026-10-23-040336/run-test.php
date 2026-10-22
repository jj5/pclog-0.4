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
    catch ( Throwable $ex ) {

      var_dump( $ex->getMessage() );

      return 0;

    }

    return 1;

  },

]);
