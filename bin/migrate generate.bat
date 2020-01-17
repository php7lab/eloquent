@echo off
php console db:migrate:generate
pause

REM use --withConfirm=0 for skip dialog
