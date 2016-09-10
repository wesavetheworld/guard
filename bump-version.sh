#!/bin/bash

set -e

# CHECK MASTER BRANCH
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [[ "$CURRENT_BRANCH" != "master" ]]; then
  echo "You have to be on master branch currently on $CURRENT_BRANCH . Aborting"
  exit 65
fi

# CHECK box COMMAND
command -v box >/dev/null 2>&1 || { echo "Error : Command box is not installed on the system"; echo "See : https://github.com/box-project/box2 "; echo  "Exiting..." >&2; exit 65; }

# CHECK THAT WE CAN CHANGE BRANCH
git checkout gh-pages
git checkout --quiet master

# BACK TO MASTER AND BUILD
git checkout master
box build

# NOW UPDATE WEBSITE
git checkout gh-pages

# MOVE PHAR
mv guard.phar downloads/guard.phar

# CALCULATE SHA1 SUM
sha1sum downloads/guard.phar > downloads/guard.version

# READ SHA1 FOR DISPLAY
SHA1=`cat downloads/guard.version`

# ADD FILES TO GIT
git add downloads/guard.phar
git add downloads/guard.version
git commit -m "Bump version ${SHA1}"

#
# Go back to master
#
git checkout master

echo "New version created. Now you should run:"
echo "git push origin gh-pages"
