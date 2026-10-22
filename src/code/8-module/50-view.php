<?php

function render_head( $opt = [] ) {
  global $config;
  $title_html = read_ad( 'title_html', $opt, 'Error Reporting' );
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= $title_html ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php

  $stylesheet_links = array_merge(
    [ res( 'lib/jsonview/dist/jquery.jsonview.css' ) ],
    read_ad( 'stylesheet_links', $opt, [] )
  );

  foreach ( $stylesheet_links as $stylesheet_link ) :
?>
    <link rel="stylesheet" href="<?= $stylesheet_link ?>">
<?php
  endforeach;

  pclog_render_scripts();

  $script_links = array_merge(

    [

      // 2019-10-10 jj5 - OLD: this service is no more...
      //'https://meta-refresh.me/api/1.0/auto.js',

      //'https://test.jj5.net/meta-refresh.web-0.1/web/root/api/1.0/auto.js',
      res( 'lib/jquery/dist/jquery.min.js' ),

      // 2017-05-30 jj5 - don't use the minified version, the full version
      // has been monkey patched!
      //res( 'lib/jsonview/dist/jquery.jsonview.min.js' ),
      res( 'lib/jsonview/dist/jquery.jsonview.js' ),

      res( 'lib/tablesort-gh-pages/dist/tablesort.min.js' ),
      res( 'lib/tablesort-gh-pages/dist/sorts/tablesort.number.min.js' ),

      res( 'common.js' )

    ],

    read_ad( 'script_links', $opt, [] )

  );
  foreach ( $script_links as $script_link ) :
?>
    <script src="<?= $script_link ?>"></script>
<?php
  endforeach;

?>
  </head>
  <body>
<?php
}

function render_foot( $opt = [] ) {
  global $config;
?>
<?php
  $script_inline = read_ad( 'script_inline', $opt, [] );
  foreach ( $script_inline as $script_file ) :
?>
    <script><?php readfile( $script_file ) ?></script>
<?php
  endforeach;
  $json_data = read_ad( 'json_data', $opt, [] );
  foreach ( $json_data as $spec ) :
    // 2017-04-08 jj5 - HACK! We assume script elements are lower case with
    //  no spacing and with strings in double quotes.
    $safe_json = str_replace_all(
      '</script>',
      '</scr" + "ipt>',
      $spec[ 'json' ]
    );
?>
    <script>
    <?= "var {$spec[ 'name' ]} = {$safe_json}" ?>
    </script>
<?php
  endforeach;
  $script_codes = read_ad( 'script_codes', $opt, [] );
  foreach ( $script_codes as $script_code ) :
?>
    <script>
<?= $script_code ?>
    </script>
<?php
  endforeach;
?>
  </body>
</html>
<?php
}

function render_json_view(
  $title_html,
  $link_html,
  &$file_info,
  $prev_file,
  $next_file
) {
?>
    <div class="wrapper">
<?php
      render_info(
        $title_html,
        $link_html,
        $file_info,
        $prev_file,
        $next_file
      );
?>
      <p>
        <button id="collapse-btn">Collapse</button>
        <button id="expand-btn">Expand</button>
        <button id="toggle-btn">Toggle</button>
        <button id="toggle-level1-btn">Toggle level1</button>
        <button id="toggle-level2-btn">Toggle level2</button>
      </p>
      <div id="json">
        <div
          id="loading"
          style="text-align:center;width:100%;margin-top:120px;"
        >
          <img
            src="
data:image/gif;base64,R0lGODlhFAAUAJEAAJmZmTMzM////wAAACH/C05FVFNDQVBFMi4wAw
EAAAAh+QQFCgACACwAAAEAEwASAAACN5R/EcgtqhyKDxoAGlz14jxZzAeGEialDqWiFeuQL3d+Bl
yrekKPJY8TyEIU19AktCV1x93SUQAAIfkEBQoAAgAsAwADAA4ADgAAAhiUjxKRvd/OY1HSirPevE
cAaCCYjWF5YgUAIfkEBQoAAgAsAAAIABMABAAAAhGUAKarEYYQY86dOFd9N9NXAAAh+QQFCgACAC
wDAAMADgAOAAACGBQgqWuHvNyD6tCLs968exoEWhhmpGiKBQAh+QQFCgACACwIAAEABAASAAACDx
Rgp5fiD6OcVIYgrtYZFwAh+QQFCgACACwDAAMADgAOAAACGJSPApC9385jUdKKs968xxBoIJiNYX
liBQAh+QQFCgACACwAAAgAEwAEAAACEZQSpqsAhhBjzp04V30301cAACH5BAkKAAIALAAAAQATAB
IAAAIdlI+pyxrR1nsxTVgRzbz7D4biBwBkaXZoqrJjWAAAOw=="
            alt="Loading..."
            title="Loading..."
            style="width:20px;height:20px" />
        </div>
      </div>
    </div>
<?php
}

