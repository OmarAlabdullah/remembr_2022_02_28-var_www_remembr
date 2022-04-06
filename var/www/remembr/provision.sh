
cd /vagrant
php composer.phar selfupdate
php composer.phar install

mysql -uroot -e "create database remembr"

if [ -e "remembr.sqldump" ]
then
	mysql -uroot remembr < remembr.sqldump
fi