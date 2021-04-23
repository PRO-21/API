<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\HttpStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class CertController extends Controller {
    /**
     * Crée un certificat signé
     *
     * @param Request $request
     * @return JSON $response
     */
    public function addCert(Request $request) {
        $parameters = $request->all();

        // Récupérer les infos de l'utilisateur
        $response = DB::table('Personne')->select('typeCompte')->where('idPersonne', '=', $request->user->userId)->get();
        
        // Que les utilisateurs pro peuvent créer des certificats
        if($response[0]->typeCompte !== 'pro') {
            $response = HttpStatus::ForbiddenAccess403($request->getPathInfo());
            return response()->json($response, 403);
        }

        if(!isset($parameters['dateSignature'])) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo(), " : il manque un ou des paramètre(s)");
            return response()->json($response, 400);
        }

        // Vérification du format de la date
        if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $parameters['dateSignature'])) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo(), " : la date doit être au format yyyy-mm-dd");
            return response()->json($response, 400);
        }

        $certifParam = ['idPersonnePersonne' => $request->user->userId,
                        'dateSignature' => $parameters['dateSignature']];

        unset($parameters['dateSignature']);
        // Un champ a été renseigné ?
        if(count($parameters) == 0) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo(), " : vous n'avez pas renseigné de champs");
            return response()->json($response, 400);
        }

        DB::beginTransaction();
        try {
            // Création du certificat
            DB::table('Certificat')->insert($certifParam);
            $idCertificate = DB::table('Certificat')->select('idCertificat')->where('id', '=', DB::getPdo()->lastInsertId())->get()->first()->idCertificat;
            unset($parameters['dateSignature']);

            // Ajouter les champs au certificat
            foreach ($parameters as $key => $value) {
                DB::table('Champ')->insert(['idCertificatCertificat' => $idCertificate, 'nom' => $key, 'valeur' => Crypt::encrypt($value)]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }

        return $this->getCert($request, $idCertificate);
    }

    /**
     * Récupère un certificat signé
     *
     * @param Request $request
     * @param String $id
     * @return JSON $response
     */
    public function getCert(Request $request, $id) {
        // Récupérer le certificat
        $result = DB::table('Certificat')->select()->where('idCertificat', '=', $id)->get();
        // Il existe un certificat ?
        if(count($result) == 0) {
            $response = HttpStatus::NoDataFound404($request->getPathInfo());
            return response()->json($response, 404);
        }

        // Récupérer l'utilisateur ayant créé ce certificat
        $issuer = DB::table('Personne')->select('prenom', 'nom')->where('idPersonne', '=', $result[0]->idPersonnePersonne)->get()->first();
        // Récupérer les champs du certificat
        $fields = DB::table('Champ')->select('nom', 'valeur')->where('idCertificatCertificat', '=', $id)->get();

        // Pour chaque champs récupéré, déchiffrer sa valeur
        foreach($fields as $field) {
            try {
                $field->valeur = Crypt::decrypt($field->valeur);
            } catch (DecryptException $e) {
                $response = HttpStatus::InvalidRequest400($request->getPathInfo());
                return response()->json($response, 400);
            }
        }

        $data = [ 'idCertificat' => $result[0]->idCertificat,
                  'dateSignature' => $result[0]->dateSignature,
                  'signataire' =>  $issuer,
                   'champs' => $fields ];

        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => $data, 
            'count' =>  1
        ];
        return response()->json($response, 200);
    }
}