@echo off
cd ../../..
cd vendor/php7tool/vendor/bin
php bin git/pull
php bin git/push
pause