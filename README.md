primes webserver
======

Open Source code powering api.bigprimes.net

htaccess notes
=====

```
RewriteEngine On    # Turn on the rewriting engine

#hide git frow web
#RedirectMatch 404 "(?:.*)/(?:\.git|file_or_dir)(?:/.*)?$"

#RewriteRule . index.php 
# ?page=wuget [NC,L] 
RewriteRule ^0\.0/wu-get\.php$ index.php?page=wuget [NC,L,QSA] 
RewriteRule ^0\.0/wu-result-receiver\.php$ index.php?page=wuresult [NC,L] 
RewriteRule ^0\.0/clientwork24\.php$ index.php?page=clientwork24 [NC,L]
RewriteRule ^0\.0/clientworkall\.php$ index.php?page=clientworkall [NC,L]
RewriteRule ^$ index.php?page=404 [NC,L]

SetEnv bigprimesenvironment live
SetEnv bigprimesDBPass password
SetEnv bigprimesDBUser user
SetEnv bigprimesDBEndPoint mysql.example.bigprimes.not
SetEnv bigprimesawskey somekey
SetEnv bigprimesawssecret somesecret
SetEnv AWS_ACCESS_KEY_ID somekey
SetEnv AWS_SECRET_ACCESS_KEY Esomekey
```
