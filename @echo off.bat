@echo off
echo Génération de la structure du projet...
tree /F /A /I "vendor" /I "node_modules" > structure.txt
echo Structure générée dans structure.txt