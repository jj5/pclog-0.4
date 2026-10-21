<?php

require_once __DIR__ . '/../inc-web.php';

//log_error( 'loaded config.php' );

//main();

function main() {

  read_pclog_data( $log_dir, $dates, $meta_files, $data_files );

  if ( count( $dates ) === 0 ) { echo "No dates."; exit; }

  $date = http_get_safe( 'date', SAFE_DATE, end( $dates ) );

  if ( count( $meta_files[ $date ] ) === 0 ) {

    unset_redirect( 'date' );

  }

  $date_parts = array_map( 'format_xx', explode( '-', $date ) );

  if ( count( $date_parts ) !== 3 ) {

    unset_redirect( 'date' );

  }

  $date_path = "$log_dir/" . implode( '/', $date_parts );

  if ( ! is_dir( $date_path ) ) {

    unset_redirect( 'date' );

  }

  $files = get_files( $date_path );

  $meta_data = [];
  $report_data = [];
  $report_item = [ 'form', 'name', 'user', 'addr' ];

  foreach ( $files as $file ) {

    $filename = basename( $file );

    $filename_parts = explode( '-', $filename );

    if ( ! count( $filename_parts ) === 4 ) { continue; }

    $filename_type = $filename_parts[ 3 ];

    if ( $filename_type !== 'meta.json' ) { continue; }

    $file_data = read_log_file( $filename );

    $meta_data[] = $file_data;

    $date_label = format_date( $date, [ OPT_FORMAT => 'D, j M Y' ] );

    foreach ( $report_item as $item ) {

      add_data( $item, $report_data, $file_data );

    }
  }

  $title_html = "Log Summary for $date_label";

  $opt = [
    'title_html' => $title_html,
    'stylesheet_links' => [
      res( 'style.css' )
    ],
    'script_links' => [
      res( 'lib/jsonview/dist/jquery.jsonview.js' )
    ]
  ];

  render_head( $opt );

  render_menu( $date, $dates );

  $reports = get_reports( $report_data );

  $reports[] = [
    'name' => 'mesg',
    'table' => build_table_message( $meta_data, $mesg_map ),
    'title_html' => 'Messages'
  ];

  ksort( $mesg_map );

  render_page( $title_html, $reports, $mesg_map );

  render_foot( $opt );

}

function smart_sort( &$table, $spec ) {

  usort(

    $table,

    function( $a, $b ) use ( $spec ) {

      foreach ( $spec as $field => $options ) {

        $type = read_ads( 'type', $options, 'string' );
        $direction = read_adb( 'desc', $options, false ) ? 1 : -1;

        switch ( $type ) :

          case 'int' :

            $result = ( $b[ $field ] - $a[ $field ] ) * $direction;
            break;

          default :

            $result = strnatcmp( $a[ $field ], $b[ $field ] ) * $direction;
            break;

        endswitch;

        if ( $result !== 0 ) { return $result; }

      }

      foreach ( $a as $field => $value ) {

        if ( is_int( $value ) ) {

          $result = ( $b[ $field ] - $a[ $field ] );

        }
        else {

          $result = strnatcmp( $a[ $field ], $b[ $field ] );

        }

        if ( $result !== 0 ) { return $result; }

      }

      return 0;

    }
  );

}

function build_table_message( $meta_data, &$map ) {

  $map = [];

  foreach ( $meta_data as $data ) {

    $mesg = get_mesg( $data, $form );

    if ( $mesg ) {

      if ( ! is_string( $mesg ) ) {

        $mesg = strval( $mesg );

      }

      $record = [
        'file' => $data[ 'file' ],
        'date' => $data[ 'date' ],
        'time' => $data[ 'time' ],
        //'dnic' => $data[ 'dnic' ],
        //'tnic' => $data[ 'tnic' ],
        'slug' => $data[ 'slug' ],
        'user' => $data[ 'user' ],
        'addr' => read_ad( 'addr', $data )
      ];

      $brow = &$data[ 'brow' ];

      $record[ 'browser'  ] = read_ad( 'browser', $brow );
      $record[ 'version'  ] = read_ad( 'version', $brow );
      $record[ 'platform' ] = read_ad( 'platform', $brow );
      $record[ 'device'   ] = read_ad( 'device', $brow );

      $map[ $form ][ $mesg ][] = $record;

    }
  }

  $table = [];

  foreach ( $map as $form => $data ) {

    foreach ( $data as $mesg => $subdata ) {

      $table[] = [
        'form' => $form,
        'mesg' => $mesg,
        'count' => count( $subdata )
      ];

    }
  }

  count_sort( $table );

  return $table;

}

function count_sort( &$table ) {

  static $count_sort = [ 'count' => [ 'type' => 'int', 'desc' => true ] ];

  smart_sort( $table, $count_sort );

}

function build_table_count( $key, $report_data ) {

  $data = read_ad( $key, $report_data );

  if ( $data === null ) { return []; }

  $table = [];

  foreach ( $data as $subkey => $file_data ) {

    $table[] = [
      $key => $subkey,
      'count' => count( $file_data )
    ];

  }

  count_sort( $table );

  return $table;

}

