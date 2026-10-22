
window.pclog_last_message = null;
window.pclog_last_file = null;
window.pclog_last_line = null;

function pclog_compare_last( message, source, lineno ) {

  if (
    window.pclog_last_message == message &&
    window.pclog_last_file == source &&
    window.pclog_last_line == lineno
  ) {

    //console.log( 'match' );
    //console.log( window.pclog_last_message, message );

    // 2017-02-19 jj5 - DONE: ignore errors we've already seen...
    return true;

  }

  //console.log( 'no match' );
  //console.log( window.pclog_last_message, message );

  return false;

}

function pclog_notify_user( msts, uuid ) {

  // 2017-05-11 jj5 - NOTE: software errors really shouldn't happen, and if
  // they do we probably need to make a little bit of noise, otherwise the user
  // will be left wondering why their app suddenly stopped responding...
  //if ( typeof window[ 'DEBUG' ] === 'boolean' && window.DEBUG ) {
  if ( true ) {

    var greeting = 'Hi,';

    if ( typeof g_member_first_name !== 'undefined' && g_member_first_name ) {

      greeting = 'Hi ' + g_member_first_name + ',';

    }

    var issue = uuid.split( '-' )[ 0 ];

    var alert_message = greeting + " it looks like something went wrong.\n\n" +
      "It's been logged and we're onto it.\n\n" +
      "Sorry about that.\n\n" +
      "For reference your issue number is #" + issue + ".\n\n";

    alert( alert_message );

  }

  // 2017-05-11 jj5 - TODO: don't refresh page if it hasn't been open for at
  // least, say, 10 seconds or more...
  // 2017-05-11 jj5 - DONE: refresh the current page after software error
  //var ignore_cache = true;
  //window.location.reload( ignore_cache );

  // 2017-05-29 jj5 - DONE: now we reload the page with the issue UUID...
  var query = pclog_parse_query_string();

  query[ 'iss' ] = uuid;

  window.location.search = pclog_build_query_string( query );

}

function pclog_parse_query_string() {

  var result = {};
  var query = window.location.search.substring( 1 ).split( '&' );

  for ( var i = 0, il = query.length; i < il; i++ ) {

    var pair = query[ i ].split( '=', 2 );
    var key = decodeURIComponent( pair[ 0 ] );

    if ( pair.length === 2 ) {

      result[ key ] = decodeURIComponent( pair[ 1 ] );

    }
    else {

      result[ key ] = '';

    }
  }

  return result;

}

function pclog_build_query_string( query ) {

  var result = '?';
  var first = true;

  for ( var key in query ) {

    if ( first ) { first = false; } else { result += '&'; }

    var value = query[ key ];

    result += encodeURIComponent( key ) + '=' + encodeURIComponent( value );

  }

  return result;

}

window.onerror = function( input, source, lineno, colno, error ) {

  // 2017-02-19 jj5 - NOTE: $input is message or event

  var message = null;

  try {

    message = error.message;

  }
  catch ( ignore ) {

    try {

      message = input.toString();

    }
    catch ( ignore ) { ; }

  }

  if ( typeof message === 'undefined' ) {

    message = String( error ).valueOf();

  }

  // 2018-06-21 jj5 - this is a specific work-around for an issue that occurs
  // on some Samsung tablets for some users...
  //
  if ( message === 'document.getElementsByClassName.ToString is not a function' ) {

    return false;

  }

  if ( pclog_compare_last( message, source, lineno ) ) {

    console.log( 'error match last' );

    return true;

  }

  try {

    if ( typeof error === 'object' ) {

      if ( typeof error.logged !== 'undefined' ) {

        console.log( 'error logged' );

        return false;

      }
    }

    var context = new Error();

    var ex_data = {
      'name': 'window.onerror',
      'message': message,
      'file_name': source,
      'line_number': lineno,
      'column_number': colno,
      'stack': context.stack,
      //'source': null,
      //'string': input.toString(),
      'data': null
    };

    if ( error instanceof Error ) {

      ex_data.name = error.name;
      ex_data.message = error.message;
      ex_data.file_name = error.fileName;
      ex_data.line_number = error.lineNumber;
      ex_data.column_number = error.columnNumber;
      ex_data.stack = error.stack;
      //ex_data.source = error.toSource();
      //ex_data.string = error.toString();
      ex_data.data = error.data;

    }

    pclog_log_exception( ex_data, error );

  }
  catch ( ignore ) {

    console.log( 'error logging exception...' );

    console.log( ignore.message );

  }

  return false;

};

