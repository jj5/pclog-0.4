function PclogError( message, fileName, lineNumber ) {
  this.name = 'PclogError';
  this.message = message || 'An error occurred.';
  var context = new Error();
  this.fileName = fileName || context.fileName;
  this.lineNumber = lineNumber || context.lineNumber;
  this.stack = context.stack;
}
PclogError.prototype = Object.create( Error.prototype );
PclogError.prototype.constructor = PclogError;

window.test = 1;
window.password = 'not telling';

function raise_error( test ) {
  window.test = test;
  if ( test === 4 ) { return a1(); }
  try {
    c();
  }
  catch ( ex ) {
    //console.dir( ex );
    return;
    alert( ex.tag );
    for ( var i in ex ) { alert( i ); }
  }
}
function a() {
  var fn = 'a' + window.test;
  window[fn]();
}
function a1() {
  var data = { a: 1, b: 2 };
  throw_exception( "Testing from throw_exception...", data );
}
function a2() {
  throw 'bare string';
}
function a3() {
  throw { message: 'Just testing' };
}
function b() {
  try {
    a();
  }
  catch ( ex ) {
    process_exception( ex, new Error() );
  }
}
function c() {
  try {
    b();
  }
  catch ( ex ) {
    process_exception( ex, new Error() );
  }
}
