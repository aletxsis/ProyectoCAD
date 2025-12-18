# 🔄 Guía de Despliegue con Load Balancer en Azure

## Arquitectura del Sistema

```
                          ┌─────────────────────┐
                          │   Azure Load        │
                          │   Balancer          │
                          │   (Public IP)       │
                          └──────────┬──────────┘
                                     │
                    ┌────────────────┼────────────────┐
                    │                │                │
            ┌───────▼──────┐ ┌──────▼───────┐ ┌─────▼────────┐
            │   VM 1       │ │   VM 2       │ │   VM 3       │
            │   Web Server │ │   Web Server │ │   Web Server │
            │   (PHP 8.2)  │ │   (PHP 8.2)  │ │   (PHP 8.2)  │
            └───────┬──────┘ └──────┬───────┘ └─────┬────────┘
                    │                │                │
                    └────────────────┼────────────────┘
                                     │
                          ┌──────────▼──────────┐
                          │   Azure Database    │
                          │   for MySQL         │
                          │   (Flexible Server) │
                          └─────────────────────┘
                                     │
                          ┌──────────▼──────────┐
                          │   Azure Blob        │
                          │   Storage           │
                          │   (Fotos Perfil)    │
                          └─────────────────────┘
```

## 📋 Requisitos Previos

- Suscripción activa de Azure
- Azure CLI instalado localmente
- Cuenta con permisos de Owner o Contributor
- Dominio DNS (opcional, pero recomendado)

## 🚀 Paso 1: Crear Grupo de Recursos

```bash
# Variables de configuración
RESOURCE_GROUP="saes-rg"
LOCATION="eastus"
PROJECT_NAME="saes20"

# Crear grupo de recursos
az group create \
  --name $RESOURCE_GROUP \
  --location $LOCATION
```

## 🗄️ Paso 2: Crear Azure Database for MySQL

```bash
# Variables de base de datos
DB_SERVER_NAME="${PROJECT_NAME}-mysql"
DB_ADMIN_USER="saesadmin"
DB_ADMIN_PASSWORD="SaesAdmin2024!@#"
DB_NAME="proyecto_cad"

# Crear servidor MySQL
az mysql flexible-server create \
  --resource-group $RESOURCE_GROUP \
  --name $DB_SERVER_NAME \
  --location $LOCATION \
  --admin-user $DB_ADMIN_USER \
  --admin-password $DB_ADMIN_PASSWORD \
  --sku-name Standard_B1ms \
  --tier Burstable \
  --version 8.0 \
  --storage-size 32 \
  --public-access 0.0.0.0

# Crear base de datos
az mysql flexible-server db create \
  --resource-group $RESOURCE_GROUP \
  --server-name $DB_SERVER_NAME \
  --database-name $DB_NAME

# Configurar SSL
az mysql flexible-server parameter set \
  --resource-group $RESOURCE_GROUP \
  --server-name $DB_SERVER_NAME \
  --name require_secure_transport \
  --value ON
```

## 📦 Paso 3: Crear Azure Blob Storage

```bash
# Variables de storage
STORAGE_ACCOUNT="${PROJECT_NAME}storage"
STORAGE_CONTAINER="saes-photos"

# Crear cuenta de almacenamiento
az storage account create \
  --name $STORAGE_ACCOUNT \
  --resource-group $RESOURCE_GROUP \
  --location $LOCATION \
  --sku Standard_LRS \
  --kind StorageV2 \
  --access-tier Hot

# Obtener connection string
STORAGE_CONNECTION=$(az storage account show-connection-string \
  --name $STORAGE_ACCOUNT \
  --resource-group $RESOURCE_GROUP \
  --query connectionString -o tsv)

# Crear container
az storage container create \
  --name $STORAGE_CONTAINER \
  --connection-string $STORAGE_CONNECTION \
  --public-access blob

# Obtener key para la aplicación
STORAGE_KEY=$(az storage account keys list \
  --account-name $STORAGE_ACCOUNT \
  --resource-group $RESOURCE_GROUP \
  --query '[0].value' -o tsv)

echo "Storage Account: $STORAGE_ACCOUNT"
echo "Storage Key: $STORAGE_KEY"
```

