<?php

namespace App\Integrations;

use App\Services\CountriesService;
use App\Services\StatesService;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Http;
use mysql_xdevapi\Exception;

class IQAirIntegration
{

   private Client $httpClient;

    /**
     * @param Client $httpClient
     */
    public function __construct()
    {
        $this->httpClient = new Client();
    }


    public function getSupportedCountries()
    {

        $result = [];

        $supportedCitiesArray = $this->getResponse('http://api.airvisual.com/v2/countries?key=' . $this->getApiKey());
        if (empty($supportedCitiesArray->data)) {
            return $result;
        }


        foreach ($supportedCitiesArray->data as $item) {
            $result[] = $item->country;
        }

        return $result;
    }

    private function validateStatusCode( $status ){

        //@todo request status validation

    }

    private function getApiKey(): string{

        $apiKey = env('IQ_AIR_API_KEY');

        if($apiKey === null){
            throw new Exception('IQ AIR apikey is missing. Check your enviroment variables');
        }

        return $apiKey;
    }

    public function getSupportedStatesNamesByCountry(string $country): array{

        if (!$this->validateCountry($country)) {
            throw new \Exception('Given country is not supported or given name is wrong. Try with proper english country name');
        }

        $supportedStatesArray = $this->getResponse('http://api.airvisual.com/v2/states?country=' . $country . '&key=' . $this->getApiKey());

        if (empty($supportedStatesArray->data)) {
            throw new \Exception('There is no supported states for given country');
        }
        $result = [];
        foreach ($supportedStatesArray->data as $item) {
            $result[] = $item->state;
        }

        return $result;

    }


    public function getSupportedCitiesNamesByCountry(string $country): array
    {

        if (!in_array($country, $this->supportedCountries)) {
            throw new \Exception('Given country is not supported');
        }

        $supportedCities = [];

        $countryStates = $this->getSupportedStatesNamesByCountry('Poland');
        sleep(2);
        foreach ($countryStates as $state) {
            sleep(2);
            $supportedCities[] = $this->getSupportedCountryCitiesByState($country, $state);
        }


        if (empty($supportedCities)) {
            throw new \Exception('There is no supported states for given country');
        }

        return $supportedCities;


    }

    public function getSupportedCountryCitiesByState(string $country, string $state): array{

        if(!$this->validateCountry($country)){
            throw new \Exception('Given country is not supported or given name is wrong. Try with proper english country name');
        }

        $result = [];
        $supportedCitiesArray = $this->getResponse('http://api.airvisual.com/v2/cities?state=' . $state . '&country=' . $country . '&key=' . $this->getApiKey(),true);

        foreach ($supportedCitiesArray['data'] as $item){
           $result[] = $item['city'];
        }

        return $result;
    }

//    private function getAllCitiesByCountry(string $country): array{
//
//    }

    private function validateCountry(string $country){
        return in_array($country,self::getSupportedCountries());
    }


    private function getResponse(string $httpUrl,bool $associative = false)
    {
        try {
            $request = new Request('GET', $httpUrl);
            $response = $this->httpClient->sendAsync($request)->wait();
            $resContent = $response->getBody()->getContents();
            if($associative === true){
                $resContent = json_decode($resContent,true);
            }else{
                $resContent = json_decode($resContent);
            }

        } catch (\Exception $e) {
            throw new \Exception('An error occurred while taking response from IQAIR web api. Error: ' . $e);
        }

        return $resContent;
    }

}
