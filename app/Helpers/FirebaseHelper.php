<?php

namespace App\Helpers;

use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

class FirebaseHelper
{
    public static function enviarNotificacion($token, $titulo, $mensaje)
    {
        try {
            // Inicializa Firebase con las credenciales
            $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
            $messaging = $factory->createMessaging();

            // Define el mensaje
            $message = [
                'token' => $token,
                'notification' => [
                    'title' => $titulo,
                    'body' => $mensaje,
                ],
            ];

            // Envía la notificación
            $messaging->send($message);

            Log::info("✅ Notificación enviada correctamente a: {$token}");
            return true;
        } catch (\Throwable $e) {
            Log::error("❌ Error al enviar notificación FCM: " . $e->getMessage());
            return false;
        }
    }
}
