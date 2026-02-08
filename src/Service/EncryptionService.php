<?php

namespace App\Service;

class EncryptionService
{
    private string $encryptionKey;
    private string $cipherMethod = 'AES-256-CBC';

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * Encrypt a password or sensitive data using AES-256-CBC
     */
    public function encrypt(string $plaintext): string
    {
        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $iv = openssl_random_pseudo_bytes($ivLength);
        
        $encrypted = openssl_encrypt(
            $plaintext,
            $this->cipherMethod,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        // Combine IV and encrypted data, then base64 encode
        $result = base64_encode($iv . $encrypted);
        
        return $result;
    }

    /**
     * Decrypt an encrypted password or sensitive data
     */
    public function decrypt(string $encryptedData): string
    {
        $data = base64_decode($encryptedData);
        
        $ivLength = openssl_cipher_iv_length($this->cipherMethod);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        
        $decrypted = openssl_decrypt(
            $encrypted,
            $this->cipherMethod,
            $this->encryptionKey,
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return $decrypted !== false ? $decrypted : '';
    }

    /**
     * Generate a secure random password
     */
    public function generateSecurePassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}|;:,.<>?';
        
        $allChars = $uppercase . $lowercase . $numbers . $special;
        
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];
        
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }

    /**
     * Calculate password strength (0-100)
     */
    public function calculatePasswordStrength(string $password): int
    {
        $strength = 0;
        $length = strlen($password);
        
        // Length scoring
        if ($length >= 8) $strength += 20;
        if ($length >= 12) $strength += 10;
        if ($length >= 16) $strength += 10;
        
        // Character variety
        if (preg_match('/[a-z]/', $password)) $strength += 15;
        if (preg_match('/[A-Z]/', $password)) $strength += 15;
        if (preg_match('/[0-9]/', $password)) $strength += 15;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength += 15;
        
        return min(100, $strength);
    }
}
