# webENM
This tool replaces SchILD ENM

# Requirements
## mdb_connector_server
Source: https://github.com/Sarius121/mdb_connector_server
This server has to run on the same machine on port 8080 (server and port can be changed in lib/MDBConnector/MDBDatabase.php). 
It is used because there is no MDB-file driver for php on linux (no one that can handle all sql commands this WebApp needs). The mdb_connector_server is written in Java (Spring) and uses the UCanAccess driver (http://ucanaccess.sourceforge.net/site.html), which is platform-independant. 

# Used libraries
- bootstrap (4.5.3)
- jquery (3.5.1)
- jquery-ui (1.12.1)
- editablegrid (https://github.com/webismymind/editablegrid) (clone on 01.2021)

# Features
- supports encrypted SchILD grade files (.enz)
- all student grade, class teacher and exams functions work
- platform independant

## not supported
- not encrypted grade files (.enm)
- renamed grade files
- support measures

# TODOs
- restoring backups (check uploaded files)
- temporary files are not deleteted directly (probably because they are somehow still opened by mdb_connector_server)