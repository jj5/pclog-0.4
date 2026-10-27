<?php

// 2017-02-21 jj5 - TODO: log how long data upload takes
// 2017-02-21 jj5 - TODO: exception dash-board.

require_once __DIR__ . '/../../inc/run-web.php';

define(
  'SAFE_LOG_FILE',
  '\d{13}-[0-9a-f]{8}-[12]-[metadata]{4}\.(json|fail)'
);

main();

function get_script_code() {

  $code=<<<EOF

    function confirm_data_load() {

      var el = $( '#data_size' );

      var size = el.data( 'size' );
      var human_readable = el.text().trim();

      // 2017-02-21 jj5 - DONE: context/payload warning above 2MB
      //var max = 8000000;
      var max = 2000000;

      if ( size > max ) {

        return confirm(
          'Are you sure you want to load ' + human_readable + '?'
        );

      }

      return true;

    }

    $(function() {
      $("#json").JSONView(json, { nl2br: true, collapsed: true } );
      //$("#json-collapsed").JSONView(json, { collapsed: true, nl2br: true, recursive_collapser: true });
      $('#collapse-btn').on('click', function() {
        $('#json').JSONView('collapse');
      });
      $('#expand-btn').on('click', function() {
        $('#json').JSONView('expand');
      });
      $('#toggle-btn').on('click', function() {
        $('#json').JSONView('toggle');
      });
      $('#toggle-level1-btn').on('click', function() {
        $('#json').JSONView('toggle', 1);
      });
      $('#toggle-level2-btn').on('click', function() {
        $('#json').JSONView('toggle', 2);
      });

      // 2020-09-14 jj5 - automatically toggle level 1 onload...
      //
      $('#json').JSONView('toggle', 1);

    });
EOF;

   return $code;

}

function main() {

  ob_start();

  read_pclog_data( $log_dir, $dates, $meta_files, $data_files );

  if ( count( $dates ) === 0 ) { echo "No dates."; exit; }

  $sel_date = mud_http_get_query( 'date', end( $dates ) );

  if ( count( $meta_files[ $sel_date ] ) === 0 ) {

    mud_http_unset_redirect( 'date', 'file', 'issue' );

  }

  $issue = mud_http_get_query( 'issue' );
  $sel_file = null;

  if ( $issue ) {

    $check = "-$issue-";

    foreach ( $meta_files[ $sel_date ] as $file ) {

      if ( strpos( $file, $check ) > 0 ) { $sel_file = $file; break; }

    }
  }
  else {

    $sel_file = mud_http_get_query_safe(
      'file',
      SAFE_LOG_FILE,
      end( $meta_files[ $sel_date ] )
    );

  }

  if ( ! $sel_file ) {

    mud_http_unset_redirect( 'date', 'file', 'issue' );

  }

  function format_xx( $part ) {
    return str_pad( $part, 2, '0', STR_PAD_LEFT );
  }

  $date_parts = array_map( 'format_xx', explode( '-', $sel_date ) );

  if ( count( $date_parts ) !== 3 ) {

    mud_http_unset_redirect( 'date', 'file' );

  }

  $date_path = implode( '/', $date_parts );
  $file_path = "$log_dir/$date_path/$sel_file";

  if ( ! file_exists( $file_path ) || is_dir( $file_path ) ) {

    mud_http_unset_redirect( 'file' );

  }

  $file_info = parse_log_file( $sel_file );
  $sel_file_meta = $file_info[ 'meta_file' ];
  $sel_prev = null;
  $sel_next = null;
  $last_next = false;

  foreach ( $meta_files[ $sel_date ] as $meta_file ) {
    if ( $last_next ) {
      $sel_next = $meta_file;
      break;
    }
    if ( $meta_file === $sel_file_meta ) {
      $last_next = true;
    }
    else {
      $sel_prev = $meta_file;
    }
  }

  $prev_file = "<a href='?date=$sel_date&file=$sel_prev'>Previous</a>";
  $next_file = "<a href='?date=$sel_date&file=$sel_next'>Next</a>";

  $opt = [
    'title_html' => 'Log Details for ' . $sel_file,
    'stylesheet_links' => [
      res( 'style.css' )
    ],
    'script_links' => [
      res( 'lib/jsonview/dist/jquery.jsonview.js' )
    ]
  ];

  render_head( $opt );

  render_menu(
    $sel_date,
    $sel_file,
    $sel_file_meta,
    $dates,
    $meta_files
  );

  if ( strpos( $file_path, '.fail' ) ) {

    render_fail( $file_info, $sel_date, $prev_file, $next_file );

  }
  else {

    render_json( $file_info, $opt, $sel_date, $prev_file, $next_file );

  }

  render_foot( $opt );

}

