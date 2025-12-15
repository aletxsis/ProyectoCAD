# Configuración para Azure App Service

## Pasos para desplegar en Azure:

### 1. Crear recursos en Azure

```bash
# Login a Azure
az login

# Crear un grupo de recursos
az group create --name ProyectoCAD-RG --location eastus

# Crear Azure Database for MySQL
az mysql server create \
  --resource-group ProyectoCAD-RG \
  --name proyectocad-mysql \
  --location eastus \
  --admin-user adminuser \
  --admin-password TuPasswordSeguro123! \
  --sku-name GP_Gen5_2

# Configurar reglas de firewall para MySQL
az mysql server firewall-rule create \
  --resource-group ProyectoCAD-RG \
  --server proyectocad-mysql \
  --name AllowAzureServices \
  --start-ip-address 0.0.0.0 \
  --end-ip-address 0.0.0.0

# Crear base de datos
az mysql db create \
  --resource-group ProyectoCAD-RG \
  --server-name proyectocad-mysql \
  --name proyecto_cad

# Crear App Service Plan
az appservice plan create \
  --name ProyectoCAD-Plan \
  --resource-group ProyectoCAD-RG \
  --sku B1 \
  --is-linux

# Crear Web App
az webapp create \
  --resource-group ProyectoCAD-RG \
  --plan ProyectoCAD-Plan \
  --name proyectocad-app \
  --runtime "PHP|8.2"
```

### 2. Configurar variables de entorno en Azure Web App

```bash
az webapp config appsettings set \
  --resource-group ProyectoCAD-RG \
  --name proyectocad-app \
  --settings \
    DB_HOST="proyectocad-mysql.mysql.database.azure.com" \
    DB_NAME="proyecto_cad" \
    DB_USER="adminuser@proyectocad-mysql" \
    DB_PASS="TuPasswordSeguro123!" \
    BASE_URL="https://proyectocad-app.azurewebsites.net"
```

### 3. Desplegar código

```bash
# Opción 1: Desde Git
az webapp deployment source config \
  --name proyectocad-app \
  --resource-group ProyectoCAD-RG \
  --repo-url https://github.com/tu-usuario/tu-repo \
  --branch main \
  --manual-integration

# Opción 2: Desde carpeta local
cd /path/to/ProyectoCAD
az webapp up \
  --name proyectocad-app \
  --resource-group ProyectoCAD-RG \
  --runtime "PHP:8.2"
```

### 4. Inicializar base de datos

1. Conectarse a la base de datos MySQL en Azure
2. Ejecutar el script `database/init.sql`

Puedes usar Azure Cloud Shell o MySQL Workbench:

```bash
mysql -h proyectocad-mysql.mysql.database.azure.com \
  -u adminuser@proyectocad-mysql \
  -p \
  proyecto_cad < database/init.sql
```

### 5. Configurar almacenamiento para uploads

Para producción en Azure, se recomienda usar Azure Blob Storage para las fotos de perfil.

## Notas importantes:

- Asegúrate de que la carpeta `uploads/` tenga permisos de escritura
- Configura SSL/TLS para conexiones seguras
- Actualiza las reglas de firewall de MySQL según sea necesario
- Considera usar Azure Key Vault para las credenciales sensibles

## Monitoreo

```bash
# Ver logs de la aplicación
az webapp log tail \
  --resource-group ProyectoCAD-RG \
  --name proyectocad-app

# Habilitar logs de aplicación
az webapp log config \
  --resource-group ProyectoCAD-RG \
  --name proyectocad-app \
  --application-logging filesystem
```
