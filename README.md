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

The tests require access to a GPG keyring with specific secret keys present,
and the integration suite needs an active server running with database fixtures.

This consistent environment is loaded in [the pre-built Docker container][18].

    docker run --rm -it -v "$PWD":/code/console gpgl/test-console

Code coverage HTML reports are generated in the `./tests/coverage` folder.

    php -S localhost:8686 -t ./tests/coverage/

You can customize the container entry command script in [run.bash][19].

    docker run --rm -it -v "$PWD"/tests/docker/run.bash:/run.bash -v "$PWD":/code/console gpgl/test-console

When developing new features which cross-cut console, core, and server,
you can mount volumes for all the source directories into the container.
It's convenient to locally house all three repositories in one folder.

    .
    ├── console
    ├── core
    └── server

Then delete the core library from the console's vendor directory
and symlink your local copy.

    rm -rf ./vendor/gpgl/core
    ln -s ../../../core/ ./vendor/gpgl/core

Your directory structure should look like this:

    .
    ├── console
    │   └── vendor
    │       ├── ...
    │       └── gpgl
    │           └── core -> ../../../core/
    ├── core
    └── server

And finally run the container mounting all your local volumes.

    docker run --rm -it -v "$PWD":/code/console -v "$PWD"/../core:/code/core -v "$PWD"/../server:/code/server gpgl/test-console

You may also want to override environment variables for server set in [Dockerfile][20].

If you want to change the server database test fixtures,
the sqlite database is mounted at the root of the container.

    docker run --rm -it -v /path/to/database.sqlite:/database.sqlite -v "$PWD":/code/console gpgl/test-console

The [Dockerfile][20] is included for customizing fixtures and platform dependencies.

    docker build -t gpgl/test-console:MyTag ./tests/docker

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
[18]:https://hub.docker.com/r/gpgl/test-console/
[19]:./tests/docker/run.bash
[20]:./tests/docker/Dockerfile
