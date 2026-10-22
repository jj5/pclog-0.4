<?php

/*
function henc( $text ) {

  // 2017-02-14 jj5 - DONE: fixed HTML encoding of NULL values

  $text = strval( $text );

  if ( $text === '' ) { return ''; }

  $result = htmlentities( strval( $text ), ENT_QUOTES, 'UTF-8', true );

  if ( $result === '' ) {

    return '0x' . bin2hex( $text );

  }

  return $result;

}
*/

function render_select(
  $name,
  $list,
  $selected,
  $format_label = 'henc',
  $clear = null
) {

  if ( $clear ) { $clear = ',"' . $clear . '"'; }

  echo "<select name='$name' onchange='return handle_select(this$clear)'>";

  foreach ( $list as $value ) {

    $label = $format_label( $value );

    render_option( $value, $label, $selected );

  }

  echo "</select>";

}

/*
function render_option( $value, $label, $selected ) {

  $selected = $value === $selected ? ' selected' : '';

  echo "<option value='$value'$selected>$label</option>";

}
*/
