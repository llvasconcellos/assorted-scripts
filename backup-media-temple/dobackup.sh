/root/backup.sh > /root/backup.log
mail -s "Relatorio Backup `date +%d/%m/%Y`" "leonardo@devhouse.com.br" < /root/backup.log
mail -s "Relatorio Backup `date +%d/%m/%Y`" "denisejlle@hotmail.com" < /root/backup.log
