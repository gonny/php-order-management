<?php

namespace App\Services;

class HmacSignatureService
{
    /**
     * Generate HMAC signature for API request
     */
    public static function generateSignature(
        string $method,
        string $path,
        string $body,
        string $secret,
        int $timestamp = null
    ): array {
        $timestamp = $timestamp ?: time();
        
        // Generate body digest
        $digest = 'SHA-256=' . base64_encode(hash('sha256', $body, true));
        
        // Create string to sign
        $stringToSign = $method . $path . $timestamp . $digest;
        
        // The secret should be hashed for storage but raw for signing
        $secretHash = hash('sha256', $secret);
        
        // Generate HMAC signature
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $secretHash, true));
        
        return [
            'signature' => $signature,
            'timestamp' => $timestamp,
            'digest' => $digest,
            'secret_hash' => $secretHash, // For storing in database
        ];
    }

    /**
     * Generate headers for API request
     */
    public static function generateHeaders(
        string $keyId,
        string $method,
        string $path,
        string $body,
        string $secret,
        int $timestamp = null
    ): array {
        $signatureData = self::generateSignature($method, $path, $body, $secret, $timestamp);
        
        return [
            'X-Key-Id' => $keyId,
            'X-Signature' => $signatureData['signature'],
            'X-Timestamp' => (string) $signatureData['timestamp'],
            'Digest' => $signatureData['digest'],
            'Content-Type' => 'application/json',
        ];
    }
}