<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer tu contraseña | Clínica Los Andes</title>
    <style>
        body {
            background-color: #f4f7fb;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border-top: 5px solid #007BFF;
        }

        .header {
            background: linear-gradient(135deg, #007BFF, #00AEEF);
            color: white;
            text-align: center;
            padding: 25px 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .content {
            padding: 30px 40px;
            line-height: 1.6;
        }

        .content h2 {
            color: #007BFF;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .token-box {
            background-color: #f0f6ff;
            border: 1px solid #cde4ff;
            color: #0056b3;
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            letter-spacing: 1px;
        }

        .footer {
            background-color: #f9fafc;
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #e0e0e0;
        }

        .footer a {
            color: #007BFF;
            text-decoration: none;
        }

        @media only screen and (max-width: 600px) {
            .container {
                margin: 20px;
            }
            .content {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- HEADER -->
        <div class="header">
            <h1>Clínica Los Andes</h1>
        </div>

        <!-- CONTENIDO -->
        <div class="content">
            <h2>Restablecer tu contraseña</h2>
            <p>Hola,</p>
            <p>Has solicitado restablecer tu contraseña en la aplicación de la <strong>Clínica Los Andes</strong>.</p>
            <p>Utiliza la siguiente llave de acceso en la app para crear una nueva contraseña:</p>

            <div class="token-box">
                {{ $token }}
            </div>

            <p>⚠️ Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
            <p>Gracias por confiar en nosotros ❤️</p>
        </div>

        <!-- FOOTER -->
        <div class="footer">
            <p>© {{ date('Y') }} Clínica Los Andes. Todos los derechos reservados.</p>
            <p>
                <a href="https://clinicadelosandesips.com/">www.clinicalosandes.com</a>
            </p>
        </div>
    </div>
</body>
</html>
