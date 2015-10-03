<?php namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\Annotations as Rest;

class ApiController extends FOSRestController
{
    private $contentType = array('Content-Type' => 'application/json');

    /*
     * $unit is a string
     * "metric" returns the current temp. in celsius
     * "imperial" returns the current temp. in fahrenheit
     * any other value returns the current temp. in kelvin
     */
    private function getCurrentTemperature($unit)
    {
        $units = "";

        if ($unit === "metric" || $unit === "imperial") {
            $units = "&units=" . $unit;
        }

        //List of city ID city.list.json.gz can be downloaded here http://bulk.openweathermap.org/sample/
        //$cityID from http://openweathermap.org/help/city_list.txt
        //Should automate this.. But won't.. For now..
        $cityID = "3054643";

        $jsonurl = "http://api.openweathermap.org/data/2.5/weather?id=" . $cityID . $units;
        $json = file_get_contents($jsonurl);

        //If everything goes wrong, we presume that the API is unavailable.
        $retData = array(
            "message" => "The openweathermap API is unavailable.",
            "code" => 503
        );

        if ($json !== FALSE) {
            $weather = json_decode($json);
            $retData["code"] = $weather->cod;
            //If we can get the file from the API ($json is not FALSE)
            //And the returned data is OK (code is 200)
            if ($retData["code"] === 200) {
                $retData["message"] = $weather->main->temp;
            } else {
                $retData["message"] = "Error!";
            }
        }

        return $retData;
    }

    /**
     * Return the current temperature (in celsius) in Budapest
     * Route: /api/current_temperature
     * Method: GET
     * -----------
     * @Rest\View
     * @Route("/api/current_temperature")
     */
    public function getCurrentTemperatureAction()
    {
        $data = $this->getCurrentTemperature("metric");

        return new Response(
            json_encode(array(
                "temp" => $data["message"]
            )), $data["code"], $this->contentType
        );
    }

    /**
     * Send the current temperature to the specified email address 
     * Method: POST
     * -----------
     * @Rest\View
     * @Route("/api/email_temperature")
     */
    public function postEmailTemperatureAction()
    {
        //$to comes from POST

        $data = array(
            'status' => 'OK'
        );

        return new Response(
            json_encode($data), 200, array('Content-Type' => 'application/json')
        );
    }

    /**
     * Send the current temperature to the specified email address in every hour
     * Method: POST
     * -----------
     * @Rest\View
     * @Route("/api/subscribe_temperature")
     */
    public function postSubscribeTemperatureAction()
    {
        //$to comes from POST
        $data = array(
            'status' => 'OK'
        );

        return new Response(
            json_encode($data), 200, array('Content-Type' => 'application/json')
        );
    }
}
