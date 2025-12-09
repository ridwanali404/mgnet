<?php

namespace App\Http\Controllers;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Illuminate\Http\Request;
use DB;
use App\Models\Province;
use App\Models\City;
use App\Models\Subdistrict;
use App\Models\Product;
use App\Models\Address;
use App\Models\User;

class RajaongkirController extends Controller
{
    public function __construct() {
        $this->key = '2269f77837513d8cd5bc7677f48c9234';
    }

    public function index()
    {
        ini_set('max_execution_time', 180);
        $this->getProvince();
        $this->getCity();
        $this->getSubdistrict();
    }

    public function getProvince()
    {
        $client = new Client();
        $rajaongkirProvince = $client->request('GET', 'https://pro.rajaongkir.com/api/province', ['headers' => ['key' => $this->key]])->getBody()->getContents();
        $rajaongkirProvince = json_decode($rajaongkirProvince);
        DB::statement("SET foreign_key_checks=0");
        Province::truncate();
        foreach ($rajaongkirProvince->rajaongkir->results as $value) {
            Province::create([
                'province_id' => $value->province_id,
                'province' => $value->province
            ]);
        }
        DB::statement("SET foreign_key_checks=1");
        echo "province added!";
    }

    public function getCity()
    {
        $client = new Client();
        $rajaongkirCity = $client->request('GET', 'https://pro.rajaongkir.com/api/city', ['headers' => ['key' => $this->key]])->getBody()->getContents();
        $rajaongkirCity = json_decode($rajaongkirCity);
        DB::statement("SET foreign_key_checks=0");
        City::truncate();
        foreach ($rajaongkirCity->rajaongkir->results as $value) {
            City::create([
                'city_id' => $value->city_id,
                'province_id' => $value->province_id,
                'type' => $value->type,
                'city_name' => $value->city_name,
                'postal_code' => $value->postal_code
            ]);
        }
        DB::statement("SET foreign_key_checks=1");
        echo "city added!";
    }

    public function getSubdistrict()
    {
        $client = new Client();
        // var_dump($rajaongkirSubdistrict->rajaongkir->results);
        DB::statement("SET foreign_key_checks=0");
        Subdistrict::truncate();
        $cities = City::all();
        foreach ($cities as $city) {
            $rajaongkirSubdistrict = $client->request('GET', 'https://pro.rajaongkir.com/api/subdistrict?city='.$city->city_id, ['headers' => ['key' => $this->key, 'originType' => 'subdistrict']])->getBody()->getContents();
            $rajaongkirSubdistrict = json_decode($rajaongkirSubdistrict);
            foreach ($rajaongkirSubdistrict->rajaongkir->results as $value) {
                Subdistrict::create([
                    "subdistrict_id" => $value->subdistrict_id,
                    "province_id" => $value->province_id,
                    "province" => $value->province,
                    "city_id" => $value->city_id,
                    "city" => $value->city,
                    "type" => $value->type,
                    "subdistrict_name" => $value->subdistrict_name
                ]);
            }
        }
        DB::statement("SET foreign_key_checks=1");
        echo "subdistrict added!";
    }

    public function cost(Request $r)
    {
        $rr = $r->all();
        $client = new Client();
        try {
            // Map parameter lama ke format baru jika perlu
            $params = [
                'origin' => $rr['origin'] ?? '',
                'destination' => $rr['destination'] ?? '',
                'weight' => $rr['weight'] ?? 1000,
                'courier' => $rr['courier'] ?? 'jne:jnt',
                'price' => 'lowest'
            ];
            
            $response = $client->request('POST', 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'headers' => [
                    'key' => $this->key,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => $params
            ]);
            $responseBody = json_decode($response->getBody(), true);
            // Transform response baru ke format lama
            $transformedResponse = $this->transformRajaOngkirResponse($responseBody);
            // Return sebagai JSON dengan Content-Type yang benar (jQuery akan otomatis parse)
            return response()->json($transformedResponse);
        } catch (\Exception $e) {
            return response()->json([
                'rajaongkir' => [
                    'status' => ['code' => 500, 'description' => 'Error: ' . $e->getMessage()],
                    'results' => []
                ]
            ], 500);
        }
    }

    public function official(Request $request)
    {
        $product = Product::find($request->product_id);
        $address = Address::find($request->address_id);
        $weight = $product->weight * $request->qty * ($request->qty_month ?? 1);
        $client = new Client();
        try {
            $response = $client->request('POST', 'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'headers' => [
                    'key' => $this->key,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'origin' => User::where('type', 'admin')->first()->address->subdistrict_id,
                    'destination' => $address->subdistrict_id,
                    'weight' => $weight,
                    'courier' => 'jne:jnt',
                    'price' => 'lowest'
                ]
            ]);
            $responseBody = json_decode($response->getBody(), true);
            // Transform response baru ke format lama
            $transformedResponse = $this->transformRajaOngkirResponse($responseBody);
            // Return sebagai JSON dengan Content-Type yang benar (jQuery akan otomatis parse)
            return response()->json($transformedResponse);
        } catch (\Exception $e) {
            return json_encode([
                'rajaongkir' => [
                    'status' => ['code' => 500, 'description' => 'Error: ' . $e->getMessage()],
                    'results' => []
                ]
            ]);
        }
    }

    /**
     * Transform response API RajaOngkir baru ke format lama
     * Format baru: {"meta": {...}, "data": [{"name": "...", "code": "...", "service": "...", "cost": ..., "etd": "..."}]}
     * Format lama: {"rajaongkir": {"results": [{"name": "...", "code": "...", "costs": [{"service": "...", "cost": [{"value": ..., "etd": "..."}]}]}]}}
     */
    private function transformRajaOngkirResponse($responseBody)
    {
        if (!isset($responseBody['data']) || !is_array($responseBody['data'])) {
            return [
                'rajaongkir' => [
                    'results' => []
                ]
            ];
        }

        // Group by courier code
        $grouped = [];
        foreach ($responseBody['data'] as $item) {
            $code = $item['code'] ?? 'unknown';
            if (!isset($grouped[$code])) {
                $grouped[$code] = [
                    'name' => $item['name'] ?? '',
                    'code' => $code,
                    'costs' => []
                ];
            }
            
            // Format ETD: "6 day" -> "6 HARI" atau sesuai format lama
            $etd = '';
            if (isset($item['etd']) && $item['etd']) {
                $etd = str_replace(' day', ' HARI', $item['etd']);
            }
            
            $grouped[$code]['costs'][] = [
                'service' => $item['service'] ?? '',
                'description' => $item['description'] ?? '',
                'cost' => [
                    [
                        'value' => $item['cost'] ?? 0,
                        'etd' => $etd
                    ]
                ]
            ];
        }

        return [
            'rajaongkir' => [
                'results' => array_values($grouped)
            ]
        ];
    }
}
