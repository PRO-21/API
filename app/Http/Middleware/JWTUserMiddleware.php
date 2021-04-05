<?php
namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Classes\JWTManager;
use App\Classes\HttpStatus;
use Illuminate\Support\Facades\DB;

class JWTUserMiddleware
{
    /**
     * S'occupe de décoder le token et de vérifier sa validité
     *
     * @param Request $request
     * @param Closure $next
     * @param string $guard
     * @return Closure $next
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->bearerToken();
        //Le token n'a pas été renseigné
        if(!$token) {
            $response = HttpStatus::AuthenticationError401($request->getPathInfo());
            return response()->json($response, 401);
        }

        $decoded_token = JWTManager::Decode($token);
        //Le décode a levé une exception ?
        if($decoded_token['status'] == 400){
            $response = HttpStatus::AuthenticationError401($request->getPathInfo());
            return response()->json($response, 401);
        }
        //On regarde si un utilisateur existe bien dans la bdd avec l'id du jwt et si le type de compte est le bon
        $result = DB::select('SELECT * FROM personne WHERE idPersonne = ?', [$decoded_token['data']->userId]);

        // Il existe un utilisateur avec cet id dans la base ?
        if(count($result) == 0) {
            $response = HttpStatus::AuthenticationError401($request->getPathInfo());
            return response()->json($response, 401);
        }

        // On vérifie que le informations correspondent
        if($decoded_token['data']->userId !== $result[0]->idPersonne || 
            $decoded_token['data']->email !== $result[0]->email ||
            $decoded_token['data']->accountType !== $result[0]->typeCompte) {

                $response = HttpStatus::AuthenticationError401($request->getPathInfo());
                return response()->json($response, 401);
        }
        
        //On passe les informations de l'utilisateur dans le controller pour pouvoir les réutiliser plus tard
        $request->user = $decoded_token['data'];
        return $next($request);
    }
}
