<?php
namespace App\Classes;
/**
* Retourne les différentes erreurs possibles 
*/
class HttpStatus{
    public static function NoError200($path){
        return [
            "code" => 200,
            "message" => "OK",
            "request" => $path,
            "api-version" => env('APP_VERSION')
         ]; 
     }
    public static function InvalidRequest400($path){
        return [
             "status" => [
                 "code" => 400,
                 "message" => "Requête invalide",
                 "request" => $path,
                 "api-version" => env('APP_VERSION')
             ],
             "data" => null
         ]; 
     }
     public static function AuthenticationError401($path){
        return [
             "status" => [
                 "code" => 401,
                 "message" => "Erreur d'authentification",
                 "request" => $path,
                 "api-version" => env('APP_VERSION')
             ],
             "data" => null
         ]; 
     }
     public static function ForbiddenAccess403($path){
        return [
             "status" => [
                 "code" => 403,
                 "message" => "Accès interdit",
                 "request" => $path,
                 "api-version" => env('APP_VERSION')
             ],
             "data" => null
         ]; 
     }
    public static function NoDataFound404($path){
        return [
             "status" => [
                 "code" => 404,
                 "message" => "Aucune information trouvée",
                 "request" => $path,
                 "api-version" => env('APP_VERSION')
             ],
             "data" => null
         ]; 
     }
}