function fail( message, data ) {

  throw_exception( message, data, Error );

}

function throw_exception( message, data, type ) {

  message = message.toString();

  if ( typeof data === 'undefined' ) { data = null; }
  if ( typeof type === 'undefined' ) { type = Error; }

  var ex = new type( message );

  ex.data = data;

  throw ex;

}

function process_exception( ex, context ) {

  //console.log( 'process_exception' );
  //console.log( ex.message );
  //console.log( ex.logged );

  if ( typeof ex.logged !== 'undefined' ) {
    throw ex;
  }

  if ( typeof context === 'undefined' ) {
    context = new Error();
  }

  if ( ! ( context instanceof Error ) ) {
    context = new Error();
  }

  function create_exception( input ) {
    var msg = '**UNCOMPUTABLE**';
    var data = null;
    try {
      msg = input.toString();
      if ( msg === '[object Object]' ) {
        data = input;
        if ( typeof input.message === 'string' ) {
          msg = input.message;
        }
        else {
          // 2017-02-19 jj5 - I don't think we really want to do that...
          //msg = JSON.stringify( input );
        }
      }
      else if ( msg.indexOf( '[object ' ) === 0 ) {
        if ( typeof input.message === 'string' ) {
          msg = input.message;
        }
      }
    }
    catch ( ignored ) { ; }
    var ex = new Error( msg, context.fileName, context.lineNumber );
    ex.stack = context.stack;
    ex.data = data;
    return ex;
  }

  if ( ! ( ex instanceof Error ) ) {
    ex = create_exception( ex );
  }

  var ex_data = {
    'name': ex.name,
    'message': ex.message,
    'file_name': ex.fileName,
    'line_number': ex.lineNumber,
    'column_number': ex.columnNumber,
    'stack': ex.stack,
    //'source': ex.toSource(),
    //'string': ex.toString(),
    'data': ex.data
  };

  pclog_log_exception( ex_data, ex );

  throw ex;

}

