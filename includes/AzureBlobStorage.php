<?php
/**
 * Clase para gestión de archivos en Azure Blob Storage
 * 
 * Requiere: composer require microsoft/azure-storage-blob
 * 
 * Variables de entorno necesarias:
 * - AZURE_STORAGE_ACCOUNT
 * - AZURE_STORAGE_KEY
 * - AZURE_STORAGE_CONTAINER
 */

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

class AzureBlobStorage {
    private $blobClient;
    private $containerName;
    private $useAzure;
    
    public function __construct() {
        // Detectar si Azure está configurado
        $this->useAzure = !empty(getenv('AZURE_STORAGE_ACCOUNT')) && 
                         !empty(getenv('AZURE_STORAGE_KEY'));
        
        if ($this->useAzure) {
            $accountName = getenv('AZURE_STORAGE_ACCOUNT');
            $accountKey = getenv('AZURE_STORAGE_KEY');
            $this->containerName = getenv('AZURE_STORAGE_CONTAINER') ?: 'saes-photos';
            
            $connectionString = "DefaultEndpointsProtocol=https;AccountName={$accountName};AccountKey={$accountKey};EndpointSuffix=core.windows.net";
            
            try {
                $this->blobClient = BlobRestProxy::createBlobService($connectionString);
                $this->asegurarContainer();
            } catch (Exception $e) {
                error_log("Error al conectar con Azure Blob Storage: " . $e->getMessage());
                $this->useAzure = false;
            }
        }
    }
    
    /**
     * Subir archivo a Azure Blob Storage o almacenamiento local
     */
    public function subirArchivo($file, $prefijo = 'foto') {
        // Validar archivo
        $this->validarArchivo($file);
        
        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = $prefijo . '_' . uniqid() . '_' . time() . '.' . $extension;
        
        if ($this->useAzure) {
            return $this->subirAzure($file['tmp_name'], $nombreArchivo, $file['type']);
        } else {
            return $this->subirLocal($file, $nombreArchivo);
        }
    }
    
    /**
     * Subir archivo a Azure Blob Storage
     */
    private function subirAzure($rutaTemporal, $nombreArchivo, $tipoMime) {
        try {
            $contenido = file_get_contents($rutaTemporal);
            
            $this->blobClient->createBlockBlob(
                $this->containerName,
                $nombreArchivo,
                $contenido
            );
            
            // Retornar URL pública del blob
            $accountName = getenv('AZURE_STORAGE_ACCOUNT');
            return "https://{$accountName}.blob.core.windows.net/{$this->containerName}/{$nombreArchivo}";
            
        } catch (ServiceException $e) {
            error_log("Error al subir a Azure: " . $e->getMessage());
            throw new Exception("Error al subir el archivo a Azure Blob Storage");
        }
    }
    
    /**
     * Subir archivo al almacenamiento local (fallback)
     */
    private function subirLocal($file, $nombreArchivo) {
        $uploadPath = __DIR__ . '/../uploads/';
        
        // Crear directorio si no existe
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }
        
        $rutaDestino = $uploadPath . $nombreArchivo;
        
        if (!move_uploaded_file($file['tmp_name'], $rutaDestino)) {
            throw new Exception("Error al subir el archivo al almacenamiento local");
        }
        
        // Retornar ruta relativa
        return $nombreArchivo;
    }
    
    /**
     * Eliminar archivo de Azure Blob Storage o almacenamiento local
     */
    public function eliminarArchivo($nombreArchivo) {
        if (empty($nombreArchivo)) {
            return true;
        }
        
        if ($this->useAzure && strpos($nombreArchivo, 'blob.core.windows.net') !== false) {
            return $this->eliminarAzure($nombreArchivo);
        } else {
            return $this->eliminarLocal($nombreArchivo);
        }
    }
    
    /**
     * Eliminar archivo de Azure Blob Storage
     */
    private function eliminarAzure($url) {
        try {
            // Extraer nombre del blob de la URL
            $partes = parse_url($url);
            $nombreBlob = basename($partes['path']);
            
            $this->blobClient->deleteBlob($this->containerName, $nombreBlob);
            return true;
            
        } catch (ServiceException $e) {
            error_log("Error al eliminar de Azure: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar archivo del almacenamiento local
     */
    private function eliminarLocal($nombreArchivo) {
        $rutaArchivo = __DIR__ . '/../uploads/' . $nombreArchivo;
        
        if (file_exists($rutaArchivo)) {
            return unlink($rutaArchivo);
        }
        
        return true;
    }
    
    /**
     * Asegurar que el container existe
     */
    private function asegurarContainer() {
        try {
            $this->blobClient->createContainer($this->containerName);
        } catch (ServiceException $e) {
            // Container ya existe, ignorar error
            if ($e->getCode() != 409) {
                throw $e;
            }
        }
    }
    
    /**
     * Validar archivo subido
     */
    private function validarArchivo($file) {
        $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
        
        if (!isset($file['type']) || !in_array($file['type'], $permitidos)) {
            throw new Exception("Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG y GIF.");
        }
        
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new Exception("El archivo es demasiado grande. Tamaño máximo: 5MB");
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error al subir el archivo");
        }
    }
    
    /**
     * Obtener URL de un archivo
     */
    public function getUrl($nombreArchivo) {
        if (empty($nombreArchivo)) {
            return null;
        }
        
        // Si ya es una URL de Azure, retornarla
        if (strpos($nombreArchivo, 'blob.core.windows.net') !== false) {
            return $nombreArchivo;
        }
        
        // Si es almacenamiento local
        return '/uploads/' . $nombreArchivo;
    }
    
    /**
     * Verificar si está usando Azure
     */
    public function isUsingAzure() {
        return $this->useAzure;
    }
}
