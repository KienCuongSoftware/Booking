<?php

namespace App\Support;

final class PublicDisk
{
    /**
     * URL tới file trên disk "public" — dùng đường dẫn gốc /storage/... để luôn khớp host/port hiện tại (vd. 127.0.0.1:8000).
     */
    public static function url(?string $path): string
    {
        if ($path === null || $path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = str_replace('\\', '/', $path);

        return '/storage/'.ltrim($path, '/');
    }
}
