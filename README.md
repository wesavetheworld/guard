# guard
Linux CLI application used to protect files and folders on server in real time.

## About

Guard is a Linux CLI application which relies on inotify-tools to watch multiple folders (usually containing web sites). Whenever a file is created, modified or deleted within a watched folder(s), the action will be reversed and event will be logged. Then you can list, allow or forget (remove) blocked events.

It can also send e-mail notification about modified files.

## Requirements

* PHP 5.5.9 or greater (PHP 7 highly recommended)
* [inotify-tools](https://github.com/rvoicilas/inotify-tools/wiki)

If you don't have inotify-tools installed the app will fail miserably. Make sure you [have it installed](https://github.com/rvoicilas/inotify-tools/wiki#getting) before continuing!!!

## Installation

Installation is very easy, you can choose to have it installed via `curl` or `wget`.

For cURL, type this into your command line:

`curl -LSs http://avramovic.github.io/guard/downloads/installer.php | php`

Alternatively, you can try with wget:

`wget http://avramovic.github.io/guard/downloads/installer.php; php installer.php; rm installer.php`

The installer will check your PHP configuration and will let you know if everything is okay. Once done, you can check installed version with:

`php guard.phar --version`

### Global installation

To install Guard globally, simply move it to any folder which is in your PATH:

`mv guard.phar /usr/local/bin/guard`

> **Note**: If the above fails due to permissions, you may need to run it again with sudo. (`sudo !!`)  
> **Note**: For information on changing your PATH, please read the [Wikipedia article](https://en.wikipedia.org/wiki/PATH_(variable)) and/or use Google.

From now on, you should be able to call Guard with `guard` command only:

`guard --version`

If it works, you can substitute `php guard.phar` with `guard` in all following commands.

## Quickstart

Let's say your site *example.com* is located in `/home/example/public_html` and you want to protect it. First, let's add that path to the watch list:

`php guard.phar site:add example.com --path=/home/example/public_html`

If it says *Site example.com added!* you're ready to go. 

> **Note**: By default, Guard is watching for changes only on the following file types: `*.php;*.htm*;*.js;*.css;*.sql`. You can use `--types=TYPES` to specify your own file types to watch. Use `*` to watch them all (NOT recommended)   

Now, start the watcher and that's it:

`php guard.phar start &`

> **Note**: Because `start` is a continious (never-ending) command, we're adding `&` at the end to run it in the background. 
> **Note**: To see all available commands, use `php guard.phar`   
> **Note**: To get help about individual command, use `php guard.phar help COMMAND`

## E-mail notifications

@todo

## Commands

### Site commands

#### site:add

#### site:backup

#### site:list

#### site:remove

#### site:set

### Event commands

#### event:list

#### event:diff

#### event:allow

#### event:remove

### Email commands

#### email:show

#### email:set

#### email:test

#### email:notify

### Service commands

#### status

#### start

#### stop

#### update
