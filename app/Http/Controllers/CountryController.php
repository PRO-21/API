<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Classes\HttpStatus;
use App\Classes\JWTManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class CountryController extends Controller {
    /**
     * Récupération des pays
     *
     * @param Request $request
     * @return JSON $response
     */
    public function getCountries(Request $request) {
        // Récupérer les pays
        $result = DB::table('Pays')->select()->orderBy('nomPays')->get();

        //Retourne les pays
        $response = [
            'status' => HttpStatus::NoError200($request->getPathInfo()), 
            'data' => $result, 
            'count' =>  count($result)
        ];
        return response()->json($response, 200);
    }
}
