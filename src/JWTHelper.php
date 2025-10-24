<?php

namespace WastelandDominion;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    private $secret;
    private $algorithm = 'HS256';
    private $expirationTime;
    
    public function __construct(string $secret, int $expirationTime = 3600)
    {
        $this->secret = $secret;
        $this->expirationTime = $expirationTime;
    }
    
    public function generateToken(array $payload): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $this->expirationTime;
        
        $token = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'data' => $payload
        ];
        
        return JWT::encode($token, $this->secret, $this->algorithm);
    }
    
    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public function isTokenExpired(string $token): bool
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return $decoded->exp < time();
        } catch (\Exception $e) {
            return true;
        }
    }
    
    public function refreshToken(string $token): ?string
    {
        $payload = $this->validateToken($token);
        
        if ($payload) {
            return $this->generateToken($payload);
        }
        
        return null;
    }
    
    public function extractUserIdFromToken(string $token): ?int
    {
        $payload = $this->validateToken($token);
        return $payload['user_id'] ?? null;
    }
}