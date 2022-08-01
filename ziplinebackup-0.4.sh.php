#!/bin/sh
#=====================================================================
#
#
#       Zipline Backup (for MediaTemple or cPanel)
#			with local and online rotating backups
#          		by Brett Wilcox http://www.brettwilcox.com
#
#			Website - http://www.ziplinebackup.com
#			Project Page - http://code.google.com/p/ziplinebackup/
#			Check back often for the latest versions of this script!
#
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
#=====================================================================

#=====================================================================
# Set the following variables to your system needs
#=====================================================================

# A list of website directories to back up e.g. "site1.com example2.net"
# for cPanel users, change this to public_html
WEBSITES="site1.com site2.com site3.com"

# MediaTemple Number
# For cPanel users, change this to your username
NUMBER="XXXXXX"

# Selecting yes will allow you to have online backups
# If you select yes, make sure to enable a syncing service below such
# as amazon or rackspace
ONLINEBACKUP=no

#=====================================================================
# Rackspace Cloud Files
#=====================================================================

# Enable transfer to Rackspace Cloud Files
ENABLERS=no

# Your Rackspace API Key
RSAPIKEY="enterkeyhere"

# Your Rackspace Username
RSUSER="username"

# Rackpace Cloud Files Container a.k.a. directory to upload the backups into
RSCONTAINER="containername"

#=====================================================================
# Amazon S3 Backup Options
#=====================================================================

# SEE DOCUMENTATION BELOW FOR INSTALLING AMAZON S3Sync

# Enable transfer to Amazon S3
ENABLES3=no

# The S3 bucket a.k.a. directory to upload the backups into
S3BUCKET="bucketname"

# The directory where s3sync is installed
S3SYNCDIR="/home/$NUMBER/data/s3sync"

#=====================================================================
#       ADVANCED OPTIONS 
# ( Don't Change these unless you know what you are doing )===========
#=====================================================================

# The directory where all website domain directories reside
# for cPanel users, change to /home/$NUMBER
DOMAINDIR="/home/$NUMBER/domains"

# Backup Version Control Repository to Online Storage? yes or no (Must be lowercase)
VERSIONCONTROLBACKUP=no

# Version Control Repository Location
VERSIONCONTROLDIR="/home/$NUMBER/data/svn"

# Backup Version Control Repository to Online Storage? yes or no (Must be lowercase)
SCRIPTSBACKUP=no

# Version Control Repository Location
SCRIPTSDIR="/home/$NUMBER/data/scripts"

# Backup directory location e.g /backups
BACKUPDIR="/home/$NUMBER/data/site_backups"

# Data directory location e.g /data
# At the moment this is just used for making the data directory if it
# does not exist. (mainly for cPanel users or other system users)
DATADIR="/home/$NUMBER/data/"

# The TEMP directory is what is transferred to Online Storage Backup
TEMP="/home/$NUMBER/data/temp"

# Do you want database backups to Online Storage? yes or no (Must be lowercase)
# This will backup and transfer the database_backups folder
# See documentation below for setting this up
DATABASEBACKUP=no

# The directory where rotating Database Backups are located
DATABASEDIR="/home/$NUMBER/data/database_backups"

# Create daily backups that are rotated weekly? yes or no (Must be lowercase)
DAILYBACKUP=yes

# Create weekly backups that are kept for 5 weeks? yes or no (Must be lowercase)
WEEKLYBACKUP=yes

# Create monthly non-deleting backups? yes or no (Must be lowercase)
MONTHLYBACKUP=yes

# Backup Daily Backups to Online Storage? yes or no (Must be lowercase)
DAILYONLINE=yes

# Backup Weekly Backups to Online Storage? yes or no (Must be lowercase)
WEEKLYONLINE=yes

# Backup Monthly Backups to Online Storage? yes or no (Must be lowercase)
MONTHLYONLINE=yes

# Which day do you want weekly backups to run? (1 to 7 where 1 is Monday)
DOWEEKLY=6

