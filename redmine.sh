#!/bin/sh
status=$(curl -sL -w "%{http_code}" "redmine.devhouse.com.br" -o /dev/null)

if [ `expr match "$status" "503"` -gt 0 ] ; then
    cd /home/devhouse/redmine
    ./start.sh
fi
