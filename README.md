# About

Guard is a Linux CLI application which relies on inotify-tools to watch multiple folders (usually containing web sites). Whenever a file is created, modified or deleted within a watched folder(s), the action will be reversed and event will be logged. Then you can list, allow or forget (remove) blocked events.

It can also send e-mail notification about modified files.

# Requirements

* PHP 5.5.9 or greater (PHP 7 highly recommended)
* [inotify-tools](https://github.com/rvoicilas/inotify-tools/wiki)

If you don't have inotify-tools installed the app will fail miserably. Make sure you [have it installed](https://github.com/rvoicilas/inotify-tools/wiki#getting) before continuing!!!

# Installation

Installation is very easy, you can choose to have it installed via `curl` or `wget`.

For cURL, type this into your command line:

    curl -LSs http://avramovic.github.io/guard/installer | php

Alternatively, you can try with wget:

    wget -q -O - "$@" http://avramovic.github.io/guard/installer | php

The installer will check your PHP configuration and will let you know if everything is okay. Once done, you can check installed version with:

    php guard.phar --version

## Global installation

To install Guard globally, simply move it to any folder which is in your PATH:

    mv guard.phar /usr/local/bin/guard

> **Note**: If the above fails due to permissions, you may need to run it again with sudo. (sudo !!)   
> **Note**: For information on changing your PATH, please read the [Wikipedia article](https://en.wikipedia.org/wiki/PATH_(variable)) and/or use Google.

From now on, you should be able to call Guard with guard command only:

    guard --version

If it works, you can substitute `php guard.phar` with `guard` in all following commands.

# Quickstart

Let's say your site *example.com* is located in `/home/example/public_html` and you want to protect it. First, let's add that path to the watch list:

    php guard.phar site:add example.com --path=/home/example/public_html

If it says *Site example.com added!* you're ready to go.

> **Note**: By default, Guard is watching for changes only on the following file types: `*.php;*.htm*;*.js;*.css;*.sql`. You can use `--types=TYPES` to specify your own file types to watch. Use `*` to watch them all (NOT recommended)   

Now, start the watcher and that's it:

    php guard.phar start &

> **Note**: Because start is a continious (never-ending) command, we're adding & at the end to run it in the background.

# Commands

Guard offers variety of commands to make your life easier. Let's review them all!

> **Note**: To see all available commands, use `php guard.phar`   
> **Note**: To get help about individual command, `use php guard.phar help COMMAND`

## Site commands

These commands are used to work with your site(s).

### site:add

This command is used to add site to the watched list. All files from the supplied path will be backed up.

    Usage:
      site:add [options] [--] <name>
    
    Arguments:
      name                     Name of the site. Usually the domain name
    
    Options:
      -p, --path=PATH          Path to guard [default: "."]
      -t, --types=TYPES        File extensions to protect [default: "*.php;*.htm*;*.js;*.css;*.sql"]
      -e, --email=EMAIL        Email address for notifications
      -x, --excludes=EXCLUDES  Paths to exclude (multiple values allowed)

> **Note**: Name can be any valid folder name but to make everything easier you should always use your site domain name here.   
> **Note**: You must restart Guard (stop/start) after using this command for the changes to take effect.

#### Example(s)

Protect only html files in the current directory:

    php guard.phar site:add example.com --types=*.html

Protect default files in the custom directory and set e-mail for notifications:

    php guard.phar site:add example.com --path=/home/example/public_html --email=you@example.com

### site:backup

This command is used to backup all files from site's path.

#### Example(s)

Refresh backup of the site *example.com*:

    php guard.phar site:backup example.com

### site:list

This command is used to show existing protected sites.

#### Example(s)

List all guarded sites:

    php guard.phar site:list

### site:remove

This command is used to remove site from being protected.

> **Note**: You must restart Guard (stop/start) after using this command for the changes to take effect.

#### Example(s)

Remove site *example.com* from being protected:

    php guard.phar site:remove example.com

### site:set

This command is used to set individual options for one site.

    Usage:
       site:set <name> <variable> <value> (<value>)...
    
     Arguments:
       name                  Site name
       variable              Variable name to set (can be: name, path, email, types or excludes)
       value                 Variable value (or values for excludes, separated with space) to set
    
> **Note**: You must restart Guard (stop/start) after using this command for the changes to take effect.

#### Example(s)

Change name of the site:

    php guard.phar site:set example.com name othername.org

Set/change email for notifications about this site:

    php guard.phar site:set example.com email you@othername.org

Set new excludes for the site (removing old excludes):

    php guard.phar site:set example.com excludes /home/example/public_html/excluded ./public_html/uploads

> **Note**: Setting excludes using this method will **remove** existing excludes. See below for alternatives.

## Exclude commands

These commands are used to work with your sites' excludes.

### exclude:add

Add folder to site excludes.

> **Note**: You must restart Guard (stop/start) after using this command for the changes to take effect.

#### Example(s)

Add folder to site excludes:

    php guard.phar exclude:add example.com /path/to/excluded/folder

### exclude:remove

Remove folder from site excludes.

> **Note**: You must restart Guard (stop/start) after using this command for the changes to take effect.

#### Example(s)

Remove folder from site excludes:

    php guard.phar exclude:remove example.com /path/to/excluded/folder

## Event commands

These commands are used to work with blocked events.

### event:list

List all blocked events.

#### Example(s)

Remove folder to site excludes:

    php guard.phar event:list

### event:diff

Show a diff between original file and the blocked action.

#### Example(s)

Show a diff between original file and the blocked action with number 2:

    php guard.phar event:diff 2

### event:allow

Allow blocked event to occur.

#### Example(s)

Allow blocked event to occur with number 2:

    php guard.phar event:allow 2

Allow all blocked events to occur.

    php guard.phar event:allow all

> **Note**: Be very careful when allowing ALL events. Study all the events with event:list and remove suspicious events first!

### event:remove

Forget (remove) blocked event.

#### Example(s)

Remove blocked event to occur with number 3:

    php guard.phar event:remove 3

Remove all blocked events without asking for confirmation:

    php guard.phar event:remove all --silent

## Email commands

One neat feature of Guard is to send you email notifications when events are blocked. It can send mail using three different transports:

* mail - Use PHP's mail() function to send emails. This is the default transport.
* sendmail - Use sendmail to send emails. You'll need to have sendmail installed on your server.
* smtp - Use SMTP server to send emails. It supports no encryption, ssl and tls encryption. This is preferred transport.

You should set up a cron job to call `email:notify` at regular intervals to notify you about new changes on site files. See examples below.

### email:show

Show current email configuration.

#### Example(s)

Show current email configuration:

    php guard.phar email:show

### email:set

Set email configuration variable.

    Usage:
       email:set <variable> <value> (<value>)...
    
     Arguments:
       variable              Variable name to set (can be: recipient, transport, sendmail, smtp_host, smtp_port, smtp_user, smtp_pass or smtp_encrypt)
       value                 Variable value to set

> **Note**: You must restart Guard (stop/start) after using this command for the changes to take effect.

#### Example(s)

Change email transport to sendmail:

    php guard.phar email:set transport sendmail

Set sendmail command path:

    php guard.phar email:set sendmail "/usr/bin/sendmail -bs"

Set default recipient(s) for all e-mails:

    php guard.phar email:set address you@example.com someoneelse@gmail.com

Change email transport to SMTP:

    php guard.phar email:set transport smtp

Set up SMTP:

    php guard.phar email:set smtp_host mail.example.com
    php guard.phar email:set smtp_port 465
    php guard.phar email:set smtp_user example
    php guard.phar email:set smtp_pass your-password
    php guard.phar email:set smtp_encrypt tls

### email:test

Send a test email to the default recipient(s) or custom email address.

#### Examples

Send a test email to the default recipient(s):

    php guard.phar email:test

Send a test email to the custom email address:

    php guard.phar email:test someoneelse@example.com

### email:notify

Send email notifications about new blocked file events.

> **Note**: When sending these emails FROM email address will be set to guard-notifications@YOUR-SITE-NAME, if YOUR-SITE-NAME is a valid domain name. Otherwise, it will be the same address as the site's email address (so it'll look like you're sending an email to yourself).

#### Example(s)

Set up a cron job to send you email notifications every 5 minutes (if any new blocked events occur since last notification):

    */5 * * * * php /full/path/to/guard.phar email:notify >/dev/null 2>&1

## Service commands

These are service commands used to start/stop and update Guard.

### status

Shows if Guard is currently running:

    php guard.phar status

### start

Start Guard if not running:

    php guard.phar start

### stop

Stop Guard if running:

    php guard.phar stop

### update

Update Guard to the latest version:

    php guard.phar update

Rollback Guard to the previous version:

    php guard.phar update --rollback

> **Note**: If you have Guard installed globally, you may need to run this command with sudo!
