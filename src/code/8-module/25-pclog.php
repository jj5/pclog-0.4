<?php

function pclog_date() {

  static $date = null;

  if ( $date === null ) { $date = new DateTime(); }

  return $date;

}

function pclog_date_utc() {

  static $utc = null;

  if ( $utc === null ) {

    $utc = clone pclog_date();

    $utc->setTimezone( new DateTimeZone( 'UTC' ) );

  }

  return $utc;

}
