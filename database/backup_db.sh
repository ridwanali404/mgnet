#!/bin/bash

cd /var/www/cr
git stash -u
/usr/bin/mysqldump -uroot -pdBase404@ --opt cr > /var/www/cr/database/cr.sql
git add .
git commit -m 'backup db'
git push -u origin main
git gc
cd
