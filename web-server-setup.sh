#this script has only beet tested on an Amazon Linux Machine Image

#update yum packages without asking
sudo yum update -y

#install apache, php 5.6 and the mysql connector
sudo yum install -y httpd24 php56 php56-mysqlnd

#now if you want to turn the service on
sudo service httpd start

#and your webfiles should go here
#/var/www

#user based filesystem security
sudo groupadd www
sudo usermod -a -G www ec2-user
sudo chown -R root:www /var/www
sudo chmod 2775 /var/www
find /var/www -type d -exec sudo chmod 2775 {} +
find /var/www -type f -exec sudo chmod 0664 {} +