#=====================================================================
# About this script
#=====================================================================
#
# This script creates a site_backups folder.  In this folder it creates
# another three folders - daily, weekly and monthly.  
#
# It first looks to see if it is the first day of the month.  If it is, 
# then it creates a tar.gz of the month and places it into the monthly folder.
#
# Next it looks to see if it is the specific day of the week according
# to what you have specified in the settings.  If it is, then it creates
# a tar.gz of the week and places it into the weekly folder.
#
# Then it creates a tar.gz of the day and places it into the daily folder.
#
# Then if you have the online storage backup enabled, it will start by
# creating a tar.gz of each of the websites that you have in your daily,
# weekly, and monthly folders.  It places these into the temp folder.
#
# Once it has gone through and created all of the tar.gz files of the
# websites, it then looks to see if you have database backups enabled.
#
# If you do, then it tar.gz that folder that is specified in the settings
# and coppies it over to the temp folder.
#
# Next the script looks to see what online backup services you have enabled.
# It will then go through and copy whatever is in the temp folder to the
# online storage of your choice.
#
# The script then deletes everything out of the temp folder.
#
# You should then have full backups both locally and at the online
# storage of your choie.
#
#=====================================================================
# Please Note!!
#=====================================================================
#
# I take no resposibility for any data loss or corruption when using
# this script..
# This script will not help in the event of a hard drive crash if you
# have not enabled the Online Storage Backup options and setup a service.
#
# Please see Documentation below on how to setup one of these services.
#
# Happy backing up...
#
#=====================================================================
# Backup Rotation..
#=====================================================================
#
# Daily Backups are rotated weekly..
# Weekly Backups are run by default on Saturday Morning when
# cron script is run...Can be changed with DOWEEKLY setting..
# Weekly Backups are rotated on a 5 week cycle..
# Monthly Backups are run on the 1st of the month..
# Monthly Backups are NOT rotated automatically...
#
#=====================================================================
# Documantation
#=====================================================================
#
# Setup a cron job to run ONCE daily during off peak hours.
# This is the command that I would recommend using:
#
#		sh /home/XXXXX/data/scripts/serverbackup.sh > /dev/null
#
# Replace XXXXX with your number or username
#
# Also please notice the location.  I would recommend using the location
# /home/XXXXX/data/scripts/ as this is the most compatible location
# if you plan on backing up subversion repositories and enabling the
# system to also backup the scripts themselves.
#
# I will add more documentation at a later date.
#
#=====================================================================
# Restoring
#=====================================================================
#
# Upload the file to the server or keep the file on your local computer
# You will then need to uncompress the file. browse to the directory
# using the cd command.
# Next you will need to uncompress the backup file.
# eg.
# gunzip backupfile.tar.gz
#
# It will then uncompress the file into its own folder where you can
# then use a cp file command and copy the files to wherever you are
# serving up your public files.
#
# If using cPanel and you created a folder called restore in a
# root folder called data
# cp -r /home/XXXXX/restore/* /home/XXXXX/public_html
#
# If using MediaTemple and you created a folder called restore in a
# root folder called data
# cp -r /home/XXXXX/data/restore/* /home/XXXXX/domains/example.com
#
# Lets hope you never have to use this... :)
#
#=====================================================================
# Change Log
#=====================================================================
#
# Ver 0.4 - (2009-09-29)
#	Renamed script from Online Storage Backup script to Zipline Backup
#
#	Added domain name for the project - http://www.ziplinebackup.com
#
#	Added a google code project page - http://code.google.com/p/ziplinebackup/
#
#	Changed naming conventions throughout script to reflect changes and ad links
#
# VER 0.3 - (2009-09-27)
#	Added suport for Rackspace Cloud Files - And it is SLICK!
#
#	Added support to upload version control repositories to online storage
#
#	Added support to upload the scripts folder to online storage.
#	I would like in the future to have this rotating as well.
#
# VER 0.2 - (2009-09-26)
#	Added logic to weekly and monthly transfers to Amazon S3
#	so that it will not transfer every day.  It will save bandwidth (money)
#
#	Changes made for future compatibility with other online syncing
#	services such as Rackspace Cloud Files
#		
#	Spelling Mistakes and typos
#
#	Renamed the temporary directory from tmp to temp for clarification
#
#	Moved the temp directory out of S3Sync folder for future compatibility
#
#	Renamed Script from Amazon S3 Backup Script to Zipline Backup
#	for future compatibility with other online storage services
#
#	Added information on what this script does and how it functions
#
#	Added option to daily, weekly and monthly local backups to
#	enable or disable
#
#	Made it to where it will create a data directory if it does not exist
#	(this is mainly for cPanel support)
#
#	Started documentation for chaging directory structure for cPanel users
#	though it is unsupported at this time as it has not been tested.
#
#	Renamed DATABASE to DATABASEDIR for better nameing convention
#
#	Added restore documentation
#
#	Added backup rotation documentation
#
# VER 0.1 - (2009-09-20)
#	Initial Release
#
#=====================================================================
#=====================================================================
#=====================================================================
#
# Should not need to be modified from here down!!
#
#=====================================================================
#=====================================================================
#=====================================================================
VER=0.4										# Version Number
DATE=`date +%Y-%m-%d_%Hh%Mm`				# Datestamp e.g 2002-09-21_04Hh20Mm
SHORTDATE=`date +%Y-%m-%d`					# Short Date eg 2002-09-21
DOW=`date +%A`								# Day of the week e.g. Monday
DNOW=`date +%u`								# Day number of the week 1 to 7 where 1 represents Monday
DOM=`date +%d`								# Date of the Month e.g. 27
M=`date +%B`								# Month e.g January
W=`date +%V`								# Week Number e.g 37

