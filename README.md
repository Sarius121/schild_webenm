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

Please have a look at the respective files/folders for more information on the licenses. All the above libraries are licensed under the MIT License.

# Features
- supports encrypted SchILD grade files (.enz)
- all student grade, class teacher and exams functions work
- platform independant (but Linux is recommended)

## not supported
- not encrypted grade files (.enm)
- support measures
- form print (print with designer)

# Installation notices
## Permissions
The grade-files/tmp/ directory has to be read and write accessible by the webserver with this webapp and the servlet-container with the mdb_connector_server. Easy way to do this in Linux is creating a group with the two users and setting the default permission for this directory as discribed here: https://unix.stackexchange.com/questions/1314/how-to-set-default-file-permissions-for-all-folders-files-in-a-directory.

## Configuration
- *config/ui-conf.php*: configure the front-end; note that these configurations are only at the front end: disabling a button doesn't mean that the functions of this button are not available anymore. It just hides the button.

For the following configuration files, *constants.php-example* files are included in the directories. You can just rename and then adjust them properly.

- *lib/MDBConnector/constants.php*: connection settings to mdb_connector_server (settings have to match the mdb_connector_server setup)
- *lib/ENMLibrary/constants.php*: configure the grade file directories and passwords and the visible columns

    The DEFAULT_DB_PASSWORD is set by SchILD and can be easily "cracked" by exporting any enm-file (enm not enz!) from SchILD and opening it with the ["Access PassView" program by NirSoft](http://www.nirsoft.net/utils/accesspv.html). It will output the password.

The source directory can be synchronized with a file location. In my setup it is synchronized with a WebDAV share. At the moment this synchonization has to be configured on the machine and cannot be handled by this application.

## Security
As I am not a professional software developer or cyber security specialist I cannot guarantee that there are no security issues in this app. You should only use it in an internal network or provide extra security e.g. with a HTTP authentication. When using it outside a safe environment it is furthermore highly recommended to use HTTPS and HSTS.

I don't take any responsibility for security flaws and this app does not follow the german data processing laws. In my opinion, this app is nevertheless more secure than the often usual "sending not encrypted grade files by email" method (by working on this project I noticed how badly protected the not encrypted grade files are).

# Known issues
- in Windows temporary files are often not deleteted directly (probably because they are somehow still opened by mdb_connector_server) -> this is not a problem in Linux