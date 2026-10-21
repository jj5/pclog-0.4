
function handle_select( el, clear, form ) {

  if ( typeof clear === 'undefined' ) { clear = []; }
  if ( ! ( clear instanceof Array ) ) { clear = [ clear ]; }

  if ( typeof form === 'undefined' ) { form = 'form'; }

  form = document.forms[ form ];

  form.elements.namedItem( el.name ).value = el.value;

  for ( var i in clear ) {

    form.elements.namedItem( clear[ i ] ).value = '';

  }

  form.submit();

  return false;

}
