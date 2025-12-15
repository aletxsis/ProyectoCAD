#!/bin/bash

echo "===================================="
echo "  Proyecto CAD - Inicio Rápido"
echo "===================================="
echo ""

# Verificar Docker
if ! command -v docker &> /dev/null; then
    echo "ERROR: Docker no está instalado"
    echo "Instala Docker desde: https://www.docker.com/products/docker-desktop"
    exit 1
fi

echo "Docker encontrado!"
echo ""

# Crear archivo de configuración
if [ ! -f "config/database.php" ]; then
    cp config/database.example.php config/database.php
    echo "Archivo database.php creado"
else
    echo "database.php ya existe"
fi
echo ""

# Iniciar contenedores
echo "Iniciando contenedores Docker..."
echo "Esto puede tardar unos minutos la primera vez..."
docker-compose up -d

if [ $? -ne 0 ]; then
    echo ""
    echo "ERROR: No se pudieron iniciar los contenedores"
    exit 1
fi

echo ""
echo "===================================="
echo "  Contenedores iniciados!"
echo "===================================="
echo ""
echo "Aplicación web: http://localhost:8080"
echo "phpMyAdmin:     http://localhost:8081"
echo ""
echo "Credenciales por defecto:"
echo "  Usuario: admin"
echo "  Contraseña: admin123"
echo ""
echo "Presiona Ctrl+C para detener los logs"
echo "===================================="
echo ""

docker-compose logs -f web
