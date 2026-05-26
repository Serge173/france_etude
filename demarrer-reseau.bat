@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo.
echo ========================================
echo   France Etude - serveur reseau local
echo ========================================
echo.

for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /c:"IPv4"') do (
  set "ip=%%a"
  setlocal enabledelayedexpansion
  set "ip=!ip:~1!"
  echo   Votre collegue peut ouvrir :
  echo   http://!ip!:8080/
  echo.
  endlocal
)

echo   Admin : http://VOTRE_IP:8080/admin/
echo   Arret : Ctrl+C dans cette fenetre
echo ========================================
echo.

php -S 0.0.0.0:8080 -t "%~dp0"
pause
