To download/update dependencies:
    php composer.phar selfupdate
    php composer.phar update


### Deployment

#### Ubuntu 15.10 -- Herbert Kruitbosch

Install the web-server & co:

    sudo apt-get install mysql-server apache2 php5{,-mysql,-curl} phpmyadmin php php-gd

Install some php5 packages remembr uses:

    sudo apt-get install php5-intl php5-pecl-http php5-mcrypt php{,5}-enchant
    sudo php5enmod enchant
    sudo service apache2 restart

Export some version of the database to `remembr.sql`, then load it into your local database:

    (echo 'CREATE DATABASE remembr; use remembr; '; cat ~/remembr.sql) | mysql -uroot -p

Create a remembr-account for the database

    echo -n "Password for remembr database user: "; stty -echo; read password; stty echo; echo "grant all privileges on remembr.* to remembr@localhost identified by '$password';" | mysql -uroot -p

etc ect...


#### Installation on CentOS 7


##### System dependencies

    sudo yum install php-{intl,pecl,`enchant} hunspell-{nl,en-{US,GB}} gd


##### Directories for template cache, database entities and static file minimization

    sudo mkdir -p /var/data/remembr
    sudo mv /var/www/remembr/data/* /var/data/remembr/
    sudo rm -r /var/www/remembr/data/
    sudo ln -s /var/data/remembr /var/www/remembr/data
    sudo mkdir /var/data/remembr/twigcache
    sudo mkdir -p /var/www/remembr/module/Munee/src/cache/

    for i in Css JavaScript Image; do
        sudo mkdir -p /var/data/remembr/minify/$i
        sudo ln -s /var/data/remembr/minify/$i /var/www/remembr/module/Munee/src/cache/$i
    done

    sudo chown -R remembr:apache /var/data/remembr/twigcache data/DoctrineModule/cache/ /var/data/remembr/minify /var/data/remembr/DoctrineORMModule/Proxy
    sudo chmod -R g+wX /var/data/remembr/twigcache data/DoctrineModule/cache/ /var/data/remembr/minify /var/data/remembr/DoctrineORMModule/Proxy

##### Update php dependencies

    php composer.phar self-update
    php composer.phar update

##### Migrate database

    sudo -u remembr php ./vendor/doctrine/doctrine-module/bin/doctrine-module.php migrations:migrate

##### Protect uploads:

    (echo 'order deny,allow'; echo 'deny from all') | sudo -iu remembr tee /var/www/remembr/public/uploads/.htaccess

##### remembr js config: /var/www/remembr/public/js/remembr-config.js

    angular.module('remembrConfig', [])
       .constant('DEBUG_STATES', true)
       .constant('POLLING_INTERVAL', 10000);


##### httpd config: /etc/httpd/vhosts.d/vhosts.d

    LogFormat "%a %l %u %t \"%r\" %>s %b" threvproxy
    RemoteIPInternalProxy thproxy01.priv.tgho.nl
    RemoteIPInternalProxy thproxy02.priv.tgho.nl

    <VirtualHost *:80>
        DocumentRoot /var/www/remembr/public/
        ServerName www.remembr.com
        ServerAlias remembr.com
        ServerAlias www.remembr.nl
        ServerAlias remembr.nl
        ErrorLog logs/www.remembr.com.priv-error_log
        CustomLog logs/www.remembr.com.priv-access_log threvproxy

        Alias /robots.txt /var/www/remembr/public/robots.txt

        SetEnv APPLICATION_ENV testing

        RemoteIPHeader X-Forwarded-For

        <Directory "/var/www/remembr/public/">
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>

    <VirtualHost *:443>
        DocumentRoot /var/www/remembr/public/
        ServerName www.remembr.com
        ServerAlias remembr.com
        ServerAlias www.remembr.nl
        ServerAlias remembr.nl

        ErrorLog logs/www.remembr.com.priv-error_log
        CustomLog logs/www.remembr.com.priv-access_log common

        Alias /robots.txt /var/www/remembr/public/robots.txt

        SSLEngine on
        SSLStrictSNIVHostCheck on
        SSLCertificateFile /etc/pki/tls/certs/STAR_remembr_com_sha256.crt
        SSLCertificateKeyFile /etc/pki/tls/private/STAR_remembr_com_sha256.key
        SSLCertificateChainFile /etc/pki/tls/certs/STAR_remembr_com_sha256.chain

        SetEnv APPLICATION_ENV testing

        <Directory "/var/www/remembr/public/">
            Options Indexes FollowSymLinks MultiViews
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>
