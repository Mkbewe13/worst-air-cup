<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Services\CitiesService;
use App\Services\CountriesService;
use App\Services\StatesService;

class StartController extends  Controller
{
    public function welcome(){

        if(!Country::exists()){
            $supportedCountriesService = new CountriesService();
            $supportedCountriesService->refreshSupportedCountriesData();
        }

        if(!State::exists()){
            $supportedStatesService = new StatesService();
            $supportedStatesService->refreshSupportedStatesData('Poland');
        }

        if(!City::exists()){
            $citiesService = new CitiesService();
            $citiesService->refreshCitiesData('Poland');
        }

        return view('welcome');
    }
}