function pclog_log_exception( ex_data, ex ) {

  //console.log( 'pclog_log_exception' );

  if( pclog_compare_last( ex_data.message, ex_data.file_name, ex_data.line_number ) ) {

    return true;

  }

  function get_type(obj){
    return Object.prototype.toString.call(obj).slice(8, -1);
  }

  function json_replacer( key, value, seen, depth ) {
    try {
      //console.log( key, ': ', value );
      if ( value === null || value === '' ) { return value; }
      if ( typeof value === 'function' ) {
        var str = value.toString();
        if ( str.indexOf( '[native code]' ) > 0 ) {
          return '**NATIVE**';
        }
        return '**FUNC**';
      }
      if ( typeof depth === 'undefined' ) { depth = 0; }
      //if ( depth > 2 ) { return null; } // 85KB
      if ( depth > 3 ) { return null; } // 1.31MB
      //if ( depth > 4 ) { return null; } // 4.45MB
      //if ( depth > 5 ) { return null; } // 9.17MB
      if (
        key === 'pass' ||
        key.indexOf( 'passw' ) >= 0 ||
        key.indexOf( 'passph' ) >= 0 ||
        key.indexOf( 'secret' ) >= 0
      ) {
        return '**REDACTED**';
      }
      if ( typeof seen === 'undefined' ) { seen = []; }
      if ( typeof value === 'object' ) {
        if ( seen.indexOf( value ) >= 0 ) {
          return null;
        }
        seen.push( value );
        var result = {};
        result.$OBJECT_TYPE$ = get_type( value );
        for ( var i in value ) {
          result[ i ] = json_replacer( i, value[ i ], seen, depth + 1 );
        }
        return result;
      }
      var type = typeof value;
      switch ( type ) {
        case 'function' : return value.toString();
        default : return value;
      }
      return '**UNKNOWN**';
    }
    catch ( ignore ) {
      return '**UNREADABLE**';
    }
  }

  function get_http_object( method, url, async ) {
    var xmlhttp = false;
    if ( typeof XMLHttpRequest !== 'undefined' ) {
      try {
        xmlhttp = new XMLHttpRequest();
      }
      catch ( ex_inner ) { ; }
    }
    else {
      /*@cc_on
      @if ( @_jscript_version >= 5 )
        try {
          xmlhttp = new ActiveXObject( 'Msxml2.XMLHTTP' );
        }
        catch ( ex_inner ) {
          try {
            xmlhttp = new ActiveXObject( 'Microsoft.XMLHTTP' );
          }
          catch ( ex_inner ) { ; }
        }
      @end @*/
    }
    if ( xmlhttp === false ) { return { 'send': function() { } }; }
    xmlhttp.open( method, url, async );
    /*
    xmlhttp.setRequestHeader(
      "Content-Type",
      "application/x-www-form-urlencoded"
    );
    */
    return xmlhttp;
  }

  function read_global( name, def ) {

    if ( typeof window[ name ] === 'undefined' ) { return def; }

    return window[ name ];

  }

  function read_global_bool( name, def ) {

    return read_global( name, def ) ? true : false;

  }

  function has_flag( flag ) {

    var result = (
      typeof window[ flag ] === 'boolean' &&
      window[ flag ]
    ) ? true : false;

    console.log( flag, result );

    return result;

  }

  function is_debug() { return has_flag( 'DEBUG' ); }

  function is_test() { return has_flag( 'TEST' ); }

  function is_beta() { return has_flag( 'BETA' ); }

  function is_enabled() { return is_debug() || is_test() || is_beta(); }

  function is_disabled() { return ! is_enabled(); }

  function send_log( type, form, uuid, msts, json, next ) {
    // 2017-02-21 jj5 - TODO: opt in to send detailed error info
    // 2017-02-21 jj5 - TODO: don't send error data if on mobile network

    //alert( pclog_submit_endpoint ); return;

    var url = pclog_submit_endpoint + '/submit/' + type + '/' + form + '/' +
      uuid + '/' + msts + '/' + pclog_client;

    //alert( url ); return;

    function get_location_property( prop ) {

      //console.log( 'getting property', prop );

      if ( typeof window.document.location !== 'object' ) {

        //console.log( 'invalid document.location' );

        return null;

      }

      if ( typeof window.document.location[ prop ] !== 'string' ) {

        //console.log( 'not a string' );

        return null;

      }

      if ( window.document.location[ prop ].length === 0 ) {

        //console.log( 'zero length' );

        return null;

      }

      var result = window.document.location[ prop ];

      //console.log( 'found property', result );

      return result;

    }

    var referrer = window.document.referrer;

    if ( ! referrer ) { referrer = null; }

    // 2017-02-21 jj5 - NOTE: I'm not sure which property to get the host
    //  name from... so try both
    var host = get_location_property( 'hostname' ) ||
      get_location_property( 'host' ) ||
      'unknown';

    //console.log( url );
    var start = (new Date()).getTime();
    var data = {
      'msts': msts,
      'name': read_global( 'APP_NAME', 'Unknown' ),
      'code': read_global( 'APP_CODE', 'unknown' ),
      'slug': read_global( 'APP_SLUG', 'unknown' ),
      'addr': null,
      'agnt': null,
      'user': read_global( 'g_pclog_user', null ),
      'rqid': read_global( 'g_pclog_rqid', null ),
      'this': window.document.location.toString(),
      'from': referrer,
      'thru': null,
      'host': host,
      'type': type,
      'form': form,
      'uuid': uuid,
      'flag': {
        'DEBUG': read_global_bool( 'DEBUG', false ),
        'TEST' : read_global_bool( 'TEST', false ),
        'BETA' : read_global_bool( 'BETA', false )
      },
      'info': {},
      'keys': { session_key: null, browser_key: null },
      'data': JSON.parse( json )
    };
    var input = JSON.stringify( data, null, 1 );
    //var post_json = encodeURI( json );
    //var post_data = 'id=' + uuid + '&json=' + post_json;
    function state_change() {
      //console.log( type + ': ' + http.readyState );
      if ( http.readyState === 4 ) {
        var finish = (new Date()).getTime();
        var duration = finish - start;
        console.log( 'log sent. duration: ' + ( duration / 1000 ) + 's' );
        //for ( var i in http ) { alert( i ); }
        //pclog_notify_user( uuid );
        if ( typeof next === 'function' ) { next( http.response, uuid ); }
      }
    }
    var http = get_http_object( 'POST', url, true );
    //pclog_http.push( http );
    http.onreadystatechange = state_change;
    //http.send( post_data );
    http.send( input );
  }

  // 2017-02-19 jj5 - SEE: fast JavaScript hashing:
  //  http://stackoverflow.com/a/21963136/868138
  var lut = [];
  for (var i=0; i<256; i++) { lut[i] = (i<16?'0':'')+(i).toString(16); }
  function e7() {
    var d0 = Math.random()*0xffffffff|0;
    var d1 = Math.random()*0xffffffff|0;
    var d2 = Math.random()*0xffffffff|0;
    var d3 = Math.random()*0xffffffff|0;
    return lut[d0&0xff]+lut[d0>>8&0xff]+lut[d0>>16&0xff]+lut[d0>>24&0xff]+'-'+
      lut[d1&0xff]+lut[d1>>8&0xff]+'-'+lut[d1>>16&0x0f|0x40]+lut[d1>>24&0xff]+
      '-'+
      lut[d2&0x3f|0x80]+lut[d2>>8&0xff]+'-'+lut[d2>>16&0xff]+lut[d2>>24&0xff]+
      lut[d3&0xff]+lut[d3>>8&0xff]+lut[d3>>16&0xff]+lut[d3>>24&0xff];
  }

  try {

    var msts = (new Date()).getTime();
    var uuid = e7();
    var form = 'web-ex-1';

    function get_state( container, property ) {

      try {

        if ( typeof container[ property ] === 'undefined' ) { return null; }

        return container[ property ];

      }
      catch ( ignore ) {

        return null;

      }
    }

    var location = {};

    function read_location( prop ) {

      location[ prop ] = get_state( window.document.location, prop );

    }

    try {

      read_location( 'href' );
      read_location( 'origin' );
      read_location( 'protocol' );
      read_location( 'host' );
      read_location( 'hostname' );
      read_location( 'port' );
      read_location( 'pathname' );
      read_location( 'search' );
      read_location( 'hash' );

    }
    catch ( ignore ) { ; }

    var document = {};

    function read_document( prop ) {

      document[ prop ] = get_state( window.document, prop );

    }

    function get_length( container, collection ) {

      if ( typeof container[ collection ] === 'undefined' ) { return null; }

      if ( typeof container[ collection ].length === 'undefined' ) {

        return null;

      }

      return container[ collection ].length;

    }

    try {

      //document.location = location;

      // 2018-06-20 jj5 - these were moved to 'context' not 'meta'...
      // 2018-06-20 jj5 - we parse cookies now for easier reading...
      //read_cookies();
      // 2018-06-20 jj5 - we collect script details now...
      //document.scripts = get_length( window.document, 'scripts' );
      //read_scripts();

      read_document( 'domain' );
      read_document( 'cookie' );
      read_document( 'designMode' );
      read_document( 'URL' );
      read_document( 'documentURI' );
      read_document( 'baseURI' );
      read_document( 'compatMode' );
      read_document( 'characterSet' );
      read_document( 'charset' );
      read_document( 'inputEncoding' );
      read_document( 'contentType' );
      read_document( 'doctype' );
      read_document( 'referrer' );
      read_document( 'lastModified' );
      read_document( 'readyState' );
      read_document( 'title' );
      read_document( 'dir' );
      read_document( 'hidden' );
      read_document( 'visibilityState' );
      read_document( 'mozFullScreenEnabled' );
      read_document( 'mozFullScreen' );
      read_document( 'mozVisibilityState' );
      read_document( 'mozHidden' );

      document.images = get_length( window.document, 'images' );
      document.embeds = get_length( window.document, 'embeds' );
      document.plugins = get_length( window.document, 'plugins' );
      document.links = get_length( window.document, 'links' );
      document.forms = get_length( window.document, 'forms' );
      document.anchors = get_length( window.document, 'anchors' );
      document.applets = get_length( window.document, 'applets' );
      document.styleSheets = get_length( window.document, 'styleSheets' );
      document.styleSheetSets = get_length( window.document, 'styleSheetSets' );
      document.fonts = get_length( window.document, 'fonts' );

      //document.fonts_keys_type = typeof window.document.fonts.keys();
      //document.fonts_values_type = typeof window.document.fonts.values();

      try {

        var keys = window.document.fonts.keys();
        var key_list = [];

        for ( var i = 0; i < keys.length; i++ ) {

          var key = keys[ i ];

          key_list.push( key );

        }

        document.fonts_keys = key_list.join( ', ' );

      }
      catch ( ignore ) { ; }

      try {

        var values = window.document.fonts.values();
        var value_list = [];

        for ( var i = 0; i < values.length; i++ ) {

          var value = values[ i ];

          value_list.push( value );

        }

        document.fonts_values = value_list.join( ', ' );

      }
      catch ( ignore ) { ; }

    }
    catch ( ignore ) { ; }

    var window_data = {};

    function read_window( prop ) {

      window_data[ prop ] = get_state( window, prop );

    }

    try {

      read_window( 'status' );
      read_window( 'innerWidth' );
      read_window( 'innerHeight' );
      read_window( 'scrollX' );
      read_window( 'pageXOffset' );
      read_window( 'scrollY' );
      read_window( 'pageYOffset' );
      read_window( 'screenX' );
      read_window( 'screenY' );
      read_window( 'outerWidth' );
      read_window( 'outerHeight' );
      read_window( 'devicePixelRatio' );
      read_window( 'scrollMaxX' );
      read_window( 'scrollMaxY' );
      read_window( 'fullScreen' );
      read_window( 'mozInnerScreenX' );
      read_window( 'mozInnerScreenY' );
      read_window( 'mozPaintCount' );

    }
    catch ( ignore ) { ; }

    var navigator = {};

    function read_navigator( prop ) {

      navigator[ prop ] = get_state( window.navigator, prop );

    }

    try {

      read_navigator( 'oscpu' );
      read_navigator( 'vendor' );
      read_navigator( 'vendorSub' );
      read_navigator( 'productSub' );
      read_navigator( 'cookieEnabled' );
      read_navigator( 'buildID' );
      read_navigator( 'appCodeName' );
      read_navigator( 'appName' );
      read_navigator( 'appVersion' );
      read_navigator( 'platform' );
      read_navigator( 'userAgent' );
      read_navigator( 'product' );
      read_navigator( 'language' );
      read_navigator( 'languages' );
      read_navigator( 'onLine' );

    }
    catch ( ignore ) { ; }

    var controls = {};

    function read_controls( prop ) {

      var setting = null;

      try {

        if ( typeof window[ prop ] === 'undefined' ) {

          setting = null;

        }
        else {

          if ( typeof window[ prop ].visible !== 'undefined' ) {

            setting = window[ prop ].visible;

          }
          else {

            setting = null;

          }
        }
      }
      catch ( ignore ) {

        setting = null;

      }

      controls[ prop ] = setting;

    }

    try {

      read_controls( 'locationbar' );
      read_controls( 'menubar' );
      read_controls( 'personalbar' );
      read_controls( 'scrollbars' );
      read_controls( 'statusbar' );
      read_controls( 'toolbar' );

    }
    catch ( ignore ) { ; }

    var orientation = {};

    function read_orientation( prop ) {

      orientation[ prop ] = get_state( window.screen.orientation, prop );

    }

    try {

      read_orientation( 'type' );
      read_orientation( 'angle' );

    }
    catch ( ignore ) { ; }

    var screen = {};

    function read_screen( prop ) {

      screen[ prop ] = get_state( window.screen, prop );

    }

    try {

      read_screen( 'availWidth' );
      read_screen( 'availHeight' );
      read_screen( 'width' );
      read_screen( 'height' );
      read_screen( 'colorDepth' );
      read_screen( 'pixelDepth' );
      read_screen( 'top' );
      read_screen( 'left' );
      read_screen( 'availTop' );
      read_screen( 'availLeft' );
      read_screen( 'mozOrientation' );

      screen[ 'orientation' ] = orientation;

    }
    catch ( ignore ) { ; }

    var environment = {
      'location': location,
      'document': document,
      'window': window_data,
      'navigator': navigator,
      'controls': controls,
      'screen': screen
    };

    /* forms:
     * web-ex, php-http, php-ass, php-err, php-ex
     */

    var meta_data = {
      /*
      'meta': {
        'form': form,
        'uuid': uuid
      },
      */
      'time': {
        'msts': 0,
        'date': null,
        'time': null,
        'client': (new Date()).toString(),
        'server': null,
        'local': null,
        'utc': null
        //'dnic': null,
        //'tnic': null
      },
      'environment': environment,
      'exception': ex_data
    };

    function send_payload( msts, uuid ) {

      // 2017-02-21 jj5 - DONE: don't send context data if not debugging
      if ( is_disabled() ) { return; }

      var window_json = JSON.stringify( window, json_replacer, 1 );
      var window_data = JSON.parse( window_json );

      var data = {};

      for ( var i in meta_data ) {
        data[ i ] = meta_data[ i ];
      }

      data.window = window_data;

      data.context = {
        'cookies': pclog_read_cookies(),
        'scripts': pclog_read_scripts()
      };

      var json = JSON.stringify( data, null, 1 );
      send_log( 'data', form, uuid, msts, json, pclog_notify_user );

    }

    var after_meta = pclog_notify_user;

    if ( ! is_disabled() ) {

      after_meta = send_payload;

    }

    send_log(
      'meta',
      form,
      uuid,
      msts,
      JSON.stringify( meta_data ),
      //send_payload
      after_meta
    );

    if ( typeof ex !== 'undefined' ) { ex.logged = true; }

    window.pclog_last_message = ex_data.message;
    window.pclog_last_file = ex_data.file_name;
    window.pclog_last_line = ex_data.line_number;

  }
  catch ( ignore ) { ; }

}

function pclog_read_cookies() {

  var cookies = {};

  var parts = window.document.cookie.split( ';' );

  for ( var i = 0; i < parts.length; i++ ) {

    var part = parts[ i ].trim();

    var key_val = part.split( '=', 2 );

    var key = key_val[ 0 ].trim();

    var val = '';

    if ( key_val.length === 2 ) {

      val = key_val[ 1 ].trim();

    }

    var key_text = decodeURIComponent( key );
    var val_text = decodeURIComponent( val );

    cookies[ key_text ] = val_text;

    if ( val_text === '' ) { continue; }

    if ( val_text[ 0 ] === '{' || val_text[ 0 ] === '[' ) {

      try {

        cookies[ key_text ] = JSON.parse( val_text );

      }
      catch ( ex ) { ; }

    }
  }

  return cookies;

}

function pclog_read_scripts() {

  var scripts = [];

  for ( var i = 0; i < window.document.scripts.length; i++ ) {

    var script = window.document.scripts[ i ].outerHTML;

    scripts.push( script );

  }

  return scripts;

}

