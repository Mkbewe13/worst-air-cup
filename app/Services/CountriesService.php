<?php

namespace App\Services;

use App\Integrations\IQAirIntegration;
use App\Models\Country;

class CountriesService
{

    public function refreshSupportedCountriesData(): void
    {

        $iqAir = new IQAirIntegration();
        $supportedCountries = $iqAir->getSupportedCountries();

        if(empty($supportedCountries)){
            return;
        }

        foreach ($supportedCountries as $key => $country){
            Country::firstOrCreate([
                'name' => $country
            ]);
        }

    }

}
