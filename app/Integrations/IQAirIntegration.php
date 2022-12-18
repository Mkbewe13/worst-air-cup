<?php

namespace App\Integrations;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
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

        $supportedCitiesArray = $this->getResponse('http://api.airvisual.com/v2/countries?key=' . $this->getApiKey());
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

    private function getSupportedCountryCitiesByState(string $country, string $state): array{

        if(!$this->validateCountry($country)){
            throw new \Exception('Given country is not supported or given name is wrong. Try with proper english country name');
        }

        $result = [];
        $supportedCitiesArray = $this->getResponse('http://api.airvisual.com/v2/cities?state=' . $state . '&country=' . $country . '&key=' . $this->getApiKey());

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
        return $this->supportedCountries;
    }

    private function getResponse(string $httpUrl): \stdClass
    {
        try {
            $request = new Request('GET', $httpUrl);
            $response = $this->httpClient->sendAsync($request)->wait();
            $resContent = $response->getBody()->getContents();
            $resContent = json_decode($resContent);
        } catch (\Exception $e) {
            throw new \Exception('An error occurred while taking response from IQAIR web api. Error: ' . $e);
        }

        return $resContent;
    }

}
