<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\HttpStatus;
use Illuminate\Support\Facades\DB;


class CertController extends Controller {
    /**
     * Crée un certificat signé
     *
     * @param Request $request
     * @return JSON $response
     */
    public function addCert(Request $request) {
        $parameters = $request->all();

        if(!isset($parameters['dateSignature'])) {
            $response = HttpStatus::InvalidRequest400($request->getPathInfo(), " : il manque un ou des paramètre(s)");
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
            DB::table('certificat')->insert($certifParam);
            $idCertificate = DB::select('SELECT idCertificat FROM certificat where id = LAST_INSERT_ID()')[0]->idCertificat;

            unset($parameters['dateSignature']);

            // Ajouter les champs au certificat
            foreach ($parameters as $key => $value) {
                DB::table('champ')->insert(['idCertificatCertificat' => $idCertificate, 'nom' => $key, 'valeur' => $value]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $response = HttpStatus::InvalidRequest400($request->getPathInfo());
            return response()->json($response, 400);
        }

        $result = DB::select('SELECT idCertificat, dateSignature, nom AS champ, valeur FROM certificat
        INNER JOIN champ ON idCertificat = idCertificatCertificat
        WHERE idCertificat = ?', [$idCertificate]);

        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => $result, 
            'count' =>  count($result)
        ];
        return response()->json($response, 200);
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
        $result = DB::select('SELECT * FROM Certificat WHERE idCertificat = ?', [$id]);
        // Il existe un certificat ?
        if(count($result) == 0) {
            $response = HttpStatus::NoDataFound404($request->getPathInfo());
            return response()->json($response, 404);
        }

        // Récupérer l'utilisateur ayant créé ce certificat
        $issuer = DB::select('SELECT prenom, nom FROM personne WHERE idPersonne = ?', [$result[0]->idPersonnePersonne]);
        // Récupérer les champs du certificat
        $fields = DB::select('SELECT nom, valeur FROM champ WHERE idCertificatCertificat = ?', [$id]);

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