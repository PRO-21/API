<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\HttpStatus;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\Table;

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
        $result = DB::table('Personne')
        ->select('idPersonne', 'prenom', 'nom', 'adresse', 'npa', 'typeCompte', 'email', 'idPays', 'nomPays', 'code AS codePays')
        ->join('Pays', 'idPersonnePays', '=', 'idPays')
        ->where("idPersonne", "=", $id)->get();

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
     * Modifie les informations d'un utilisateur spécifique
     *
     * @param Request $request
     * @return JSON $response
     */
    public function editUser(Request $request){
        $parameters = $request->all();
        $id = $parameters['idPersonne'];
        unset($parameters['idPersonne']);

        // On ne peut pas modifier le type de compte
        if(isset($parameters['typeCompte'])) {
            unset($parameters['typeCompte']);
        }

        if(count($parameters) == 0) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }

        //On regarde si un utilisateur existe bien dans la bdd avec l'id du body
        $result = DB::table('Personne')->select()->where('idPersonne', '=', $id)->get();

        // Il existe un utilisateur avec cet id dans la base ?
        if(count($result) == 0) {
            $response = HttpStatus::NoDataFound404($request->getPathInfo());
            return response()->json($response, 404);
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

        try {
            DB::table('Personne')->where('idPersonne', $id)->update($parameters);
        } catch (\Exception $e){
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }

        $result = DB::table('Personne')
        ->select('idPersonne', 'prenom', 'nom', 'adresse', 'npa', 'typeCompte', 'email', 'idPays', 'nomPays', 'code AS codePays')
        ->join('Pays', 'idPersonnePays', '=', 'idPays')
        ->where("idPersonne", "=", $id)->get();

        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => $result, 
            'count' =>  1
        ];
        return response()->json($response, 200);
    }

    /**
     * Crée un utilisateur
     *
     * @param Request $request
     * @return JSON $response
     */
    public function addUser(Request $request) {
        $parameters = $request->all();

        if(!isset($parameters['motDePasse']) ||
         isset($parameters['typeCompte']) || 
         !isset($parameters['email']) ||
         !isset($parameters['prenom']) || 
         !isset($parameters['nom']) ||
         !isset($parameters['adresse']) ||
         !isset($parameters['npa']) ||
         !isset($parameters['idPersonnePays'])) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo(), " : il manque un ou des paramètre(s)");
            return response()->json($response, 400);
        }

        $parameters['typeCompte'] = 'pro';      // Type de compte par défaut
        $parameters['email'] = strtolower($parameters['email']);
        if (!filter_var($parameters['email'], FILTER_VALIDATE_EMAIL)) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo(), " : email incorrect");
            return response()->json($response, 400);
        }

        // Tester qu'il n'existe pas déjà un utilisateur avec l'email
        $result = DB::table('Personne')->select()->where("email", "=",  $parameters['email'])->get();
        if(count($result) > 0) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo(), " : cet email est déjà utilisé");
            return response()->json($response, 400);
        }

        $parameters['motDePasse'] = hash("sha512", $parameters['motDePasse']);

        try {
            $id = DB::table('Personne')->insertGetId($parameters);
        } catch (\Exception $e){
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }

        $result = DB::table('Personne')
        ->select('idPersonne', 'prenom', 'nom', 'adresse', 'npa', 'typeCompte', 'email', 'idPays', 'nomPays', 'code AS codePays')
        ->join('Pays', 'idPersonnePays', '=', 'idPays')
        ->where("idPersonne", "=", $id)->get();

        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => $result, 
            'count' =>  1
        ];
        return response()->json($response, 200);
    }
}
