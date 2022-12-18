<?php

namespace App\Services;

use App\Models\SupportedCountry;

class SupportedCountriesService
{

    public function refreshSupportedCountriesData(array $supportedCountries): void
    {

        if(empty($supportedCountries)){
            return;
        }

        foreach ($supportedCountries as $key => $country){
            SupportedCountry::firstOrCreate([
                'name' => $country
            ]);
        }

    }

    private function checkIfCountryExistInDatabase(string $country)
    {

    }

}
