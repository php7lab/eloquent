@echo off
php console db:fixture:export
pause

REM use --withConfirm=0 for skip dialog
