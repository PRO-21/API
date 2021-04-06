<?php
namespace App\Classes;

use \Firebase\JWT\JWT;

//Gestion des JWT
class JWTManager{

    /**
     * Crée un JWT
     *
     * @param  string $email
     * @param string $fullname
     * @param int $accountType
     * @return JSON $response
     */
    public static function create($id, $email, $fullname, $accountType) {
        $payload = [
            'iss' => "pro21-jwt", 
            'userId' => $id,
            'email' => strtolower($email), 
            'fullname' => $fullname,
            'accountType' => $accountType,
            'iat' => time(), //date d'émission du JWT
        ];
        
        $payload['exp'] = time() + 60*60*24*7/*1sem*/;
        return JWT::encode($payload, env('JWT_SECRET'));
    } 

    /**
     * Retourne si oui ou non un token est valide
     *
     * @return JWT $token
     */
    public static function decode($token){
        try {
            $decoded = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
        } catch (\Exception $e) {
            return ['status' => 400];
        }
        return ['status' => 200, 'data' => $decoded];
    }
}
