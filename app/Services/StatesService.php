<?php

namespace App\Services;

use App\Integrations\IQAirIntegration;
use App\Models\Country;
use App\Models\State;

class StatesService
{

    public function refreshSupportedStatesData(string $country_name): void
    {

        $country = Country::where('name',$country_name)->first();
        if(!$country || !$country->id){
            return;
        }

        $iqAir = new IQAirIntegration();
        $supportedStates = $iqAir->getSupportedStatesNamesByCountry($country->name);


        if(empty($supportedStates)){
            return;
        }

        foreach ($supportedStates as $key => $state){
            State::firstOrCreate([
                'name' => $state,
                'country_id' => $country->id
            ]);
        }

    }

}
