@echo off
echo ====================================
echo   Proyecto CAD - Inicio Rapido
echo ====================================
echo.

echo Verificando Docker...
docker --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Docker no esta instalado o no esta en el PATH
    echo Por favor instala Docker Desktop desde: https://www.docker.com/products/docker-desktop
    pause
    exit /b 1
)

echo Docker encontrado!
echo.

echo Creando archivo de configuracion de base de datos...
if not exist "config\database.php" (
    copy "config\database.example.php" "config\database.php"
    echo Archivo database.php creado
) else (
    echo database.php ya existe
)
echo.

echo Iniciando contenedores Docker...
echo Esto puede tardar unos minutos la primera vez...
docker-compose up -d

if errorlevel 1 (
    echo.
    echo ERROR: No se pudieron iniciar los contenedores
    pause
    exit /b 1
)

echo.
echo ====================================
echo   Contenedores iniciados!
echo ====================================
echo.
echo Aplicacion web: http://localhost:8080
echo phpMyAdmin:     http://localhost:8081
echo.
echo Credenciales por defecto:
echo   Usuario: admin
echo   Password: admin123
echo.
echo Presiona Ctrl+C para detener los logs
echo ====================================
echo.

docker-compose logs -f web
