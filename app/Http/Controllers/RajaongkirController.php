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
        $rr =  $r->all();
        $client = new Client();
        $response = $client->request('POST', 'https://pro.rajaongkir.com/api/cost', [
            'headers' => ['key' =>  $this->key],
            'form_params' => $rr
        ]);
        return $response;
    }

    public function official(Request $request)
    {
        $product = Product::find($request->product_id);
        $address = Address::find($request->address_id);
        $weight = $product->weight * $request->qty * ($request->qty_month ?? 1);
        $client = new Client();
        $response = $client->request('POST', 'https://pro.rajaongkir.com/api/cost', [
            'headers' => ['key' =>  $this->key],
            'form_params' => [
                'origin' => User::where('type', 'admin')->first()->address->subdistrict_id,
                'originType' => 'subdistrict',
                'destination' => $address->subdistrict_id,
                'destinationType' => 'subdistrict',
                'weight' => $weight,
                'courier' => 'jne:jnt',
            ]
        ]);
        return $response->getBody();
    }
}
