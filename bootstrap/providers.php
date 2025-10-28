<?php

return [
    Illuminate\Foundation\Providers\FoundationServiceProvider::class,

    // Core framework providers commonly required
    Illuminate\Auth\AuthServiceProvider::class,
    Illuminate\Broadcasting\BroadcastServiceProvider::class,
    Illuminate\Bus\BusServiceProvider::class,
    Illuminate\Cache\CacheServiceProvider::class,
    Illuminate\Database\DatabaseServiceProvider::class,
    Illuminate\Encryption\EncryptionServiceProvider::class,
    Illuminate\Filesystem\FilesystemServiceProvider::class,
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
    Illuminate\Hashing\HashServiceProvider::class,
    Illuminate\Mail\MailServiceProvider::class,
    Illuminate\Notifications\NotificationServiceProvider::class,
    Illuminate\Pagination\PaginationServiceProvider::class,
    Illuminate\Session\SessionServiceProvider::class,
    Illuminate\View\ViewServiceProvider::class,
    Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,

    // Fix for "Class 'cookie' does not exist"
    Illuminate\Cookie\CookieServiceProvider::class,

    // JWT Auth (PHPOpenSourceSaver)
    PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider::class,

    // Application
    App\Providers\AppServiceProvider::class,
];
