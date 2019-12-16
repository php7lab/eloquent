@echo off
php console db:migrate:up
pause

REM use --withConfirm=0 for skip dialog
