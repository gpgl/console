# GPL PHP GPG Locker Console

[![Build Status][12]][11]
[![Codecov][16]][14]
[![Latest Stable Version][7]][6]
[![Total Downloads][8]][6]
[![License][9]][6]

PHP 7 command line [symfony console application][1] to manage passwords and data
secured with [The GNU Privacy Guard][2].

## Installation

[Download the latest release][6], set it executable:

    curl -sSL https://github.com/gpgl/console/releases/latest | egrep -o '/gpgl/console/releases/download/[0-9\.]*/gpgl.phar"' | head -c-2 | wget --base=https://github.com/ -i - -O gpgl && chmod +x gpgl

Move it to a good path. For example: make it available system-wide:

    sudo mv gpgl /usr/local/bin/

After installing, run without any arguments to see a list of commands:

    gpgl

Use the `-h` flag with any command to get help with usage:

    gpgl <command> -h

## Testing

Because the tests require access to your GPG keyring and fixtures need specific
secret keys present, it's best to run them inside [the pre-built Docker container][18].

    docker run --rm -it -v "$PWD":/code gpgl/test-core

[1]:http://symfony.com/doc/current/components/console.html
[2]:https://www.gnupg.org/
[4]:https://github.com/gpgl/console/issues
[5]:https://getcomposer.org/
[6]:https://github.com/gpgl/console/releases/latest
[7]:https://poser.pugx.org/gpgl/console/v/stable
[8]:https://img.shields.io/github/downloads/gpgl/console/total.svg
[9]:https://poser.pugx.org/gpgl/console/license
[11]:https://travis-ci.org/gpgl/console
[12]:https://travis-ci.org/gpgl/console.svg?branch=master
[13]:https://github.com/composer/composer/issues/4072
[14]:https://codecov.io/gh/gpgl/console/branch/master
[16]:https://img.shields.io/codecov/c/github/gpgl/console/master.svg
[18]:https://hub.docker.com/r/gpgl/test-core/
