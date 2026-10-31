<?php

namespace App\Services;

use App\Models\Application;

class ReferenceGenerator
{
    /**
     * Unique public reference, e.g. APP-2026-8F42K.
     * Retries on the (astronomically unlikely) collision.
     */
    public static function generate(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no confusing 0/O/1/I

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $suffix = '';
            for ($i = 0; $i < 5; $i++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $reference = sprintf('APP-%d-%s', now()->year, $suffix);

            if (! Application::where('reference', $reference)->exists()) {
                return $reference;
            }
        }

        throw new \RuntimeException('Unable to generate a unique application reference.');
    }
}
