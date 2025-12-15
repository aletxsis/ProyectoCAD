<?php
// Script para generar hash de contraseña
echo "Hash para 'admin123': " . password_hash('admin123', PASSWORD_BCRYPT) . "\n";
