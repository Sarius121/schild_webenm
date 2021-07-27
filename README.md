# webENM
This tool replaces SchILD ENM

# Requirements
## mdb_connector_server
Source: https://github.com/Sarius121/mdb_connector_server
This server has to run on the same machine on port 8080 (server and port can be changed in lib/MDBConnector/MDBDatabase.php). 
It is used because there is no MDB-file driver for php on linux (no one that can handle all sql commands this WebApp needs). The mdb_connector_server is written in Java (Spring) and uses the UCanAccess driver (http://ucanaccess.sourceforge.net/site.html), which is platform-independant. 

# Used libraries
- bootstrap (5.0.2)
- jquery (3.5.1)
- jquery-ui (1.12.1)
- popper (2.9.2)
- editablegrid (https://github.com/webismymind/editablegrid) (clone on 01.2021)

# Features
- supports encrypted SchILD grade files (.enz)
- all student grade, class teacher and exams functions work
- platform independant

## not supported
- not encrypted grade files (.enm)
- renamed grade files
- support measures

# Installation notices
## Permissions
The grade-files/tmp/ directory has to be read and write accessible by the webserver with this webapp and the servlet-container with the mdb_connector_server. Easy way to do this in Linux is creating a group with the two users and setting the default permission for this directory as discribed here: https://unix.stackexchange.com/questions/1314/how-to-set-default-file-permissions-for-all-folders-files-in-a-directory.

# TODOs
- restoring backups (check uploaded files)
- temporary files are not deleteted directly (probably because they are somehow still opened by mdb_connector_server) -> not a problem in linux