#!/bin/bash

cd `dirname $0`/..

svn up

cat >doc/links.wiki <<EOF
== Links ==

Links are generated with [https://www.jj5.net/sixsigma/Bugslist.php bugslist.php]:

 \$ bugslist --all -a= -c=s -o=sk -t=s > doc/links.wiki

EOF

bugslist --all -a= -c=s -o=sk -t=s >> doc/links.wiki

firefox 'https://www.progclub.org/pcwiki/index.php?title=Pclog&action=edit&section=11' 1>/dev/null 2>&1 &

kate doc/links.wiki 1>/dev/null 2>&1 &

