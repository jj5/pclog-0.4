#!/bin/bash

cd $(dirname $0)
if [ "$?" -ne "0" ]; then
  echo 'Failed to cd to project dir...'
  exit 1
fi

cd ../web/res/lib/jquery
if [ "$?" -ne "0" ]; then
  echo 'Failed to cd to jquery dir...'
  exit 2
fi

echo $PWD

svn up
if [ "$?" -ne "0" ]; then
  echo 'Failed to svn up...'
  exit 3
fi

git pull
if [ "$?" -ne "0" ]; then
  echo 'Failed to git pull...'
  exit 4
fi

npm run build
if [ "$?" -ne "0" ]; then
  echo 'Failed to run npm build...'
  exit 5
fi

rm -rf node_modules
if [ "$?" -ne "0" ]; then
  echo 'Failed to remove node_modules...'
  exit 6
fi

svn ci -m 'Building jquery...'
if [ "$?" -ne "0" ]; then
  echo 'Failed to svn commit...'
  exit 7
fi

true