# Create required directories
if [ ! -e "$DATADIR" ]							# Check TEMP Directory exists
	then
	mkdir -p "$DATADIR"
fi

if [ ! -e "$TEMP" ]							# Check TEMP Directory exists
	then
	mkdir -p "$TEMP"
fi

if [ ! -e "$BACKUPDIR" ]					# Check Backup Directory exists.
	then
	mkdir -p "$BACKUPDIR"
fi

if [ ! -e "$BACKUPDIR/daily" ]				# Check Daily Directory exists.
	then
	mkdir -p "$BACKUPDIR/daily"
fi

if [ ! -e "$BACKUPDIR/weekly" ]				# Check Weekly Directory exists.
	then
	mkdir -p "$BACKUPDIR/weekly"
fi

if [ ! -e "$BACKUPDIR/monthly" ]			# Check Monthly Directory exists.
	then
	mkdir -p "$BACKUPDIR/monthly"
fi

echo
echo ======================================================================
echo Zipline Backup $VER
echo by Brett Wilcox
echo www.ziplinebackup.com
echo ======================================================================
echo
echo
echo ======================================================================
echo Website Backup Start Time `date`
echo ======================================================================
echo

for SITE in $WEBSITES
	do
	
	# Create Seperate directory for each Website
	if [ ! -e "$BACKUPDIR/daily/$SITE" ]		# Check Daily Website Directory exists.
		then
		mkdir -p "$BACKUPDIR/daily/$SITE"
	fi

	if [ ! -e "$BACKUPDIR/weekly/$SITE" ]		# Check Weekly Website Directory exists.
		then
		mkdir -p "$BACKUPDIR/weekly/$SITE"
	fi
	
	if [ ! -e "$BACKUPDIR/monthly/$SITE" ]		# Check Weekly Website Directory exists.
		then
		mkdir -p "$BACKUPDIR/monthly/$SITE"
	fi

	# Monthly Backup
	if [ "$MONTHLYBACKUP" = "yes" ]; then
		if [ $DOM = "01" ]; then
			echo
			echo Monthly Backup of Website \( $SITE \)
			echo
			echo
				echo Removing $SITE Duplicate monthly backup
				eval rm -fv $BACKUPDIR/weekly/$SITE/$SHORTDATE.*.tar.gz
				echo
				echo Performing Monthly Backup of $SITE
				tar -czf $BACKUPDIR/monthly/$SITE/$DATE.$M.tar.gz $DOMAINDIR/$SITE
				echo
			echo ----------------------------------------------------------------------
		fi
	fi

	# Weekly Backup
	if [ "$WEEKLYBACKUP" = "yes" ]; then
		if [ $DNOW = $DOWEEKLY ]; then
			echo
			echo Weekly Backup of Website \( $SITE \)
			echo
			echo Rotating 5 weeks Backups...
			echo
				if [ "$W" -le 05 ];then
					REMW=`expr 48 + $W`
				elif [ "$W" -lt 15 ];then
					REMW=0`expr $W - 5`
				else
					REMW=`expr $W - 5`
				fi
			eval rm -fv "$BACKUPDIR/weekly/$SITE/week.$REMW.*"
			echo Done Rotating
			echo
				echo Removing $SITE Duplicate weekly backup
				eval rm -fv $BACKUPDIR/weekly/$SITE/*.$W.*.tar.gz
				echo 
				echo Performing Weekly Backup of $SITE
				tar -czf $BACKUPDIR/weekly/$SITE/week.$W.$DATE.$DOW.tar.gz $DOMAINDIR/$SITE
				echo
			echo ----------------------------------------------------------------------
		fi
	fi

	# Daily Backup
	if [ "$DAILYBACKUP" = "yes" ]; then
		echo
		echo Daily Backup of Website \( $SITE \)
		echo
		echo Rotating last weeks Backup...
		echo
			eval rm -fv "$BACKUPDIR/daily/$SITE/*.$DOW.*"
		echo Done Rotating
		echo
			echo Removing $SITE Duplicate daily backup
			eval rm -fv $BACKUPDIR/daily/$SITE/*.$W.*.tar.gz
			echo
			echo Performing Daily Backup of $SITE
			tar -czf $BACKUPDIR/daily/$SITE/$DATE.$DOW.tar.gz $DOMAINDIR/$SITE
			echo
		echo ----------------------------------------------------------------------
	fi
	
done

echo
echo ======================================================================
echo Website Backup End Time `date`
echo ======================================================================
echo

if [ "$ONLINEBACKUP" = "yes" ]; then

echo
echo ======================================================================
echo Website TEMP Copy Backup Start Time `date`
echo ======================================================================
echo

# Create tars of files in TEMP folder for transfer to Online Storage Backup

for WEBSITE in $WEBSITES
	do

		# Monthly
		if [ "$MONTHLYONLINE" = "yes" ]; then
			if [ $DOM = "01" ]; then
				echo Monthly backup \for transfer to Online Storage Backup of $WEBSITE
				echo
					tar -czf $TEMP/$WEBSITE.monthly.tar.gz $BACKUPDIR/monthly/$WEBSITE
				echo
				echo ----------------------------------------------------------------------
				echo
			fi
		fi
		
		# Weekly
		if [ "$WEEKLYONLINE" = "yes" ]; then
			if [ $DNOW = $DOWEEKLY ]; then
				echo Weekly backup \for transfer to Online Storage Backup of $WEBSITE
				echo
					tar -czf $TEMP/$WEBSITE.weekly.tar.gz $BACKUPDIR/weekly/$WEBSITE
				echo
				echo ----------------------------------------------------------------------
				echo
			fi
		fi
	
		# Daily
		if [ "$DAILYONLINE" = "yes" ]; then
			echo Daily backup \for transfer to Online Storage Backup of $WEBSITE
			echo
				tar -czf $TEMP/$WEBSITE.daily.tar.gz $BACKUPDIR/daily/$WEBSITE
			echo
			echo ----------------------------------------------------------------------
			echo
		fi
done

echo
echo ======================================================================
echo Website TEMP Copy Backup End Time `date`
echo ======================================================================
echo

if [ "$SCRIPTSBACKUP" = "yes" ]; then
	
echo
echo ======================================================================
echo Scripts Backup Start Time `date`
echo ======================================================================
echo

# Scripts Backup
echo `date` ": Backing scripts..." >> $BACKUPDIR/backup.log
echo Backing up Scripts from $SCRIPTSEDIR
	tar -czf $TEMP/scripts.tar.gz $SCRIPTSDIR
	echo Complete

echo
echo ======================================================================
echo Scripts Backup End Time `date`
echo ======================================================================
echo

fi

if [ "$DATABASEBACKUP" = "yes" ]; then
	
echo
echo ======================================================================
echo Database Backup Start Time `date`
echo ======================================================================
echo

# Databases Backup
echo `date` ": Backing up revolving databases..." >> $BACKUPDIR/backup.log
echo Backing up databases from $DATABASEDIR
	tar -czf $TEMP/databases.tar.gz $DATABASEDIR
	echo Complete

echo
echo ======================================================================
echo Database Backup End Time `date`
echo ======================================================================
echo

fi

if [ "$VERSIONCONTROLBACKUP" = "yes" ]; then
	
echo
echo ======================================================================
echo Version Control Backup Start Time `date`
echo ======================================================================
echo

# Version Control Backup
echo `date` ": Backing up Version Control files..." >> $BACKUPDIR/backup.log
echo Backing up Version Control files from $VERSIONCONTROLDIR
	tar -czf $TEMP/version.control.tar.gz $VERSIONCONTROLDIR
	echo Complete

echo
echo ======================================================================
echo Version Control Backup End Time `date`
echo ======================================================================
echo

fi

# Write to Backup log Saying Backup is complete
echo `date` ": Backup process complete." >> $BACKUPDIR/backup.log

echo
echo ======================================================================
echo Starting Transfer to Online Storage Backup `date`
echo ======================================================================
echo

if [ "$ENABLES3" = "yes" ]; then

	# Change directory and sync with Amazon S3
		cd $S3SYNCDIR
		echo Now transfering Backups to Amazon S3
		./s3sync.rb $TEMP/ $S3BUCKET:
	
	echo
	echo Complete
	echo
	echo ----------------------------------------------------------------------
	echo

fi

if [ "$ENABLERS" = "yes" ]; then

# Sync with the Rackspace Cloud Files

	RSLOGIN=`curl --dump-header - -s -H "X-Storage-User: $RSUSER" \
			-H "X-Storage-Pass: $RSAPIKEY" "https://auth.api.rackspacecloud.com/v1.0"`
	RSTOKEN=`echo "$RSLOGIN" | grep ^X-Storage-Token | sed 's/.*: //' | tr -d "\r\n"`
	RSURL=`echo "$RSLOGIN" | grep ^X-Storage-Url | sed 's/.*: //' | tr -d "\r\n"`
	
	echo Now transfering Backups to Rackspace Cloud Files
	echo

	for THING in $WEBSITES
		do
			# Monthly
			if [ "$MONTHLYONLINE" = "yes" ]; then
				if [ $DOM = "01" ]; then
					echo Uploading $THING Monthly Backup to Rackspace... 
					curl -s -X PUT -T $TEMP/$THING.monthly.tar.gz \
					-H "Content-Type: application/x-gzip" \
					-H "X-Auth-Token: $RSTOKEN" \
					-H "X-Object-Meta-$THING backup: `date`" \
					$RSURL/$RSCONTAINER/$THING.monthly.tar.gz
					echo Finished Uploading $THING.monthly.tar.gz
					echo
				fi
			fi
			
			# Weekly
			if [ "$WEEKLYONLINE" = "yes" ]; then
				if [ $DNOW = $DOWEEKLY ]; then
					echo Uploading $THING Weekly Backup to Rackspace... 
					curl -s -X PUT -T $TEMP/$THING.weekly.tar.gz \
					-H "Content-Type: application/x-gzip" \
					-H "X-Auth-Token: $RSTOKEN" \
					-H "X-Object-Meta-$THING backup: `date`" \
					$RSURL/$RSCONTAINER/$THING.weekly.tar.gz
					echo Finished Uploading $THING.weekly.tar.gz
					echo
				fi
			fi
			
			# Daily
			if [ "$DAILYONLINE" = "yes" ]; then
				echo Uploading $THING Daily Backup to Rackspace...
				curl -s -X PUT -T $TEMP/$THING.daily.tar.gz \
				-H "Content-Type: application/x-gzip" \
				-H "X-Auth-Token: $RSTOKEN" \
				-H "X-Object-Meta-$THING backup: `date`" \
				$RSURL/$RSCONTAINER/$THING.daily.tar.gz
				echo Finished Uploading $THING.daily.tar.gz
				echo
			fi
	done
	
	#Scripts
	#Needs to be out of the loop so it does not send multiple times
	if [ "$SCRIPTSBACKUP" = "yes" ]; then
		echo Uploading Scripts Backup to Rackspace... 
		curl -s -X PUT -T $TEMP/scripts.tar.gz \
		-H "Content-Type: application/x-gzip" \
		-H "X-Auth-Token: $RSTOKEN" \
		-H "X-Object-Meta-scripts backup: `date`" \
		$RSURL/$RSCONTAINER/scripts.tar.gz
		echo Finished Uploading scripts.tar.gz
		echo
	fi
	
	#Databases
	#Needs to be out of the loop so it does not send multiple times
	if [ "$DATABASEBACKUP" = "yes" ]; then
		echo Uploading Databases Backup to Rackspace... 
		curl -s -X PUT -T $TEMP/databases.tar.gz \
		-H "Content-Type: application/x-gzip" \
		-H "X-Auth-Token: $RSTOKEN" \
		-H "X-Object-Meta-databases backup: `date`" \
		$RSURL/$RSCONTAINER/databases.tar.gz
		echo Finished Uploading databases.tar.gz
		echo
	fi
	
	#Version Control
	#Needs to be out of the loop so it does not send multiple times
	if [ "$VERSIONCONTROLBACKUP" = "yes" ]; then
		echo Uploading Version Control Backup to Rackspace... 
		curl -s -X PUT -T $TEMP/version.control.tar.gz \
		-H "Content-Type: application/x-gzip" \
		-H "X-Auth-Token: $RSTOKEN" \
		-H "X-Object-Meta-version control backup: `date`" \
		$RSURL/$RSCONTAINER/version.control.tar.gz
		echo Finished Uploading version.control.tar.gz
		echo
	fi

echo Complete
echo
echo ----------------------------------------------------------------------
echo

fi

echo
echo ======================================================================
echo Tranfer to Online Storage Backup has finished `date`
echo ======================================================================
echo
	
# Remove all Files in Temp folder
	echo Removing all files \in the TEMP directory
	rm -fv $TEMP/*.*

fi

echo
echo
echo
echo ======================================================================
echo Zipline Backup has finished running. `date`
echo ======================================================================
echo
#
# Thans for looking at the source!  Please share back any changes that you
# make to the project page -  
#
# I hope you learned something new! I sure did while making this project.
#