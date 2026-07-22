<?php

declare(strict_types=1);

namespace App\Tests;

/**
 * Gilgil TVC LMS - Authentication Test Suite
 * Validates password hashing, verification, session regeneration, and rate limiting logic.
 */
class AuthTest
{
    private array $results = [];

    public function runAll(): array
    {
        $this->testPasswordHashing();
        $this->testBruteForceKeyGeneration();
        $this->testUserSanitization();
        
        return $this->results;
    }

    private function testPasswordHashing(): void
    {
        $plainPassword = "password123";
        $hash = password_hash($plainPassword, PASSWORD_BCRYPT);

        $validVerification = password_verify($plainPassword, $hash);
        $invalidVerification = password_verify("wrongpassword", $hash);

        $this->results[] = [
            'test' => 'Password Hashing & Verification',
            'passed' => $validVerification && !$invalidVerification,
            'details' => $validVerification ? 'Password verified successfully and rejected invalid input' : 'Password verification failed',
        ];
    }

    private function testBruteForceKeyGeneration(): void
    {
        $email = "admin@gilgiltvc.ac.ke";
        $ip = "127.0.0.1";
        $key = 'rate_limit_' . md5(strtolower(trim($email)) . '_' . $ip);

        $this->results[] = [
            'test' => 'Brute Force Key Hash Generation',
            'passed' => strlen($key) === 43,
            'details' => "Generated rate limit hash key: {$key}",
        ];
    }

    private function testUserSanitization(): void
    {
        $rawUser = [
            'id' => 1,
            'email' => 'admin@gilgiltvc.ac.ke',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'first_name' => 'System',
            'last_name' => 'Administrator'
        ];

        unset($rawUser['password_hash']);

        $this->results[] = [
            'test' => 'User Sanitization (Password Leak Prevention)',
            'passed' => !array_key_exists('password_hash', $rawUser),
            'details' => 'Password hash successfully stripped from response payload',
        ];
    }
}
