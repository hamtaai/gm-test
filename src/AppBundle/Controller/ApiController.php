<?php namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Email as Email;
use AppBundle\Form\RestEmailType;
use Symfony\Bundle\MonologBundle\SwiftMailer;

class ApiController extends FOSRestController
{

    private $contentType = array('Content-Type' => 'application/json');

    /*
     * $unit is a string
     * "metric" returns the current temp. in celsius
     * "imperial" returns the current temp. in fahrenheit
     * any other value returns the current temp. in kelvin
     */

    private function getCurrentTemperature($unit = "metric")
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
        $data = $this->getCurrentTemperature();

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
        $entity = new Email();
        $form = $this->createForm(new RestEmailType(), $entity);
        $form->handleRequest($this->getRequest());

        $data = array(
            'code' => 400,
            'status' => 'Bad request.'
        );

        if ($form->isValid()) {
            if ($this->sendEmail($entity->getTo()) !== 0) {
                $data["code"] = 200;
                $data["status"] = "OK";
            } else {
                $data["code"] = 550;
                $data["status"] = "Sending the email failed.";
            }
        }

        return new Response(
            json_encode(array("status" => $data["status"])), $data["code"], array('Content-Type' => 'application/json')
        );
    }

    private function sendEmail($to)
    {
        $currTemp = $this->getCurrentTemperature()["message"];
        $mailMsg = "Hello, " . $to . "!\n"
            . "\nThank you for using the API."
            . "\nThe temperature in budapest is currently $currTemp celsius.";

        $message = \Swift_Message::newInstance()
            ->setSubject('Budapest Weather')
            ->setFrom(array("havelant.mate@gmail.com" => "Havelant Máté"))
            ->setTo(array($to => "Receiver"))
            ->setBody($mailMsg);

        echo "\n-----------------\n";
        echo $mailMsg;
        echo "\n-----------------\n";

        return $this->get('mailer')->send($message);
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
