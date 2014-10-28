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

# Task 1 #

Task 1 is to sort the list of videos by publication date using the Database class.

# Task 2 #

Task 2 is to build a new page showing top 5 most recent videos including their thumbnail using the Image class.
