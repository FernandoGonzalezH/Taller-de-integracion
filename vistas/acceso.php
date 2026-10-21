<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar | FG Motors</title>
    <link rel="stylesheet" href="public/css/estilo.css?v=20261008-6">
    <style>
        .acceso-pantalla { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .acceso-tarjeta { width: min(420px, 100%); padding: 28px; border-radius: 14px; background: rgba(0,0,0,.78); box-shadow: 0 8px 28px rgba(0,0,0,.55); }
        .acceso-logo { display: block; width: 320px; max-width: 95%; height: auto; margin: 0 auto 18px; }
        .acceso-tarjeta label { display: block; margin-bottom: 8px; font-weight: bold; }
        .acceso-tarjeta input { width: 100%; padding: 11px; border-radius: 8px; border: 1px solid #999; font-size: 16px; }
        .acceso-tarjeta .btn-gold { display: block; margin: 18px auto 0; }
        .acceso-error { margin-top: 14px; color: #ffb7b7; text-align: center; }
    </style>
</head>
<body>
    <main class="acceso-pantalla">
        <form class="acceso-tarjeta" method="post" action="index.php?accion=ingresar" autocomplete="on">
            <img class="acceso-logo" src="public/imagenes/logo.png" alt="Logo de FG Motors">
            <label for="claveAcceso">Ingresá la clave</label>
            <input id="claveAcceso" name="clave" type="password" autocomplete="current-password" required autofocus>
            <input type="hidden" name="token" value="<?= htmlspecialchars($_SESSION['token_acceso'], ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn-gold" type="submit">Ingresar</button>
            <?php if (!empty($errorAcceso)): ?>
                <p class="acceso-error" role="alert"><?= htmlspecialchars($errorAcceso, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </form>
    </main>
</body>
</html>
