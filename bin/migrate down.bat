@echo off
php console db:migrate:down
pause

REM use --withConfirm=0 for skip dialog
