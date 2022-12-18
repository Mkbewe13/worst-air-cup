<?php

namespace App\Http\Controllers;

use App\Integrations\IQAirIntegration;
use App\Services\StatesService;

class CitiesController extends Controller{

    private $IQAir;

    /**
     * @param $IQAir
     */
    public function __construct()
    {
        $this->IQAir = new IQAirIntegration();
    }


    public function index($country_name){



    }

}
