<?
#!/bin/sh
#=====================================================================
#
#	BACKUP SERVIDOR DEVHOUSE - MEDIATEMPLE
#		por Leonardo Lima de Vasconcellos
#			em 20/10/2009
#				http://www.devhouse.com.br
#
#
#=====================================================================

HOJE=`date +%d/%m/%Y`
MYSQLUSER="admin"
MYSQLPASS="123456"

#/usr/local/psa/bin/pleskbackup --all /backup/dump-$HOJE-PSA
#tar -cvf /backup/dump-$HOJE-WWW.tar /var/www
mysqldump -u $MYSQLUSER -p $MYSQLPASS --all-databases > /backup/dump-$HOJE-MySQL.sql
gzip /backup/dump-$HOJE-MySQL.sql
#gzip /backup/dump-$HOJE-WWW.tar


?>