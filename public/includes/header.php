<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <h1>Proyecto CAD</h1>
            </div>
            <nav class="main-nav">
                <a href="/index.php">Inicio</a>
                <?php if (Auth::isDirectivo()): ?>
                    <a href="/usuarios/listar.php">Usuarios</a>
                <?php endif; ?>
                <a href="/auth/logout.php" class="btn-logout">Cerrar Sesión</a>
            </nav>
        </div>
    </div>
</header>
