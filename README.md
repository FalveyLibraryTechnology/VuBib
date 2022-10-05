VuBib is a project to modernize our old bibliography management software. Old bibliography management software also known as "Finding Augustine"/
"Panta Rhei" contains works about Saint Augustine. Finding Augustine is a web application that has details of works about saint augustine, 
publishers, agents, languages of these works. Finding Augustine application relies on Smarty Templates and a variety of 
PEAR libraries for its functionality which are aging out of favor. 

VuBib is built using the following technologies:
PHP 5.6
zend-servicemanager dependency container
FastRoute router
Twig template engine
Whoops Error Handler
Zend expressive framework
Bootstrap for HTML, CSS and JS

Not many functional changes were made in VuBib except for removing few unnecessary and redundant things like 'Parent lookup' in Work > New, Edit 
and 'Parent' in Classification > Edit. Database constraints are enforced wherever necessary.

Installation:
-------------
Download/Clone the project from github
Goto prj directory path, type composer install

Change the paths accordingly in config\autoload folder.

Database Installation:
------------------------
1. Goto 'path of\mysql\bin' and type mysql -u username -p password
2. Type create database database name
3. mysql --default-character-set=utf8 -u username -p password database name< path of panta_rhei.sql
4. Dump sample data to get started: goto path of\mysql\bin path and give mysql -u username database name< path of each table.sql file
   (eg: mysql -u username test< path of agenttype.sql). Load data to tables users, module_access, page_instructions, translate_language,
folder,worktype, workattribute, workattribute_option to get started.
