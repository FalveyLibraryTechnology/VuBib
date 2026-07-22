VuBib is the backend software used to manage https://findingaugustine.org.

Installation:
-------------
1. Download/Clone the project from github
2. Goto project directory path, type composer install
3. Change the paths accordingly in config\autoload folder.

Database Installation:
------------------------
1. Goto 'path of\mysql\bin' and type mysql -u username -p password
2. Type create database database name
3. mysql --default-character-set=utf8 -u username -p password database name< path of panta_rhei.sql
4. Dump sample data to get started: goto path of\mysql\bin path and give mysql -u username database name< path of each table.sql file
   (eg: mysql -u username test< path of agenttype.sql). Load data to tables users, module_access, page_instructions, translate_language,
folder,worktype, workattribute, workattribute_option to get started.