## 🖥️ Paso 4: Crear Virtual Network

```bash
# Variables de red
VNET_NAME="${PROJECT_NAME}-vnet"
SUBNET_NAME="${PROJECT_NAME}-subnet"

# Crear VNet
az network vnet create \
  --resource-group $RESOURCE_GROUP \
  --name $VNET_NAME \
  --address-prefix 10.0.0.0/16 \
  --subnet-name $SUBNET_NAME \
  --subnet-prefix 10.0.1.0/24

# Crear Network Security Group
NSG_NAME="${PROJECT_NAME}-nsg"
az network nsg create \
  --resource-group $RESOURCE_GROUP \
  --name $NSG_NAME

# Regla para HTTP
az network nsg rule create \
  --resource-group $RESOURCE_GROUP \
  --nsg-name $NSG_NAME \
  --name Allow-HTTP \
  --priority 100 \
  --source-address-prefixes '*' \
  --destination-port-ranges 80 \
  --access Allow \
  --protocol Tcp

# Regla para HTTPS
az network nsg rule create \
  --resource-group $RESOURCE_GROUP \
  --nsg-name $NSG_NAME \
  --name Allow-HTTPS \
  --priority 110 \
  --source-address-prefixes '*' \
  --destination-port-ranges 443 \
  --access Allow \
  --protocol Tcp

# Regla para SSH (solo para administración)
az network nsg rule create \
  --resource-group $RESOURCE_GROUP \
  --nsg-name $NSG_NAME \
  --name Allow-SSH \
  --priority 120 \
  --source-address-prefixes 'TU_IP_PUBLICA' \
  --destination-port-ranges 22 \
  --access Allow \
  --protocol Tcp
```

## 🔄 Paso 5: Crear Load Balancer

```bash
# Variables de Load Balancer
LB_NAME="${PROJECT_NAME}-lb"
LB_FRONTEND="lb-frontend"
LB_BACKEND="lb-backend"
LB_PUBLIC_IP="${PROJECT_NAME}-lb-ip"

# Crear IP pública para el Load Balancer
az network public-ip create \
  --resource-group $RESOURCE_GROUP \
  --name $LB_PUBLIC_IP \
  --sku Standard \
  --allocation-method Static

# Crear Load Balancer
az network lb create \
  --resource-group $RESOURCE_GROUP \
  --name $LB_NAME \
  --sku Standard \
  --public-ip-address $LB_PUBLIC_IP \
  --frontend-ip-name $LB_FRONTEND \
  --backend-pool-name $LB_BACKEND

# Crear Health Probe
az network lb probe create \
  --resource-group $RESOURCE_GROUP \
  --lb-name $LB_NAME \
  --name http-probe \
  --protocol http \
  --port 80 \
  --path /health.php \
  --interval 15 \
  --threshold 2

# Crear regla de balanceo para HTTP
az network lb rule create \
  --resource-group $RESOURCE_GROUP \
  --lb-name $LB_NAME \
  --name http-rule \
  --protocol tcp \
  --frontend-port 80 \
  --backend-port 80 \
  --frontend-ip-name $LB_FRONTEND \
  --backend-pool-name $LB_BACKEND \
  --probe-name http-probe \
  --idle-timeout 15 \
  --enable-tcp-reset true

# Crear regla de balanceo para HTTPS
az network lb rule create \
  --resource-group $RESOURCE_GROUP \
  --lb-name $LB_NAME \
  --name https-rule \
  --protocol tcp \
  --frontend-port 443 \
  --backend-port 443 \
  --frontend-ip-name $LB_FRONTEND \
  --backend-pool-name $LB_BACKEND \
  --probe-name http-probe

# Obtener IP pública del Load Balancer
LB_IP=$(az network public-ip show \
  --resource-group $RESOURCE_GROUP \
  --name $LB_PUBLIC_IP \
  --query ipAddress -o tsv)

echo "Load Balancer IP: $LB_IP"
```

## 💻 Paso 6: Crear VMs (3 instancias)

