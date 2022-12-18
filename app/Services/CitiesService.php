<?php

namespace App\Services;

use App\Integrations\IQAirIntegration;
use App\Models\City;
use App\Models\Country;

class CitiesService
{


    public function refreshCitiesData(string $country_name): void
    {

        $country = Country::where('name',$country_name)->first();
        if(!$country || !$country->id){
            return;
        }

        $country_states = $country->states;

        if(empty($country_states)){
            return;
        }

        $iqAir = new IQAirIntegration();

        foreach ($country_states as $state){

           $cities = $iqAir->getSupportedCountryCitiesByState($country->name,$state->name);

           foreach ($cities as $key => $city){
               City::firstOrCreate([
                   'name' => $city,
                   'country_id' => $country->id,
                   'state_id' => $state->id
               ]);

           }
            sleep(10);

        }


    }


}
