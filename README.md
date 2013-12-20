# Tiny Address Book #

This demo application shows the design and programming techniques used in my projects. Some of the components of vRegistrator framework are used. All the code is original (except some standard functions).

## Functionality ##

The database consists of *contacts* and *towns* tables containing First & Last Name, Street, Postcode and Town data. The records can be added, deleted or modified. The table can be browsed and saved in XML format. Double-clicking on the row selects the record for editing. The towns list must be edited outside.

The demo is uploaded on [abk.vregistrator.com].

## Design & programming ##

OOP MVC principles are followed both on the back and front end. The AJAX is used for client-server communications. See [vregistrator.com] regarding the techniques.

## Requirements ##

- Webserver: Apache or IIS supporting PHP and MySql
- PHP: version 5.4+, PDO mysql and sqlite extensions
- MySql: version 5+, Innodb engine
- JavaScript: 1.5+ version
- Browser: any common (IE, FF, Chrome, Safari, Opera,...), newer version

## Installation ##

Unpack the *abk.zip* to your local/remote webserver retaining the folder structure. Create and load the MySql database from the *sql* files supplied. Find/change the connection properties in the configuration file */pri/abk/abk.xml* (node *db*):

- nme - database name
- usr - access username
- pwd - access password
- pfx - database and username prefix (if hosting requires)

## File structure ##

The files are arranged in folders according to their scope and functionality.

### Folders ###

The root folder contains default startup. The folders:

- *pri* - private server-side files; subfolders:
 + *abk* - application files
 + *sys* - framework files
 + *tmp* - temporary workfiles
- *pub* - public client-side files; subfolders:
 + *js* - JavaScript code
 + *css* - stylesheets
 + *pic* - images
- *dev* - miscellaneous development data

The *abk* and *sys* folders (may) have the subfolders:

- *_act* - actions PHP code *.php
- *_css* - dynamic CSS code *.css
- *_js* - dynamic JavaScript code *.js
- *_lib* - PHP classes *.php
- *_srv* - action's PHP services *.inc
- *_tpl* - htm templates *.phtml

### Files ###

- *index.php* - startup
- *php.php* - check PHP version
- *pri/.htaccess* - deny access
- *pri/gateway* - common data gateway
- *pri/startup* - bootstrap
- *dev/abook_data.sql* - MySql data loading queries
- *dev/abook_struc.sql* - MySql structure queries
- *pri/abk/abk.db* - dictionary
- *pri/abk/abk.xml* - configuration
- *pri/abk/_act/abook.php* - main panel creator
- *pri/abk/_act/browse.php* - browse panel creator
- *pri/abk/_act/finish.php* - bye panel creator
- *pri/abk/_act/shell.php* - layout creator
- *pri/abk/_css/shell.css* - layout style
- *pri/abk/_js/abook.js* - editor/browser class
- *pri/abk/_lib/Frontal.php* - front controller class
- *pri/abk/_srv/abook.inc* - data manipulation
- *pri/abk/_srv/browse.inc* - browse services
- *pri/abk/_tpl/abook.phtml* - panel template
- *pri/abk/_tpl/browse.phtml* - browse template
- *pri/abk/_tpl/shell.phtml* - layout template
- *pri/sys/_act/download.php* - file download dialogue
- *pri/sys/_lib/Base.php* - base functionality class
- *pri/sys/_lib/Common.php* - common methods & properties class
- *pri/sys/_lib/Database.php* - database abstraction layer class
- *pri/sys/_lib/IData.php* - database I/O layer interface
- *pri/sys/_lib/Mypdo.php* - PDO I/O layer
- *pri/sys/_lib/Texts.php* - multilingual texts class
- *pri/sys/_tpl/exit.phtml* - finish/error panel template
- *pri/sys/_tpl/exits.phtml* - exit panel shell template


[vregistrator.com]: http://vregistrator.com/hlp/en/spgm
[abk.vregistrator.com]: http://abk.vregistrator.com
