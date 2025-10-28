<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private $messaging;

    public function __construct()
    {
        try {
            $credentialsPath = storage_path('app/firebase-credentials.json');
            
            if (!file_exists($credentialsPath)) {
                throw new \Exception('Firebase credentials file not found at: ' . $credentialsPath);
            }

            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        } catch (\Exception $e) {
            Log::error('Firebase initialization error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enviar notificación a un dispositivo específico
     * 
     * @param string $deviceToken Token del dispositivo
     * @param string $titulo Título de la notificación
     * @param string $mensaje Cuerpo del mensaje
     * @param array $datos Datos adicionales (opcional)
     * @return bool
     */
    public function enviarNotificacionDispositivo($deviceToken, $titulo, $mensaje, $datos = [])
    {
        try {
            $notification = Notification::create($titulo, $mensaje);
            
            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification);

            if (!empty($datos)) {
                $message = $message->withData($datos);
            }

            $this->messaging->send($message);
            
            Log::info("✅ Notificación enviada a dispositivo", [
                'token' => substr($deviceToken, 0, 20) . '...',
                'titulo' => $titulo,
                'mensaje' => $mensaje
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("❌ Error enviando notificación a dispositivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación a múltiples dispositivos
     * 
     * @param array $deviceTokens Array de tokens
     * @param string $titulo Título de la notificación
     * @param string $mensaje Cuerpo del mensaje
     * @param array $datos Datos adicionales (opcional)
     * @return array Resultados del envío
     */
    public function enviarNotificacionMultiple($deviceTokens, $titulo, $mensaje, $datos = [])
    {
        $resultados = [
            'exitosos' => 0,
            'fallidos' => 0,
            'errores' => []
        ];

        foreach ($deviceTokens as $token) {
            if ($this->enviarNotificacionDispositivo($token, $titulo, $mensaje, $datos)) {
                $resultados['exitosos']++;
            } else {
                $resultados['fallidos']++;
                $resultados['errores'][] = $token;
            }
        }

        Log::info("📊 Resumen de notificaciones", $resultados);
        return $resultados;
    }

    /**
     * Enviar notificación a un tema (topic)
     * 
     * @param string $topic Nombre del tema
     * @param string $titulo Título de la notificación
     * @param string $mensaje Cuerpo del mensaje
     * @param array $datos Datos adicionales (opcional)
     * @return bool
     */
    public function enviarNotificacionTema($topic, $titulo, $mensaje, $datos = [])
    {
        try {
            $notification = Notification::create($titulo, $mensaje);
            
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification);

            if (!empty($datos)) {
                $message = $message->withData($datos);
            }

            $this->messaging->send($message);
            
            Log::info("✅ Notificación enviada al tema: " . $topic, [
                'titulo' => $titulo,
                'mensaje' => $mensaje
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("❌ Error enviando notificación al tema: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Suscribir dispositivo a un tema
     * 
     * @param string $topic Nombre del tema
     * @param array $deviceTokens Array de tokens
     * @return bool
     */
    public function suscribirAlTema($topic, $deviceTokens)
    {
        try {
            $this->messaging->subscribeToTopic($topic, $deviceTokens);
            
            Log::info("✅ Dispositivos suscritos al tema: " . $topic, [
                'cantidad' => count($deviceTokens)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("❌ Error suscribiendo al tema: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desuscribir dispositivo de un tema
     * 
     * @param string $topic Nombre del tema
     * @param array $deviceTokens Array de tokens
     * @return bool
     */
    public function desuscribirDelTema($topic, $deviceTokens)
    {
        try {
            $this->messaging->unsubscribeFromTopic($topic, $deviceTokens);
            
            Log::info("✅ Dispositivos desuscritos del tema: " . $topic, [
                'cantidad' => count($deviceTokens)
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("❌ Error desuscribiendo del tema: " . $e->getMessage());
            return false;
        }
    }
}
