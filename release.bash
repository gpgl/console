#!/bin/bash

set -e

if [ -z "$1" ]; then
    echo "version required"
    exit 1
fi

# move to working directory
cd $( dirname "${BASH_SOURCE[0]}" )

# add version to script
sed -r -i "s/\\\$version = '.+\+dev'/\$version = '$1'/" bin/gpgl

# tag release code
git commit -am "release version $1"
git tag -s "$1"

# minimize distribution
composer install --no-dev

# https://github.com/clue/phar-composer
phar-composer build . gpgl.phar

# sign distributable binary
gpg --detach-sign --output gpgl.phar.asc gpgl.phar

# re-install dev dependencies
composer install

# bump version
sed -i "s/\$version = '$1'/\$version = '$1+dev'/" bin/gpgl
git commit -am "bump version $1+dev"
