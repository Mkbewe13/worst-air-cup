<?php

namespace App\Integrations;

use GuzzleHttp\Client;
use http\Client\Request;
use Illuminate\Support\Facades\Http;
use mysql_xdevapi\Exception;

class IQAirIntegration
{

   private Client $httpClient;
   private array $supportedCountries = [];

    /**
     * @param Client $httpClient
     */
    public function __construct()
    {
        $this->httpClient = new Client();
        $this->setSupportedCountries();
    }


    private function setSupportedCountries()
    {
        $response = $this->httpClient->get('http://api.airvisual.com/v2/countries?key=' . $this->getApiKey());
        $responseContent = $response->getBody()->getContents();

        //@todo validate request

        $supportedCitiesArray = json_decode($responseContent);

        if (empty($supportedCitiesArray->data)) {
            return;
        }

        foreach ($supportedCitiesArray->data as $item) {
            $this->supportedCountries[] = $item->country;
        }
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

    private function getSupportedStatesNamesByCountry(string $country): array{

        if (!$this->validateCountry($country)) {
            throw new \Exception('Given country is not supported or given name is wrong. Try with proper english country name');
        }
        sleep(3);
        $response = $this->httpClient->get('http://api.airvisual.com/v2/states?country=' . $country . '&key=' . $this->getApiKey());
        $responseContent = $response->getBody()->getContents();

        //@todo validate request

        $supportedStatesArray = json_decode($responseContent);

        if (empty($supportedStatesArray->data)) {
            throw new \Exception('There is no supported states for given country');
        }
        $result = [];
        foreach ($supportedStatesArray->data as $item) {
            $result[] = $item->state;
        }

        return $result;

    }


    private function getSupportedCitiesNamesByCountry(string $country): array
    {

        if (!in_array($country, $this->supportedCountries)) {
            throw new \Exception('Given country is not supported');
        }

        $supportedCities = [];

        $countryStates = $this->getSupportedStatesNamesByCountry('Poland');
        sleep(3);
        foreach ($countryStates as $state) {

            $supportedCities[] = $this->getSupportedCountryCitiesByState($country, $state);
            sleep(2);
        }


        if (empty($supportedCities)) {
            throw new \Exception('There is no supported states for given country');
        }

        return $supportedCities;


    }

    private function getSupportedCountryCitiesByState(string $country, string $state): array{

        if(!$this->validateCountry($country)){
            throw new \Exception('Given country is not supported or given name is wrong. Try with proper english country name');
        }

        $response = $this->httpClient->get('http://api.airvisual.com/v2/cities?state=' . $state . '&country=' . $country . '&key=' . $this->getApiKey());
        $this->httpClient->sendAsync($response)->wait();
        $responseContent = $response->getBody()->getContents();

        //@todo validate request
        $result = [];
        $supportedCitiesArray = json_decode($responseContent);

        foreach ($supportedCitiesArray as $item){
           $result[] = $item->city;
        }

        return $result;
    }

//    private function getAllCitiesByCountry(string $country): array{
//
//    }

    private function validateCountry(string $country){
        return in_array($country,self::getSupportedCountries());
    }

    private static function getSupportedCountries(): array
    {
        $IQAir = new IQAirIntegration();
        $IQAir->setSupportedCountries();
        return $IQAir->getCountries();
    }

    public function getCountries(): array{
        return $this->getCountries();
    }

}
