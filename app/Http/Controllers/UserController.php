<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\HttpStatus;
use Illuminate\Support\Facades\DB;


class UserController extends Controller {
    /**
     * Récupère un utilisateur spécifique
     *
     * @param Request $request
     * @param int $id
     * @return JSON $response
     */
    public function getUser(Request $request, $id){
        // Récupérer les infos de l'utilsateur
        $result = DB::select('SELECT idPersonne, prenom, nom, adresse, npa, typeCompte, email FROM personne WHERE idPersonne = ?', [$id]);
        // Il existe un utilisateur avec cet email dans la base ?
        if(count($result) == 0) {
            $response = HttpStatus::NoDataFound404($request->getPathInfo());
            return response()->json($response, 404);
        }

        // Est-ce que l'utilisateur authentifié a le droit de récupérer les informations de cet utilisateur ?
        if($result[0]->idPersonne !== $request->user->userId) {
            $response = HttpStatus::ForbiddenAccess403($request->getPathInfo());
            return response()->json($response, 403);
        }

        //Retourne les informations de l'utilisateur
        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => $result[0], 
            'count' =>  1
        ];
        return response()->json($response, 200);
    }

    /**
     * Récupère un utilisateur spécifique
     *
     * @param Request $request
     * @return JSON $response
     */
    public function editUser(Request $request){
        $parameters = $request->all();
        $id = $parameters['idPersonne'];
        unset($parameters['idPersonne']);

        if(count($parameters) == 0) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }

        //On regarde si un utilisateur existe bien dans la bdd avec l'id du body
        $result = DB::select('SELECT * FROM personne WHERE idPersonne = ?', [$id]);

        // Il existe un utilisateur avec cet id dans la base ?
        if(count($result) == 0) {
            $response = HttpStatus::AuthenticationError401($request->getPathInfo());
            return response()->json($response, 401);
        }

        // Est-ce que l'utilisateur authentifié a le droit de modifier les informations de cet utilisateur ?
        if($result[0]->idPersonne !== $request->user->userId) {
            $response = HttpStatus::ForbiddenAccess403($request->getPathInfo());
            return response()->json($response, 403);
        }

        if(isset($parameters['motDePasse'])) {
            // Hasher le mot de passe
            $parameters['motDePasse'] = hash("sha512", $parameters['motDePasse']);
        }

        //try {
            DB::table('personne')->where('idPersonne', $id)->update($parameters);
        /*} catch (\Exception $e){
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }*/

        $result = DB::select('SELECT * FROM personne WHERE idPersonne = ?', [$id]);
        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => $result, 
            'count' =>  1
        ];
        return response()->json($response, 200);
    }
}