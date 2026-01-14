<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class StorageHelper
{
    /**
     * Serve a file from storage without using symlink
     *
     * @param string $path Path relative to storage/app/public/
     * @return \Illuminate\Http\Response
     */
    public static function serveFile($path)
    {
        // Check if file exists in storage/app/public
        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        // Get file path and MIME type
        $filePath = Storage::disk('public')->path($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        // Return file response
        return Response::file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Generate URL for storage file
     *
     * @param string $path Path relative to storage/app/public/
     * @return string
     */
    public static function url($path)
    {
        if (empty($path)) {
            return '';
        }

        return url('/storage/' . ltrim($path, '/'));
    }

    /**
     * Check if file exists in storage
     *
     * @param string $path Path relative to storage/app/public/
     * @return bool
     */
    public static function exists($path)
    {
        return Storage::disk('public')->exists($path);
    }

    /**
     * Delete file from storage
     *
     * @param string $path Path relative to storage/app/public/
     * @return bool
     */
    public static function delete($path)
    {
        if (self::exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
}
