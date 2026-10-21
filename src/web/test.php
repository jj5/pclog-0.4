<?php

require_once __DIR__ . '/../include/application.php';

$opt = [
  'title_html' => 'Test Page',
  'script_inline' => [ 'res/test.js' ],
  'script_codes' => [
    //get_script_code()
  ]
];

render_head( $opt );
render_body( $opt );
render_foot( $opt );

function get_script_code() {

  $code=<<<EOF
    var json = {
      "hey": "guy",
      "anumber": 243,
      "anobject": {
        "whoa": "nuts",
        "anarray": [1, 2, "thr<h1>ee"],
        "more":"stuff"
      },
      "awesome": true,
      "bogus": false,
      "meaning": null,
      "japanese":"明日がある。",
      "link": "http://jsonview.com",
      "notLink": "http://jsonview.com is great",
      "multiline": ['Much like me, you make your way forward,',
        'Walking with downturned eyes.',
        'Well, I too kept mine lowered.',
        'Passer-by, stop here, please.'].join("\\n")
    };

    $(function() {
      $("#json").JSONView(json, { nl2br: true } );
      $("#json-collapsed").JSONView(json, { collapsed: true, nl2br: true, recursive_collapser: true });
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
    });
EOF;

   return $code;

}

function render_body( $opt ) {

  //render_json_view();

  render_page_menu( $opt );
?>

  <button onclick="raise_error(1)">Raise Error 1</button>
  <button onclick="raise_error(2)">Raise Error 2</button>
  <button onclick="raise_error(3)">Raise Error 3</button>
  <button onclick="raise_error(4)">Raise Error 4</button>

  <?php phpinfo(); ?>

<?php
}
