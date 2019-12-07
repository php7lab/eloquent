@echo off
cd ..
php console db:fixture:import
pause

REM use --withConfirm=0 for skip dialog
