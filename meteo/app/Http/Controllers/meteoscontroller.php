<?php
namespace App\Http\Controllers;

use App\Meteos;
use Illuminate\Http\Request;

class meteoscontroller extends Controller
{
    public function createmeteo(Request $request)
    {
        $meteo = Meteos::create($request->all());
        return response()->json($meteo);
    }

    public function updatemeteo(Request $request, $id)
    {
        $meteo = Meteos::find($id);
        $meteo->vitesse = $request->input('vitesse');
        $meteo->direction = $request->input('direction');
        $meteo->temperature = $request->input('temperature');
        $meteo->save();

        return response()->json($meteo);
    }

    public function deletemeteo($id)
    {
        $meteo = Meteos::find($id);
        $meteo->delete();

        return response()->json('Delete Successfully');
    }

    public function index()
    {
        $meteos = Meteos::all();
        return response()->json($meteos);
    }
}
