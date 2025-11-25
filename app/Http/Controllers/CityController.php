<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Province;

class CityController extends Controller
{
    public function index()
    {
        // $data =  [
        //       {
        //         "text": "Group 1",
        //         "children" : [
        //           {
        //               "id": 1,
        //               "text": "Option 1.1"
        //           },
        //           {
        //               "id": 2,
        //               "text": "Option 1.2"
        //           }
        //         ]
        //       },
        //       {
        //         "text": "Group 2",
        //         "children" : [
        //           {
        //               "id": 3,
        //               "text": "Option 2.1"
        //           },
        //           {
        //               "id": 4,
        //               "text": "Option 2.2"
        //           }
        //         ]
        //       }
        //     ]
        //   ;
        //   return $data;
        $cities = Province::with('cities')->get()->map(function($province) {
            $province['text'] = $province['province'];
            $provinceCities = collect($province['cities']);
            if (request()->q) {
                $provinceCities = $provinceCities->filter(function ($city) {
                    return false !== stripos($city->city_name, request()->q);
                });
            }
            $province['children'] = $provinceCities->map(function ($city) {
                $city['id'] = $city['city_id'];
                $city['text'] = $city['city_name'];
                return $city;
            });
            return $province;
        });
        return response()->json($cities);
    }
}