```bash
# Script de inicialización cloud-init
cat > cloud-init.yaml << 'EOF'
#cloud-config
package_update: true
package_upgrade: true

packages:
  - apache2
  - php8.2
  - php8.2-mysql
  - php8.2-xml
  - php8.2-mbstring
  - php8.2-curl
  - php8.2-gd
  - libapache2-mod-php8.2
  - git
  - unzip

runcmd:
  - a2enmod rewrite
  - a2enmod ssl
  - systemctl restart apache2
  - mkdir -p /var/www/html/saes
  - chown -R www-data:www-data /var/www/html
  - chmod -R 755 /var/www/html
EOF

# Crear 3 VMs
for i in 1 2 3; do
  VM_NAME="${PROJECT_NAME}-vm${i}"
  NIC_NAME="${VM_NAME}-nic"
  PUBLIC_IP_NAME="${VM_NAME}-ip"
  
  echo "Creando VM ${i}..."
  
  # Crear IP pública para la VM (para acceso SSH)
  az network public-ip create \
    --resource-group $RESOURCE_GROUP \
    --name $PUBLIC_IP_NAME \
    --sku Standard \
    --allocation-method Static
  
  # Crear NIC
  az network nic create \
    --resource-group $RESOURCE_GROUP \
    --name $NIC_NAME \
    --vnet-name $VNET_NAME \
    --subnet $SUBNET_NAME \
    --network-security-group $NSG_NAME \
    --public-ip-address $PUBLIC_IP_NAME \
    --lb-name $LB_NAME \
    --lb-address-pools $LB_BACKEND
  
  # Crear VM
  az vm create \
    --resource-group $RESOURCE_GROUP \
    --name $VM_NAME \
    --nics $NIC_NAME \
    --image Ubuntu2204 \
    --size Standard_B2s \
    --admin-username azureuser \
    --generate-ssh-keys \
    --custom-data cloud-init.yaml
  
  echo "VM ${i} creada: ${VM_NAME}"
done
```

## 📝 Paso 7: Crear Health Check Endpoint

Crear el archivo `/var/www/html/health.php` en cada VM:

```php
<?php
// Health check endpoint para Azure Load Balancer
header('Content-Type: application/json');

$status = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => gethostname()
];

// Verificar conexión a base de datos
try {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'proyecto_cad';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS') ?: '';
    
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_TIMEOUT => 2
    ]);
    
    $status['database'] = 'connected';
} catch (PDOException $e) {
    $status['status'] = 'unhealthy';
    $status['database'] = 'disconnected';
    http_response_code(503);
}

echo json_encode($status);
```

## 🔧 Paso 8: Desplegar Aplicación en VMs

Script para desplegar en cada VM:

```bash
#!/bin/bash

# Configurar variables
MYSQL_HOST="${DB_SERVER_NAME}.mysql.database.azure.com"
MYSQL_DB=$DB_NAME
MYSQL_USER="${DB_ADMIN_USER}"
MYSQL_PASS="${DB_ADMIN_PASSWORD}"

# Variables de Azure Storage
AZURE_STORAGE_ACCOUNT=$STORAGE_ACCOUNT
AZURE_STORAGE_KEY=$STORAGE_KEY

# Clonar repositorio
cd /var/www/html
sudo git clone https://github.com/TU_USUARIO/ProyectoCAD.git saes
cd saes

# Configurar permisos
sudo chown -R www-data:www-data /var/www/html/saes
sudo chmod -R 755 /var/www/html/saes

# Crear directorio uploads
sudo mkdir -p /var/www/html/saes/uploads
sudo chmod 777 /var/www/html/saes/uploads

# Configurar variables de entorno
sudo cat > /var/www/html/saes/.env << EOF
DB_HOST=${MYSQL_HOST}
DB_NAME=${MYSQL_DB}
DB_USER=${MYSQL_USER}
DB_PASS=${MYSQL_PASS}
DB_PORT=3306
AZURE_STORAGE_ACCOUNT=${AZURE_STORAGE_ACCOUNT}
AZURE_STORAGE_KEY=${AZURE_STORAGE_KEY}
AZURE_STORAGE_CONTAINER=saes-photos
EOF

# Configurar Apache Virtual Host
sudo cat > /etc/apache2/sites-available/saes.conf << 'VHOST'
<VirtualHost *:80>
    ServerAdmin admin@saes.mx
    DocumentRoot /var/www/html/saes/public

    <Directory /var/www/html/saes/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/saes-error.log
    CustomLog ${APACHE_LOG_DIR}/saes-access.log combined
</VirtualHost>
VHOST

# Activar sitio
sudo a2dissite 000-default.conf
sudo a2ensite saes.conf
sudo systemctl reload apache2

# Importar base de datos (solo en VM1)
if [ "$(hostname)" == "${PROJECT_NAME}-vm1" ]; then
    mysql -h ${MYSQL_HOST} -u ${MYSQL_USER} -p${MYSQL_PASS} ${MYSQL_DB} < /var/www/html/saes/database/saes_schema.sql
fi

echo "Despliegue completado en $(hostname)"
```

