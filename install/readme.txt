==============================
UPGRADE QuickTicket to v2.5
==============================

To upgrade from version 2.x to 2.5, you can proceed with a normal installation (see here after).

  Remarque #1
  It's recommended to backup your config.php file in the /bin/ directory
  (in case you don't remember the connection parameters of your database)
  
  Remarque #2
  If your board allows user photo (avatar) or document upload,
  it's recommended to NOT delete the /avatar/ and /upload/ directories.
  Other files and folders can be deleted before installing the new release.

==============================
INSTALLATION of QuickTicket v2.5
==============================

BEFORE starting the installation procedure, make sure you know:
- The type of database you will use (MySQL, SQLserver, PostgreSQL, SQLite, Firebird, Oracle or DB2).
- Your database host (the name of your database server, often "localhost")
- The name of your database (where the QuickTicket can install the tables).
- The user name for this database (having the right to create table).
- The user password for this database.


1. Upload the application on your web server
--------------------------------------------
Just send (ftp) all the files and folders on your webserver (for example in a folder /quickticket/).
If you are making an upgrade, do NOT overwrite the /avatar/ nor /upload/ directories.


2. Configure the permissions
----------------------------
This step is very important !
Without this configuration, the installation programme will not work and the database will not be configured.

Change the permission of the file /bin/config.php to make it writable (chmod 777).
Change the permission of the directories /avatar/ and /upload/ (and subdirectories) to make them writable (chmod 777).


3. Start the installation
-------------------------
From your web browser, start the installation script: install/install.php
(i.e. Type the url http://www.yourwebsite.com/quickticket/install/install.php)
This script will ask you the database connection information and create the necessary table in it.


4. Clean up
-----------
When previous steps are completed, you can delete the /install/ folder on your website and set the permission for /bin/config.php to readonly.


VERSION HISTORY
===============
2.5    : Security and profile improvement. Requires php 5.x
2.4    : Includes inspections as a new type of ticket
2.3    : Improve ticket management and calendar
2.1    : Improve tags and statistics
2.0    : Allow using free/proposed categories to classify tickets (also to search and to produce specific statistics)
1.9.0.3: Improve memory usage and allow exporting statistics to csv
1.9    : Allows multiple translations for section, status and index
1.8    : Improvement in the search tools and in the statistics
1.7.0.1: Bug fix for profile security and statistics with Oracle db
1.7    : Version supporting the Map module
1.6    : Version supporting Oracle

