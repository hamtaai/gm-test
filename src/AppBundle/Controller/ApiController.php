<?php

namespace AppBundle\Controller;

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
    /**
     * Return the current temperature (in celsius) in budapest
     * Route: /api/current_temperature
     * Method: GET
     * -----------
     * @Rest\View
     * @Route("/api/current_temperature")
     */

    public function getCurrentTemperatureAction() {
        $jsonurl = "http://api.openweathermap.org/data/2.5/weather?q=Budapest,hu";
        $json = file_get_contents($jsonurl);

        $weather = json_decode($json);
        $kelvin = $weather->main->temp;
        $celcius = $kelvin - 273.15;
               
        $data = array(
            'temp' => $celcius
        );
        
        return new Response(
            json_encode($data),
            200,
            array('Content-Type' => 'application/json')
        );
    }
    
    /*
     * 
     * 
     * POST /email_temperature

        Send the current temperature to the specified email address 
     * Body Params

-to : recipient email address
     * 
     *     Example response :
        Code: 200
        Content: { status : 'ok' }
     */
    
    
    /*
     * POST /subscribe_temperature

Send the current temperature to the specified email address in every hour

    Body Params

    -to : recipient email address

    Example response :
        Code: 200
        Content: { status : 'ok' }

     */

}