## 🔐 Paso 9: Configurar Session Affinity

Para que las sesiones PHP funcionen correctamente con Load Balancer:

**Opción 1: Session Affinity (Cookie-based)**

```bash
# Configurar distribución basada en IP del cliente
az network lb rule update \
  --resource-group $RESOURCE_GROUP \
  --lb-name $LB_NAME \
  --name http-rule \
  --load-distribution SourceIP
```

**Opción 2: Redis Cache (Recomendado para producción)**

```bash
# Crear Azure Cache for Redis
REDIS_NAME="${PROJECT_NAME}-redis"

az redis create \
  --resource-group $RESOURCE_GROUP \
  --name $REDIS_NAME \
  --location $LOCATION \
  --sku Basic \
  --vm-size c0

# Obtener Redis key
REDIS_KEY=$(az redis list-keys \
  --resource-group $RESOURCE_GROUP \
  --name $REDIS_NAME \
  --query primaryKey -o tsv)

echo "Redis Host: ${REDIS_NAME}.redis.cache.windows.net"
echo "Redis Key: $REDIS_KEY"
```

Configurar PHP para usar Redis:

```php
// En config/config.php
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://REDIS_HOST:6380?auth=REDIS_KEY&ssl[verify_peer]=0');
```

## 📊 Paso 10: Monitoreo y Diagnósticos

```bash
# Habilitar diagnósticos en Load Balancer
STORAGE_ACCOUNT_DIAG="${PROJECT_NAME}diag"

az storage account create \
  --name $STORAGE_ACCOUNT_DIAG \
  --resource-group $RESOURCE_GROUP \
  --location $LOCATION \
  --sku Standard_LRS

# Configurar Application Insights
INSIGHTS_NAME="${PROJECT_NAME}-insights"

az monitor app-insights component create \
  --app $INSIGHTS_NAME \
  --location $LOCATION \
  --resource-group $RESOURCE_GROUP \
  --application-type web

# Obtener Instrumentation Key
INSIGHTS_KEY=$(az monitor app-insights component show \
  --app $INSIGHTS_NAME \
  --resource-group $RESOURCE_GROUP \
  --query instrumentationKey -o tsv)

echo "Application Insights Key: $INSIGHTS_KEY"
```

## ✅ Paso 11: Verificación del Despliegue

```bash
# 1. Verificar health checks
curl http://${LB_IP}/health.php

# 2. Verificar que todas las VMs responden
for i in 1 2 3; do
  VM_IP=$(az vm show -d \
    --resource-group $RESOURCE_GROUP \
    --name ${PROJECT_NAME}-vm${i} \
    --query publicIps -o tsv)
  
  echo "Testing VM $i (${VM_IP}):"
  curl http://${VM_IP}/health.php
  echo ""
done

# 3. Verificar Load Balancer stats
az network lb show \
  --resource-group $RESOURCE_GROUP \
  --name $LB_NAME \
  --query "{Name:name,IP:frontendIpConfigurations[0].publicIpAddress.id}" -o table
```

