
@echo off
echo Удаление .git...
rmdir /s /q .git

echo Удаление .gitignore и .gitattributes...
del /f /q .gitignore
del /f /q .gitattributes

echo Удаление README и других Git-атрибутов...
del /f /q README.md
del /f /q LICENSE

echo Готово. Git следы удалены.
pause
