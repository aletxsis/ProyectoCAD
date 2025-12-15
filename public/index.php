<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';

Auth::requireLogin();
$currentUser = Auth::getCurrentUser();

// Redirigir según el tipo de usuario
if (Auth::isAlumno()) {
    header('Location: /alumno/dashboard.php');
    exit;
} elseif (Auth::isGestion()) {
    header('Location: /gestion/dashboard.php');
    exit;
} elseif (Auth::isDirectivo()) {
    header('Location: /directivo/dashboard.php');
    exit;
}

// Si no tiene rol definido, cerrar sesión
Auth::logout();
header('Location: /login.php?error=invalid_role');
exit;
