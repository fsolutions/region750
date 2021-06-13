<?php

namespace App\Http\Controllers\API\Address;

use App\Models\City;
use App\Models\House;
use App\Models\Region;
use App\Models\Street;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController
{
    /**
     * Display a listing of regions
     *
     * @return \Illuminate\Http\Response
     */
    public function regionIndex()
    {
        $result = Region::all();

        return $result;
    }

    /**
     * Display a listing of cities
     *
     * @return \Illuminate\Http\Response
     */
    public function cityIndex()
    {
        $region_id = empty(request('region_id')) ? '' : (int) request('region_id');

        $result = City::where('region_id', $region_id);

        return $result;
    }

    /**
     * Display a listing of streets
     *
     * @return \Illuminate\Http\Response
     */
    public function streetIndex()
    {
        $city_id = empty(request('city_id')) ? '' : (int) request('city_id');

        $result = Street::where('city_id', $city_id);

        return $result;
    }

    /**
     * Display a listing of houses
     *
     * @return \Illuminate\Http\Response
     */
    public function houseIndex()
    {
        $house_id = empty(request('house_id')) ? '' : (int) request('house_id');

        $result = Street::where('house_id', $house_id);

        return $result;
    }

    /**
     *  Store a newly created resource in storage.
     *
     * @return \App\Http\Controllers\Model|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store()
    {
        $region_id = empty(request('region_id')) ? '' : (int) request('region_id');
        $city_id = empty(request('city_id')) ? '' : (int) request('city_id');
        $street_id = empty(request('street_id')) ? '' : (int) request('street_id');
        $name = empty(request('name')) ? '' : request('name');

        if ($name != '') {
            if ($street_id != '' && $city_id != '' & $region_id != '') {
                $house = House::create([
                    'region_id' => $region_id,
                    'city_id' => $city_id,
                    'street_id' => $street_id,
                    'name' => $name
                ]);
                return $house;
            } else if ($city_id != '' & $region_id != '') {
                $street = Street::create([
                    'region_id' => $region_id,
                    'city_id' => $city_id,
                    'name' => $name
                ]);
                return $street;
            } else if ($region_id != '') {
                $city = City::create([
                    'region_id' => $region_id,
                    'name' => $name
                ]);
                return $city;
            } else {
                $region = Region::create([
                    'name' => $name
                ]);
                return $region;
            }
        }
    }
}
