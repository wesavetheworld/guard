#!/bin/bash

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi


# CHECK MASTER BRANCH
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [[ "$CURRENT_BRANCH" != "master" ]]; then
  echo "You have to be on master branch currently on $CURRENT_BRANCH . Aborting"
  exit 65
fi

# CHECK FORMAT OF THE TAG 
php -r "if(preg_match('/^\d+\.\d+\.\d+(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?\$/',\$argv[1])) exit(0) ;else{ echo 'format of version tag is not invalid' . PHP_EOL ; exit(1);}" $1


# CHECK box COMMAND
command -v box >/dev/null 2>&1 || { echo "Error : Command box is not installed on the system"; echo "See : https://github.com/box-project/box2 "; echo  "Exiting..." >&2; exit 65; }

# CHECK THAT WE CAN CHANGE BRANCH
git checkout gh-pages
git checkout --quiet master


TAG=$1


#
# Tag & build master branch
#
git checkout master
box build

#
# Copy executable file into GH pages
#
git checkout gh-pages

mv guard.phar downloads/guard.phar
git add downloads/guard.phar

sha1sum downloads/guard.phar downloads/guard.version

SHA1=`cat downloads/guard.version`

git add downloads/guard.phar
git add downloads/guard.version
git commit -m "Bump version ${SHA1}"

#
# Go back to master
#
git checkout master

echo "New version created. Now you should run:"
echo "git push origin gh-pages"
echo "git push --force --tags"