## 🎯 Pruebas de Carga

```bash
# Instalar Apache Bench
sudo apt-get install apache2-utils

# Prueba de carga básica
ab -n 1000 -c 10 http://${LB_IP}/

# Prueba de carga con sesiones
ab -n 1000 -c 10 -C "PHPSESSID=test123" http://${LB_IP}/login.php
```

## 📈 Escalado Automático (Opcional)

Crear VM Scale Set en lugar de VMs individuales:

```bash
VMSS_NAME="${PROJECT_NAME}-vmss"

az vmss create \
  --resource-group $RESOURCE_GROUP \
  --name $VMSS_NAME \
  --image Ubuntu2204 \
  --vm-sku Standard_B2s \
  --instance-count 3 \
  --vnet-name $VNET_NAME \
  --subnet $SUBNET_NAME \
  --lb $LB_NAME \
  --backend-pool-name $LB_BACKEND \
  --custom-data cloud-init.yaml \
  --upgrade-policy-mode automatic

# Configurar autoscaling
az monitor autoscale create \
  --resource-group $RESOURCE_GROUP \
  --name "${VMSS_NAME}-autoscale" \
  --resource $VMSS_NAME \
  --resource-type Microsoft.Compute/virtualMachineScaleSets \
  --min-count 2 \
  --max-count 10 \
  --count 3

# Regla: Escalar cuando CPU > 70%
az monitor autoscale rule create \
  --resource-group $RESOURCE_GROUP \
  --autoscale-name "${VMSS_NAME}-autoscale" \
  --condition "Percentage CPU > 70 avg 5m" \
  --scale out 1

# Regla: Reducir cuando CPU < 30%
az monitor autoscale rule create \
  --resource-group $RESOURCE_GROUP \
  --autoscale-name "${VMSS_NAME}-autoscale" \
  --condition "Percentage CPU < 30 avg 5m" \
  --scale in 1
```

## 🔒 Seguridad Adicional

```bash
# Configurar SSL/TLS con certificado Let's Encrypt
# (Ejecutar en cada VM)

sudo apt-get install certbot python3-certbot-apache -y

sudo certbot --apache \
  --non-interactive \
  --agree-tos \
  --email admin@tudominio.com \
  --domains saes.tudominio.com

# Renovación automática
sudo systemctl enable certbot.timer
```

## 📝 Comandos Útiles de Gestión

```bash
# Ver estado del Load Balancer
az network lb show \
  --resource-group $RESOURCE_GROUP \
  --name $LB_NAME

# Ver VMs en el backend pool
az network lb address-pool show \
  --resource-group $RESOURCE_GROUP \
  --lb-name $LB_NAME \
  --name $LB_BACKEND

# Detener todas las VMs (para ahorrar costos)
for i in 1 2 3; do
  az vm deallocate \
    --resource-group $RESOURCE_GROUP \
    --name ${PROJECT_NAME}-vm${i}
done

# Iniciar todas las VMs
for i in 1 2 3; do
  az vm start \
    --resource-group $RESOURCE_GROUP \
    --name ${PROJECT_NAME}-vm${i}
done

# Eliminar todo el despliegue
az group delete \
  --name $RESOURCE_GROUP \
  --yes --no-wait
```

## 💰 Estimación de Costos

**Costos mensuales aproximados (región East US):**

- Load Balancer Standard: $18/mes
- 3 VMs B2s: $60/mes ($20 cada una)
- Azure Database MySQL: $30/mes
- Blob Storage (100GB): $2/mes
- Redis Cache Basic: $15/mes
- Transferencia de datos: $10/mes

**Total estimado: ~$135/mes**

## 🎓 Referencias

- [Azure Load Balancer Docs](https://docs.microsoft.com/azure/load-balancer/)
- [Azure Database for MySQL](https://docs.microsoft.com/azure/mysql/)
- [Azure Blob Storage](https://docs.microsoft.com/azure/storage/blobs/)

---

**¡Despliegue completado! 🎉**

Tu aplicación SAES 2.0 ahora está corriendo con alta disponibilidad en Azure.
