<?php

namespace App\Services\Bwa;

class BwaSignature
{
    public function sign(
        string $method,
        string $path,
        string $timestamp,
        string $requestId,
        string $rawBody,
        string $secret,
    ): string {
        return 'sha256='.hash_hmac(
            'sha256',
            $this->canonicalPayload($method, $path, $timestamp, $requestId, $rawBody),
            $secret,
        );
    }

    public function verify(
        string $signature,
        string $method,
        string $path,
        string $timestamp,
        string $requestId,
        string $rawBody,
        string $secret,
    ): bool {
        return $signature !== ''
            && $secret !== ''
            && hash_equals(
                $this->sign($method, $path, $timestamp, $requestId, $rawBody, $secret),
                $signature,
            );
    }

    private function canonicalPayload(
        string $method,
        string $path,
        string $timestamp,
        string $requestId,
        string $rawBody,
    ): string {
        return implode("\n", [
            strtoupper($method),
            $path,
            $timestamp,
            $requestId,
            hash('sha256', $rawBody),
        ]);
    }
}
