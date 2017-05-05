#!/bin/bash

set -e

if [ -z "$1" ]; then
    echo "version required"
    exit 1
fi

# move to working directory
cd $( dirname "${BASH_SOURCE[0]}" )

# add version to script
sed -i "s/namespace gpgl\\\console;/namespace gpgl\\\console;\n\$version = '$1';/" bin/gpgl

composer install --no-dev

# https://github.com/clue/phar-composer
phar-composer build . gpgl.phar

gpg --detach-sign --output gpgl.phar.asc gpgl.phar

# undo hard-coded version in script
git reset --hard

git tag -s "$1"

composer install
