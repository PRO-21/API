<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\HttpStatus;
use App\Classes\JWTManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class AuthController extends Controller {
    /**
     * Authentification d'un utilisateur
     *
     * @param Request $request
     * @return JSON $response
     */
    public function authenticate(Request $request) {

        //Authentification via login/password
        if($request->input('auth_type') == 'credentials'){
            //L'utilisateur a remplit tous les champs ?
            if(null == $request->input('email') || null == $request->input('password')){
                $response = HttpStatus::InvalidRequest400($request->getPathInfo());
                return response()->json($response, 400);
            }

            $email = $request->input('email');
            $password = $request->input('password');

            // Récupérer les infos de l'utilsateur
            $result = DB::select('SELECT * FROM Personne WHERE email = ?', [$email]);
            // Il existe un utilisateur avec cet email dans la base ? Le mot de passe correspond ?
            if(count($result) == 0 || hash("sha512", $password) !== $result[0]->motDePasse) {
                $response = HttpStatus::AuthenticationError401($request->getPathInfo(), " : email ou mot de passe incorrect");
                return response()->json($response, 401);
            }

            //Préparation des paramètres
            $params = [
                $result[0]->idPersonne,
                $result[0]->prenom.' '.$result[0]->nom,
            ];
        } else 
        // Authentification via token
        if($request->input('auth_type') == 'token'){    
            //L'utiisateur a renseigné son token ?
            if(null == $request->bearerToken()){
                $response = HttpStatus::AuthenticationError401($request->getPathInfo());
                return response()->json($response, 401);
            }
            //Le refresh_token fourni est valide ?
            $decodedToken = JWTManager::decode($request->bearerToken());
            //Le décode a levé une exception ?
            if($decodedToken['status'] == 400){
                $response = HttpStatus::AuthenticationError401($request->getPathInfo());
                return response()->json($response, 401);
            }

            //Préparation des paramètres
            $params = [
                $decodedToken['data']->userId,
                $decodedToken['data']->fullname,
            ];
        } else {
            //La valeur du paramètre auth_type est fausse
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }

        //Création des JWT
        $token = JWTManager::create(...$params);

        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => [
                'token' => $token
            ] 
        ];
        return response()->json($response, 200);
    }
}