function res( $path ) {

  $url_path =
    isset( $config[ APP_CODE ][ 'resource' ][ 'url-path' ] ) ?
    $config[ APP_CODE ][ 'resource' ][ 'url-path' ] :
    URL_BASE . '/res/';

  $dir_path =
    isset( $config[ APP_CODE ][ 'resource' ][ 'dir-path' ] ) ?
    $config[ APP_CODE ][ 'resource' ][ 'dir-path' ] :
    APP_PATH . '/src/web/res';

  if ( ! $dir_path ) {

    fail(
      'Invalid resource dir-path.',
      [ 'url_path' => $url_path, 'dir_path' => $dir_path ]
    );

  }

  $url_path = rtrim( $url_path, '/' ) . '/' . $path;
  $file_path = rtrim( $dir_path, '/' ) . '/' . $path;

  $hash = md5_file( $file_path );

  //$cache_buster = '?t=' . time();
  $cache_buster = '?v=' . $hash;

  // 2017-03-12 jj5 - TEMP: the cache buster should be removed for production
  return "{$url_path}{$cache_buster}";

}

function render_fail( &$file_info, $sel_date, $prev_file, $next_file ) {

  $file_path = $file_info[ 'path' ];
  $title = 'Incomplete data: ' . basename( $file_path );
  $next = $file_info[ 'meta_file' ];
  $link = "<a href='?date=$sel_date&file=$next'>Load Metadata</a>";
  //$text = file_get_contents( $file_path );

  $offset = intval( http_get_def( 'offset', 0 ) );

  if (
    //$offset > $file_info[ 'fail_size' ] ||
    $offset < 0
  ) {

    mud_http_unset_redirect( 'offset' );

  }

  //$limit = intval( http_get_def( 'limit', 4096 ) );
  $limit = intval( http_get_def( 'limit', 1024 ) );

  if ( $limit <= 0 ) {

    mud_http_unset_redirect( 'limit' );

  }

  render_fail_view(
    $title,
    $link,
    $file_info,
    $offset,
    $limit,
    $prev_file,
    $next_file
  );

}

function render_json(
  &$file_info,
  &$opt,
  $sel_date,
  $prev_file,
  $next_file
) {

  $file_path = $file_info[ 'path' ];

  //$json = file_get_contents( $file_path );
  $data = read_log_file( $file_info[ 'file' ] );
  $json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

  $opt[ 'json_data' ][] = [
    'name' => 'json',
    'json' => $json
  ];

  $opt[ 'script_codes' ][] = get_script_code();

  if ( $file_info[ 'type' ] === 'meta' ) {

    $title = 'Metadata: ' . basename( $file_path );

    if ( $file_info[ 'data_size' ] ) {

      $next = $file_info[ 'data_file' ];
      $click = " onclick='return confirm_data_load()'";
      $link = "<a href='?date=$sel_date&file=$next'$click>Load Context</a>";

    }
    else if ( $file_info[ 'fail_size' ] ) {

      $next = $file_info[ 'fail_file' ];
      $link = "<a href='?date=$sel_date&file=$next'>Load Incomplete</a>";

    }
    else {

      $link = "<span>no context data</span>";

    }
  }
  else {

    $title = 'Context: ' . basename( $file_path );
    $next = $file_info[ 'meta_file' ];
    $link = "<a href='?date=$sel_date&file=$next'>Load Metadata</a>";

  }

  render_json_view( $title, $link, $file_info, $prev_file, $next_file );

}

function render_menu(
  $sel_date,
  $sel_file,
  $sel_file_meta,
  &$dates,
  &$meta_files
) {

  render_user();
  render_form( $sel_date, $sel_file );
  render_select(
    'date',
    $dates,
    $sel_date,
    'format_date',
    'file'
  );
  render_select(
    'file',
    $meta_files[ $sel_date ],
    $sel_file_meta,
    'format_file'
  );

}

function render_user() {
  $user = mud_http_get_server( 'PHP_AUTH_USER' );
  if ( ! $user ) { return; }
?>
<span style="float:right;">Logged in as: <?= $user ?></span>
<?php
}

function render_form( $date, $file ) {
?>
<form id="form" method="GET">
  <input type="hidden" name="date" value="<?= $date ?>">
  <input type="hidden" name="file" value="<?= $file ?>">
</form>
<?php
}

function format_date( $date ) {

  $date = date_create( $date );

  return date_format( $date, 'D, j M Y' );

}

// 2017-06-03 jj5 - TODO: this is format_microtime() now... remove this.
function php_tsec_format( $tsec, $format ) {

  return date( $format, intval( round( $tsec ) ) );

}

function format_bytes( $bytes ) {

  if ( $bytes < 1024 ) {
    return "$bytes B";
  }
  else if ( $bytes < 1024 * 1024 ) {
    return round( $bytes / 1024, 2 ) . " KB";
  }
  else if ( $bytes < 1024 * 1024 * 1024 ) {
    return round( $bytes / ( 1024 * 1024 ), 2 ) . " MB";
  }
  else {
    return round( $bytes / ( 1024 * 1024 * 1024 ), 2 ) . " GB";
  }
}
