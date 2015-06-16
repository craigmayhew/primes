#this script has only beet tested on an Amazon Linux Machine Image

#update yum packages without asking
sudo yum update -y

#install apache, php 5.6 and the mysql connector
sudo yum install -y httpd24 php56 php56-mysqlnd

#now if you want to turn the service on
sudo service httpd start

#and your webfiles should go here
#/var/www