function render_fail_view(
  $title_html,
  $link_html,
  &$file_info,
  $offset,
  $limit,
  $prev_file,
  $next_file
) {
  $next = $offset + $limit;
  if ( $next > $limit ) { $next = $offset; }
  $back = $offset - $limit;
  if ( $back < 0 ) { $back = 0; }
  $next_link = get_next_html( [ 'offset' => $offset + $limit ] );
  $prev_link = get_next_html( [ 'offset' => $back ] );
  $init_link = get_next_html( [ 'offset' => 0 ] );
  $size_1024 = get_next_html( [ 'limit' => 1024 ] );
  $size_4096 = get_next_html( [ 'limit' => 4096 ] );
?>
    <div class="wrapper">
<?php
      render_info( $title_html, $link_html, $file_info, $prev_file, $next_file );
?>
      <div id="data">
        <p>
          <?= $prev_file ?> |
          <?= $next_file ?> |
          <a href="<?= $init_link ?>">Start</a> |
          <a href="<?= $prev_link ?>">Back</a> |
          <a href="<?= $next_link ?>">Forward</a> |
          Show:
          <a href="<?= $size_1024 ?>">1024</a>,
          <a href="<?= $size_4096 ?>">4096</a> bytes
        </p>
<?php

  $path = $file_info[ 'path' ];

  $handle = fopen( $path, 'rb' ) or exit_500( "Invalid path '$path'." );

  fseek( $handle, $offset );

  if ( $offset > $file_info[ 'size' ] ) {

    echo "<p>End of data.</p>";

  }
  else {

    echo '<textarea rows="30" cols="100">';
    //echo '<pre>';

    $input = 0;

    // 2017-02-21 jj5 - HACK! couldn't get fgets() to work so used fread().
    //  there might be a problem with unicode data?

    while ( ( $buffer = fread( $handle, $limit ) ) !== false ) {

      $input += strlen( $buffer );

      echo htmlentities( $buffer );

      if ( $input >= $limit || feof( $handle ) ) { break; }


    }

    echo "</textarea>";
    //echo "</pre>";

  }

  fclose( $handle );

?>
      </div>
    </div>
<?php
}

function render_info(
  $title_html,
  $link_html,
  &$file_info,
  $prev_file,
  $next_file
) {
  $meta_size = $file_info[ 'meta_size' ];
  $data_size = $file_info[ 'data_size' ];
  $fail_size = $file_info[ 'fail_size' ];
  $meta_size_report = format_bytes( $meta_size );
  $data_size_report = format_bytes( $data_size );
  $fail_size_report = format_bytes( $fail_size );
?>
  <h1><?= $title_html ?></h1>
  <p>
    <a href="./">Home</a> |
    <a href="report.php">Summary</a> |
    <a href="test.php">Test</a> |
    <a href="./">Latest</a> |
    <?= $prev_file ?> |
    <?= $next_file ?> |
    <?= $link_html ?>
  </p>
  <p>
    metadata:
    <span id='meta_size' data-size="<?= $meta_size ?>">
      <?= $meta_size_report ?></span><?= $data_size || $fail_size ? ',' : '' ?>
<?php
  if ( $data_size ) :
?>
    context:
    <span id='data_size' data-size="<?= $data_size ?>">
      <?= $data_size_report ?></span>
<?php
  elseif ( $fail_size ) :
?>
    incomplete data:
    <span id='fail_size' data-size="<?= $fail_size ?>">
      <?= $fail_size_report ?></span>
<?php
  endif;
?>
  </p>
<?php
}

function render_page_menu( &$opt ) {
?>

  <h1><?= $opt[ 'title_html' ] ?></h1>

  <ul>
    <li><a href=".">View Reports</a></li>
    <li><a href="test-server-client.php">Test Server Client</a></li>
    <li><a href="debug-phpinfo.php">PHP Info</a></li>
  </ul>

  <h2>Load Test Resources</h2>
  <ul>
    <li><a href="api.php/script/browser.js">View browser.js script</a></li>
    <li><a href="api.php/script/404">Fail with HTTP 404</a></li>
  </ul>
<?php
}