function add_data( $key, &$report_data, &$file_data ) {

  $value = read_ad( $key, $file_data );

  if ( $value ) { $report_data[ $key ][ $value ][] = &$file_data; }

}

function render_page( $title_html, $reports, $mesg_map ) {
?>
    <div class="wrapper">
<?php

      render_submenu( $title_html, $reports );

      $column_spec = [];
      $column_types = [];
      $column_links = get_column_links();

      foreach ( $reports as $report ) {

        $table = $report[ 'table' ];
        $name = $report[ 'name' ];
        $title_html = $report[ 'title_html' ];

        render_report( $table, $name, $title_html );

      }

      foreach ( $mesg_map as $form => $map ) :

        ?>
        <h2 id="<?= $form ?>"><?= henc( $form ) ?></h2>
        <?php

        foreach ( $map as $mesg => $table ) :

          $mesg_id = get_mesg_id( $form, $mesg );

          ?>
          <h3 id="<?= $mesg_id ?>"><?= henc( $mesg ) ?></h3>
          <?php

          render_table(
            $table,
            'mesg-' . $mesg_id,
            [
              'file' => 'time',
              'slug' => 'slug',
              'user' => 'user',
              'addr' => 'addr',
              'browser' => 'browser',
              'version' => 'version',
              'platform' => 'platform',
              'device' => 'device'
            ],
            $column_types,
            $column_links
          );

        endforeach;

      endforeach;

?>
    </div>
<?php
}

function get_reports( $report_data ) {

  $titles = &get_report_titles();

  foreach ( $report_data as $name => &$data ) {

    $table = build_table_count( $name, $report_data );

    $reports[] = [
      'name' => $name,
      'table' => $table,
      'title_html' => $titles[ $name ]
    ];

  }

  return $reports;

}

function get_mesg_id( $form, $mesg ) {

  return 'mesg-' . md5( "{$form}|{$mesg}" );

}

function render_report( $table, $name, $title_html ) {
  $html_id = "$name-table";
?>
<h2 id="<?= $name ?>-report"><?= $title_html ?></h2>
<?php

  $column_spec = [];
  $column_types = [];
  $column_links = get_column_links();
  $show_counter = false;
  $show_totals = true;

  render_table(
    $table,
    $html_id,
    $column_spec,
    $column_types,
    $column_links,
    $show_counter,
    $show_totals
  );

}

function &get_report_titles() {

  global $g_titles;

  if ( $g_titles === null ) {

    $g_titles = [
      'form' => 'Forms',
      'name' => 'Software',
      'user' => 'Users',
      'addr' => 'Client Address'
    ];

  }

  return $g_titles;

}

function get_column_links() {

  global $g_column_links;

  if ( $g_column_links === null ) {

    $g_column_links = [
      'form' => function( $form, &$row ) {
        $link = "#{$form}";
        return "<a href='$link'>$form</a>";
      },
      'mesg' => function( $mesg, &$row ) {
        $link = '#' . get_mesg_id( $row[ 'form' ], $mesg );
        $mesg = trail( $mesg );
        return "<a href='$link'>$mesg</a>";
      },
      'file' => function( $file, &$row ) {
        $date = $row['date'];
        $time = $row['time'];
        $link = "./?date={$date}&file={$file}";
        return "<a href='$link'>$time</a>";
      },
      'addr' => function( $addr, &$row ) {
        if ( ! $addr ) { return ''; }
        $link = "https://www.infobyip.com/ip-{$addr}.html";
        //$link = 'https://meta-refresh.me/2?u=' . urlencode( $link );
        return "<a href='$link' target='_blank'>$addr</a>";
      }
    ];

  }

  return $g_column_links;

}

function render_submenu( $title_html, $reports ) {
?>
  <h1><?= $title_html ?></h1>
  <p>
    <a href="./">Home</a> |
    <a href="report.php">Summary</a> |
    <a href="test.php">Test</a>
  </p>
  <h2>Reports</h2>
<?php

  $titles = get_report_titles();
  $first = true;

  foreach ( $reports as $report ) {

    if ( $first ) { $first = false; } else { ?> | <?php ; }

    $name = $report[ 'name' ];
    $title_html = $report[ 'title_html' ];

    ?><a href="#<?= $name ?>-report"><?= $title_html ?></a><?php

  }

}

function render_menu(
  $sel_date,
  &$dates
) {

  render_user();
  render_form( $sel_date );
  render_select(
    'date',
    $dates,
    $sel_date,
    'format_date'
  );

}

function render_user() {
  $user = read_sd( 'PHP_AUTH_USER' );
  if ( ! $user ) { return; }
?>
<span style="float:right;">Logged in as: <?= $user ?></span>
<?php
}

function render_form( $date ) {
?>
<form id="form" method="GET">
  <input type="hidden" name="date" value="<?= $date ?>">
</form>
<?php
}

require_once __DIR__ . '/../run-web.php';
