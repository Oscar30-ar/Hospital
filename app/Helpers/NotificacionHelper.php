<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificacionHelper
{
    public static function enviarNotificacion($expoToken, $titulo, $mensaje)
    {
        try {
            $response = Http::post('https://exp.host/--/api/v2/push/send', [
                'to' => $expoToken,
                'sound' => 'default',
                'title' => $titulo,
                'body' => $mensaje,
            ]);

            Log::info("📨 Notificación enviada a {$expoToken}: " . $response->body());
        } catch (\Exception $e) {
            Log::error("❌ Error enviando notificación: " . $e->getMessage());
        }
    }
}
