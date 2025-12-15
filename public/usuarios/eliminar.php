<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/Usuario.php';

Auth::requireDirectivo();

$id = $_GET['id'] ?? 0;

if ($id > 0) {
    try {
        $usuarioObj = new Usuario();
        $usuarioObj->eliminar($id);
        header('Location: listar.php?success=deleted');
    } catch (Exception $e) {
        header('Location: listar.php?error=' . urlencode($e->getMessage()));
    }
} else {
    header('Location: listar.php?error=invalid_id');
}
exit;
