coding-challenge
================

Coding Challenge for Ecancer


# Setting up #

To setup this code base you will need to:

1. checkout the develop branch
2. install the composer dependencies
3. setup your database credentials for migrations
  * this should be setup in the phinx.yml file
4. setup your database credentials for the app/website
  * this will be in site/ini/database.ini
5. ensure write permissions set on site/cache/
  * this should be 777 - _chmod -R 777 site/cache_
6. create your blank/empty database
  * the system won't create it for you!
7. run the migrations
  * we use [phinx](http://docs.phinx.org/en/latest/commands.html#the-migrate-command) to keep our databases in-sync
8. setup a virtual host in Apache
  * you will need __AllowOverride All__ on to allow our .htaccess rules to take effect
9. add the new domain name in to your /etc/hosts file
  * you will need to sudo to do this - _sudo vim /etc/hosts_

# The Challenge #

This challenge is based upon elements of the legacy codebase you will be working on from time to time.

The framework itself is quite big and complicated so we have only ported over a few of the bare essential classes.

We will be looking to see your approach to solving the problems and how confident you are working with the PHP code.

Ideally you should try and work within the constraints of the existing codebase and framework. But we are open-minded
to other solutions and libraries that may compliment what we have already.

## The Code & Layout ##

Majority of the logic should live in the _includes/_ directory.

From the _includes/_ directory you should see:
  * a _core/_ directory - this contains the core framework
  * a _site-core/_ directory - this contains your classes and user classes

In the _migrations/_ directory we have loaded in our test data using the [phinx](http://docs.phinx.org/en/latest)
library. This library can be fetched using [composer](http://getcomposer.org/).

All configuration should live in the _site/ini/_ directory. You will need to add your database credentials to the
_site/ini/database.ini_ file - under the _development_ heading.

The _site/_ directory also contains images and cache folders. The video thumbnails are in the _site/cache/development/images/_
directory. You will need to give write permissions to the _site/cache/_ directory.

All view/template logic should live in the _includes/site-core/display/_ directory. These templates should get called
from their controller.

### Bugs ###

As we said this is a legacy codebase, and we've tried to port across the bare essentials of the codebase.

If you receive lots of debug messages or error messages and you are sure your code is working, you can disable the
custom error handler by setting _Debug = 0_ in _site/ini/debug.ini_.

If you do fix any bugs please let us know what you've fixed.


# Tasks #


## Task 1 ##

Make modifications to the existing Videos homepage.

## Task 1a ##

Order the Videos in the table by the PublicationDate column.

See: _includes/site-core/classes/controllers/class_controller_videos.php_

## Task 1b ##

Use your own judgement and add more Video data from the database to the HTML table on the Video Homepage.


# Task 2 #

Create a new Video Gallery page that displays 5 of the most recent video's thumbnails.

To do this you will need to use the _siteVideoToImage_ link table, this database query is already coded for you.

To create the thumbnail you'll need to look at the _clsImage_ class. You should use the _funFetch_ method.



# Delivery #

We don't mind how we get your code...


* If you forked this repository you can send in a _pull-request_ on github.
* If we've sent you this challenge as a Zip file or directly:
  * you can send it back to us as a Zip via email.
  * you can send us a dropbox link.