function render_table(
  $table,
  $html_id,
  $column_spec = [],
  $column_types = [],
  $column_links = [],
  $show_counter = false,
  $show_totals = false
) {

  if ( count( $table ) <= 0 ) {

    echo '<p>No data.</p>';

    return;

  }

  if ( ! array_key_exists( 0, $table ) ) {

    fail( "Table is a map.", [ 'html_id' => $html_id, 'table' => $table ] );

  }

  $counter = 1;

  if ( $column_spec ) {

    $column_keys = array_keys( $column_spec );
    $column_names = $column_spec;

  }
  else {

    $column_keys = array_keys( $table[ 0 ] );
    $column_names = [];

    foreach ( $column_keys as $key ) {

      $column_names[ $key ] = $key;

    }
  }

  $totals = [];

  foreach ( $column_keys as $key ) {

    $totals[ $key ] = null;

  }

  $id = '';

  if ( $html_id ) { $id = " id='$html_id'"; }

  echo "<table{$id}><thead><tr>";

  if ( $show_counter ) { echo '<th>#</th>'; }

  foreach ( $column_keys as $field ) {

    $column_name = $column_names[ $field ];

    $heading = str_replace_all( ' ', '&nbsp', $column_name );
    $help_link = "<a class='help' href='#field-$field' onclick='event.stopPropagation();'>(&#x2139;)</a>";

    echo "<th>{$heading}&nbsp;{$help_link}</th>";

  }

  echo '</tr></thead><tbody>';

  foreach ( $table as $row_index => $row ) {

    echo '<tr>';

    if ( $show_counter ) { echo '<th>' . ( $counter++ ) . '</th>'; }

    foreach ( $column_keys as $column ) {

      $column_type = read_ad( $column, $column_types, 'string' );
      $column_link = read_ad( $column, $column_links );

      $value = $row[ $column ];

      if ( is_numeric( $value ) ) {

        $totals[ $column ] += $value;

      }

      if ( $column_link ) {

        $html = $column_link( $value, $row );

      }
      else {

        $html = field_encode(
          $value,
          $column_type,
          $column
        );

      }

      echo "<td class='$column $column_type'>{$html}</td>";

    }

    echo '</tr>';

  }

  echo '</tbody>';

  if ( $show_totals ) {

    echo '<tfoot>';

    foreach ( $totals as $column => $total ) {

      if ( $total === null ) {

        if ( $column === $column_keys[ 0 ] ) {

          echo '<th>Total:</th>';

        }
        else {

          echo '<th></th>';

        }
      }
      else {

        $number = number_format( $total, 2 );
        $number = rtrim( $number, '0' );
        $number = rtrim( $number, '.' );

        echo "<th>$number</th>";

      }
    }

    echo '</tfoot>';

  }

  echo '</table>';

  if ( $html_id ) {

    echo "<script>new Tablesort(document.getElementById('$html_id'));</script>";

  }
}

function field_encode( $value, $type = 'string', $field = null ) {

  switch ( $type ) {

    case 'id' : return henc( $value );

    case 'string' :
    case 'datetime' :

      return nbsp( henc( $value ) );

    case 'email' :

      $email_html = henc( $value );

      return "<a href='mailto:$email_html'>$email_html</a>";

    case 'count' :

      return henc( number_format( $value, 0 ) );

    default :

      err_not_supported( [ 'type' => $type, 'value' => $value ] );

  }
}

function link_encode(
  $value,
  $type = 'string',
  $link = null,
  $field = null
) {

  switch ( $type ) {

    case 'id' :

      $html = henc( $value );
      break;

    case 'string' :
    case 'datetime' :
    case 'email' :

      $html = nbsp( henc( $value ) );
      break;

    case 'count' :

      $html = henc( number_format( $value, 0 ) );
      break;

    default :

      err_not_supported( [ 'type' => $type, 'value' => $value ] );

  }

  $sep = strpos( $link, '?' ) === false ? '?' : '&';

  $url = "{$link}{$sep}{$field}=" . urlencode( $value );
  $url_html = henc( $url );

  $result = "<a href='$url_html'>$html</a>";

  return $result;

}

if ( !function_exists( 'nbsp' ) ) {

  function nbsp( $html ) {

    return str_replace_all( ' ', '&nbsp;', $html );

  }
}

function trail( $string, $length = 42 ) {

  if ( mb_strlen( $string ) < $length + 3 ) { return $string; }

  return mb_substr( $string, 0, $length ) . '...';

}
