# webENM
This tool replaces the SchILD ENM used in NRW (Germany) for filling in the students' grades. It is a web application which can be hosted on a web server and allows the teachers to edit the grades from any device and operating system (no mobile support). It was originally developed to be able to edit the grades from Linux computers and iPads and to remove the requirement given by the SchILD program to use Windows.

# Requirements
## mdb_connector_server
Source: https://github.com/Sarius121/mdb_connector_server
This server has to run on the same machine on port 8080 (server and port can be changed in lib/MDBConnector/MDBDatabase.php). 
It is used because there is no MDB-file driver for php on linux (no one that can handle all sql commands this WebApp needs). The mdb_connector_server is written in Java (Spring) and uses the UCanAccess driver (http://ucanaccess.sourceforge.net/site.html), which is platform-independant.

## required grade file format
The grade-files must be encrypted (i.e. enz-files) with a password which is the same for all teacher. This "school master password" protects the files against non-teacher access. Furthermore the files must be saved with a teacher individual password which is used to authenticate the teachers.
In order to achieve this format, do the following when exporting the files in SchILD:
- in the tab "Allgemeine Einstellungen" select "Kennwort der Benutzer in Export-Datei übernehmen" (the passwords have to be set at a different place)
- in the tab "Verschlüsselung und Versand" select "Kennwort-geschützte Zip-Datei erzeugen" and set a password

Notice that teacher restrictions like "Eingabe von Mahnungen sperren" are ignored by this app. If you want to hide columns look at the configuration paragraph.

The filenames must start with the teachers' usernames followed by the file suffix (see configuration), some not important characters and the extension ".enz". It is highly recommended that all usernames have the same length, especially if no suffix is used because the app just selects one file if multiple files match to the described pattern.

## Software
It should work with every web server but was only tested on Linux with Apache and PHP 8.0.1

# Used libraries
- bootstrap (5.0.2)
- bootstrap icons
- jquery (3.5.1)
- jquery-ui (1.12.1)
- popper (2.9.2)
- editablegrid (https://github.com/webismymind/editablegrid) (clone on 01.2021)
- Textarea Caret Position (https://github.com/component/textarea-caret-position)
- SabreDAV (4.4)

Please have a look at the respective files/folders for more information on the licenses. All the above libraries are licensed under the MIT License.

# Features
- supports encrypted SchILD grade files (.enz)
- all student grade, class teacher and exams functions work
- platform independant (but Linux is recommended)
- admin page for seeing file insights and downloading and saving files
- possible sources of the grade files: WebDAV or local folder

## not supported
- not encrypted grade files (.enm)
- support measures
- form print (print with designer)

# Installation notices
## Permissions
The grade-files/tmp/ directory has to be read and write accessible by the webserver with this webapp and the servlet-container with the mdb_connector_server. Easy way to do this in Linux is creating a group with the two users and setting the default permission for this directory as discribed here: https://unix.stackexchange.com/questions/1314/how-to-set-default-file-permissions-for-all-folders-files-in-a-directory.

## Installation
To load the Sabre library, you have to use composer:
```
php composer.phar install
```
If composer is not installed yet, you find more information about it here: [https://getcomposer.org/doc/00-intro.md](https://getcomposer.org/doc/00-intro.md).

## Configuration
- *config/ui-conf.php*: configure the front-end; note that these configurations are only at the front end: disabling a button doesn't mean that the functions of this button are not available anymore. It just hides the button.

For the following configuration files, *constants.php-example* files are included in the directories. You can just rename and then adjust them properly.

- *lib/MDBConnector/constants.php*: connection settings to mdb_connector_server (settings have to match the mdb_connector_server setup)
- *lib/ENMLibrary/constants.php*: configure the grade file directories and passwords and the visible columns

    The DEFAULT_DB_PASSWORD is set by SchILD and can be easily "cracked" by exporting any enm-file (enm not enz!) from SchILD and opening it with the ["Access PassView" program by NirSoft](http://www.nirsoft.net/utils/accesspv.html). It will output the password.

### Source module

The source module must be configured in the *lib/ENMLibrary/constants.php* file and can at the moment be either WebDAV or a local folder (feel free to write your own module).

- *LocalFolderDataSourceModule*: The grade files have to be in a folder on the server. I had this folder synchronised with WebDAV a long time, so synchronising this folder should work.
- *WebDAVDataSourceModule*: The grade files lay on some WebDAV-Server and are downloaded to this app when editing. This feature is rather new, so it's still a bit experimental.

### Admin user

The admin user must also be configured in the *lib/ENMLibrary/constants.php* file. The username of the admin should definetely not be a normal username, because he uses the same login form as normal users. The admin can see all files and usernames which can be opened. He can also see the date of the last changes, can close files if someone forgot to sign out and download all grade files as zip. However, the actions can be disabled in the config file, too.

## Security
As I am not a professional software developer or cyber security specialist I cannot guarantee that there are no security issues in this app. You should only use it in an internal network or provide extra security e.g. with a HTTP authentication. When using it outside a safe environment it is furthermore highly recommended to use HTTPS and HSTS.

I don't take any responsibility for security flaws and this app does not follow the german data processing laws. In my opinion, this app is nevertheless more secure than the often usual "sending not encrypted grade files by email" method (by working on this project I noticed how badly protected the not encrypted grade files are).

# Known issues
- in Windows temporary files are often not deleteted directly (probably because they are somehow still opened by mdb_connector_server) -> this is not a problem in Linux