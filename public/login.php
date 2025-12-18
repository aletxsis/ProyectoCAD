<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Auth.php';

// Si ya está logueado, redirigir
if (Auth::isLoggedIn()) {
    header('Location: /index.php');
    exit;
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = $_POST['identificador'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (Auth::login($identificador, $password)) {
        header('Location: /index.php');
        exit;
    } else {
        header('Location: /login.php?error=invalid');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SAES 2.0</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
        }
        
        /* Fondo animado con gradiente dinámico */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(138, 43, 226, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(75, 0, 130, 0.2) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        /* Partículas flotantes 3D */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite;
            backdrop-filter: blur(2px);
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0;
            }
            10% {
                opacity: 0.3;
            }
            50% {
                transform: translateY(-100vh) translateX(100px) scale(1.5);
                opacity: 0.5;
            }
            90% {
                opacity: 0.2;
            }
        }
        
        /* Cubo 3D rotando en el fondo */
        .cube-container {
            position: absolute;
            width: 300px;
            height: 300px;
            perspective: 1000px;
            top: 10%;
            right: 10%;
            opacity: 0.1;
            z-index: 1;
        }
        
        .cube {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            animation: rotateCube 20s infinite linear;
        }
        
        .cube-face {
            position: absolute;
            width: 300px;
            height: 300px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        
        .cube-face:nth-child(1) { transform: rotateY(0deg) translateZ(150px); }
        .cube-face:nth-child(2) { transform: rotateY(90deg) translateZ(150px); }
        .cube-face:nth-child(3) { transform: rotateY(180deg) translateZ(150px); }
        .cube-face:nth-child(4) { transform: rotateY(-90deg) translateZ(150px); }
        .cube-face:nth-child(5) { transform: rotateX(90deg) translateZ(150px); }
        .cube-face:nth-child(6) { transform: rotateX(-90deg) translateZ(150px); }
        
        @keyframes rotateCube {
            0% { transform: rotateX(0deg) rotateY(0deg); }
            100% { transform: rotateX(360deg) rotateY(360deg); }
        }
        
        /* Contenedor principal con efecto glassmorphism 3D */
        .login-wrapper {
            position: relative;
            z-index: 10;
            perspective: 1000px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 50px 40px;
            width: 450px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.37),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset,
                0 20px 60px rgba(0, 0, 0, 0.3);
            transform-style: preserve-3d;
            animation: floatContainer 6s ease-in-out infinite;
            position: relative;
            overflow: hidden;
        }
        
        /* Reflejo de luz superior */
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
            border-radius: 24px 24px 0 0;
        }
        
        /* Efecto de brillo animado */
        .login-container::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent 30%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 70%
            );
            animation: shine 8s infinite;
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }
        
        @keyframes floatContainer {
            0%, 100% {
                transform: translateY(0px) rotateX(0deg) rotateY(0deg);
            }
            50% {
                transform: translateY(-20px) rotateX(2deg) rotateY(-2deg);
            }
        }
        
        .login-container:hover {
            animation-play-state: paused;
            transform: translateY(-10px) rotateX(5deg);
            box-shadow: 
                0 12px 40px 0 rgba(31, 38, 135, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.2) inset,
                0 30px 80px rgba(0, 0, 0, 0.4);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
        }
        
        /* Logo animado con efecto 3D */
        .logo-3d {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            position: relative;
            transform-style: preserve-3d;
            animation: rotateLogo 10s infinite linear;
        }
        
        .logo-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: white;
            box-shadow: 
                0 10px 30px rgba(102, 126, 234, 0.4),
                0 0 0 10px rgba(255, 255, 255, 0.1),
                0 0 0 20px rgba(255, 255, 255, 0.05);
            transform: translateZ(30px);
        }
        
        @keyframes rotateLogo {
            0% { transform: rotateY(0deg); }
            100% { transform: rotateY(360deg); }
        }
        
        .login-header h1 {
            color: #ffffff;
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            font-weight: 300;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: rgba(255, 255, 255, 0.5);
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 15px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 
                0 0 0 3px rgba(102, 126, 234, 0.3),
                0 8px 25px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 
                0 10px 30px rgba(102, 126, 234, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            position: relative;
            overflow: hidden;
            z-index: 2;
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 15px 40px rgba(102, 126, 234, 0.6),
                0 0 0 1px rgba(255, 255, 255, 0.2) inset;
        }
        
        .btn:active {
            transform: translateY(-1px);
        }
        
        .error {
            background: rgba(254, 73, 73, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(254, 73, 73, 0.5);
            color: #fff;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            animation: shake 0.5s;
            position: relative;
            z-index: 2;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .info {
            background: rgba(66, 153, 225, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(66, 153, 225, 0.3);
            color: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 12px;
            margin-top: 25px;
            font-size: 12px;
            position: relative;
            z-index: 2;
        }
        
        /* Líneas decorativas 3D */
        .decorative-line {
            position: absolute;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.5), transparent);
            animation: lineMove 3s infinite;
        }
        
        .decorative-line.top {
            top: 0;
            left: 0;
            right: 0;
        }
        
        .decorative-line.bottom {
            bottom: 0;
            left: 0;
            right: 0;
            animation-delay: 1.5s;
        }
        
        @keyframes lineMove {
            0%, 100% { opacity: 0; transform: scaleX(0); }
            50% { opacity: 1; transform: scaleX(1); }
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                width: 90%;
                padding: 40px 30px;
            }
            
            .cube-container {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Partículas flotantes -->
    <div class="particles"></div>
    
    <!-- Cubo 3D decorativo -->
    <div class="cube-container">
        <div class="cube">
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
            <div class="cube-face"></div>
        </div>
    </div>
    
    <div class="login-wrapper">
        <div class="login-container">
            <div class="decorative-line top"></div>
            <div class="decorative-line bottom"></div>
            
            <div class="login-header">
                <div class="logo-3d">
                    <div class="logo-circle">S</div>
                </div>
                <h1>SAES 2.0</h1>
                <p>Sistema Avanzado de Evaluación Estudiantil</p>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error">
                    ⚠️ <?php
                    switch($_GET['error']) {
                        case 'invalid':
                            echo 'Usuario o contraseña incorrectos';
                            break;
                        case 'no_permission':
                            echo 'No tienes permisos para acceder';
                            break;
                        case 'session_expired':
                            echo 'Tu sesión ha expirado';
                            break;
                        default:
                            echo 'Error al iniciar sesión';
                    }
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout'])): ?>
                <div class="info">
                    ✓ Sesión cerrada correctamente
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="identificador">Identificador</label>
                    <div class="input-wrapper">
                        <span class="input-icon">👤</span>
                        <input type="text" id="identificador" name="identificador" 
                               placeholder="Ingresa tu identificador" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-wrapper">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" 
                               placeholder="Ingresa tu contraseña" required>
                    </div>
                </div>
                
                <button type="submit" class="btn">Iniciar Sesión</button>
            </form>
            
            <div class="info">
                <strong>💡 Credenciales de prueba:</strong><br>
                Identificador: <code>admin</code><br>
                Contraseña: <code>admin123</code>
            </div>
        </div>
    </div>
    
    <script>
        // Generar partículas flotantes
        const particlesContainer = document.querySelector('.particles');
        const particleCount = 50;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            const size = Math.random() * 60 + 10;
            const startX = Math.random() * window.innerWidth;
            const startY = Math.random() * window.innerHeight;
            const delay = Math.random() * 20;
            const duration = Math.random() * 20 + 10;
            
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = startX + 'px';
            particle.style.top = startY + 'px';
            particle.style.animationDelay = delay + 's';
            particle.style.animationDuration = duration + 's';
            
            particlesContainer.appendChild(particle);
        }
        
        // Efecto de movimiento 3D con el mouse
        const loginContainer = document.querySelector('.login-container');
        const loginWrapper = document.querySelector('.login-wrapper');
        
        document.addEventListener('mousemove', (e) => {
            const xAxis = (window.innerWidth / 2 - e.pageX) / 25;
            const yAxis = (window.innerHeight / 2 - e.pageY) / 25;
            
            loginWrapper.style.transform = `perspective(1000px) rotateY(${xAxis}deg) rotateX(${yAxis}deg)`;
        });
        
        // Resetear transformación al salir
        document.addEventListener('mouseleave', () => {
            loginWrapper.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg)';
        });
        
        // Animación de entrada
        window.addEventListener('load', () => {
            loginContainer.style.opacity = '0';
            loginContainer.style.transform = 'scale(0.9) translateY(50px)';
            
            setTimeout(() => {
                loginContainer.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
                loginContainer.style.opacity = '1';
                loginContainer.style.transform = 'scale(1) translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>
