<?php

namespace App\Helpers;

use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

class FirebaseHelper
{
    public static function enviarNotificacion($token, $titulo, $mensaje)
    {
        try {
            $factory = (new Factory)->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
            $messaging = $factory->createMessaging();

            $message = [
                'token' => $token,
                'notification' => [
                    'title' => $titulo,
                    'body' => $mensaje,
                ],
                'data' => [
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'title' => $titulo,
                    'body' => $mensaje,
                ],
                'android' => [
                    'priority' => 'high',
                    'notification' => [
                        'sound' => 'default',
                        'channel_id' => 'default', // 👈 Debe coincidir con el del front
                    ],
                ],
            ];

            $messaging->send($message);
            Log::info("✅ Notificación enviada correctamente a: {$token}");
            return true;
        } catch (\Throwable $e) {
            Log::error("❌ Error al enviar notificación FCM: " . $e->getMessage());
            return false;
        }
    }
}
