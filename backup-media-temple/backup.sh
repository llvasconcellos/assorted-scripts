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
MYSQLUSER="admin"
MYSQLPASS="newlife2008"

HOJE=`date +%d.%m.%Y`	# Data de hoje separada por pontos
DDM=`date +%d`		# Dia do mes com leading zeros
MES=`date +%B`		# Mes ex. January
DNDS=`date +%u`		# Dia numerico da semana de 1 a 7 onde 1 representa Segunda-feira
SEMANA=`date +%V`	# Numero da semana a iniciar do comeco do ano de 0 a 53
DDS=`date +%A`		# Dia da semana por extenso. ex. Monday

BACKUPDIR="/backup"
DATADIR="/home/$NUMBER/data/"

BACKUPMENSAL=yes
BACKUPSEMANAL=yes
BACKUPDIARIO=yes

DIABACKUPSEMANAL=7 # Dia do backup semanal (1 a 7) 1 representa Segunda-feira


# The TEMP directory is what is transferred to Online Storage Backup
TEMP="/home/$NUMBER/data/temp"



if [ ! -e "$BACKUPDIR" ]
	then
	mkdir -p "$BACKUPDIR"
fi

if [ ! -e "$BACKUPDIR/diario" ]
	then
	mkdir -p "$BACKUPDIR/diario"
fi

if [ ! -e "$BACKUPDIR/semanal" ]
	then
	mkdir -p "$BACKUPDIR/semanal"
fi

if [ ! -e "$BACKUPDIR/mensal" ]
	then
	mkdir -p "$BACKUPDIR/mensal"
fi




if [ "$BACKUPMENSAL" = "yes" ]; then
	if [ $DDM = "01" ]; then
		echo
		echo ---------------------- Backup Mensal---------------------------------
		echo
		echo
		echo Removendo Backup Mensal do Ano Passado
		eval rm -fv "$BACKUPDIR/mensal/Backup-PLESK-*-$MES"
		echo 
		echo 
		echo Executando Backup Mensal
		/usr/local/psa/bin/pleskbackup -v -v -v all--skip-logs $BACKUPDIR/mensal/Backup-PLESK-$HOJE-$MES
		echo
		echo ----------------------------------------------------------------------
	fi
fi

if [ "$BACKUPSEMANAL" = "yes" ]; then
	if [ $DNDS = $DIABACKUPSEMANAL ]; then
		echo
		echo ---------------------- Backup Semanal---------------------------------
		echo
		echo 
		echo Verificando o Backup de 5 Semanas Anteriores...
		echo
		if [ "$SEMANA" -le 05 ]; then
			REMOVESEMANA=`expr 48 + $W`
		elif [ "$SEMANA" -lt 15 ]; then
			REMOVESEMANA=0`expr $W - 5`
		else
			REMOVESEMANA=`expr $W - 5`
		fi
		echo Fim da Verificacao
		echo
		echo Removendo Backup da Semana do Mes Passado
		eval rm -fv "$BACKUPDIR/semanal/Backup-PLESK-*-SEMANA-$REMOVESEMANA"
		echo
		echo 
		echo Executando Backup da Semana
		/usr/local/psa/bin/pleskbackup -v -v -v all --skip-logs  $BACKUPDIR/semanal/Backup-PLESK-$HOJE-SEMANA-$SEMANA
		echo
		echo ----------------------------------------------------------------------
	fi
fi

if [ "$BACKUPDIARIO" = "yes" ]; then
	echo
	echo ---------------------- Backup Diario-----------------------------------
	echo
	echo Verificando Backup da Semana Passada...
	echo
	eval rm -fv "$BACKUPDIR/diario/Backup-PLESK-*-$DDS"
	echo Fim da Verficiacao
	echo
	echo
	echo Executando backup diario - $DDS
	/usr/local/psa/bin/pleskbackup -v -v -v all --skip-logs $BACKUPDIR/diario/Backup-PLESK-$HOJE-$DDS	
	echo
	echo ----------------------------------------------------------------------
fi

echo
echo
cd /backup
echo ----------------------- ESPACO OCUPADO NO DIRETORIO BACKUP -------------------
du -h
echo
echo
echo ----------------------- LISTAGEM DO DIRETORIO BACKUP -------------------------
ls -lhR
echo
echo
echo


#mysqldump -u$MYSQLUSER -p$MYSQLPASS --all-databases > /backup/mySQL_Dump-$HOJE.sql
