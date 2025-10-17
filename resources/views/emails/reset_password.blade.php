<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer tu contraseña</title>
</head>
<body>
    <h2>Restablecer tu contraseña</h2>
    <p>Has solicitado restablecer tu contraseña.</p>
    <p>Usa el siguiente token en la aplicación para crear una nueva contraseña:</p>
    <p>
        <strong>{{ $token }}</strong>
    </p>
    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
    <hr>
    <p>Clínica Los Andes © {{ date('Y') }}</p>
</body>
</html>
