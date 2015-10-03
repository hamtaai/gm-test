<?php namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\Email as Email;
use AppBundle\Entity\CronTask as CronTask;
use AppBundle\Form\RestEmailType;

class ApiController extends FOSRestController
{

    private $contentType = array('Content-Type' => 'application/json');

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
            if ($this->sendEmail($entity->getTo(), $this->emailMsgFactory($entity->getTo(), "using")) > 0) {
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
        $entity = new Email();
        $form = $this->createForm(new RestEmailType(), $entity);
        $form->handleRequest($this->getRequest());

        $data = array(
            'code' => 400,
            'status' => 'Bad request.'
        );

        if ($form->isValid()) {
            try {
                $em = $this->getDoctrine()->getManager();
                if ($this->isEmailUnique($em, $entity->getTo())) {
                    $em->persist($entity);
                                  
                    $cronEntity = new CronTask();
                    $cronEntity
                        ->setName($entity->getTo())
                        ->setTaskInterval(3600)
                        ->setLastRun(new \DateTime());
                    $em->persist($cronEntity);
                    $em->flush();
                    
                    $this->sendEmail($entity->getTo(), $this->emailMsgFactory($entity->getTo(), "subscribing to"));
                                        
                    $data["code"] = 200;
                    $data["status"] = "OK";
                } else {
                    $data["code"] = 422;
                    $data["status"] = "There's already a subscription with the given email address!";
                }
            } catch (Excpetion $e) {
                $data["code"] = 500;
                $data["status"] = "Server error! Subscription failed!.";
            }
        }

        return new Response(
            json_encode(array("status" => $data["status"])), $data["code"], array('Content-Type' => 'application/json')
        );
    }

    private function isEmailUnique($em, $email)
    {
        if ($em->getRepository('AppBundle:Email')->findOneBy(array('to' => $email)) == NULL) {
            return true;
        }

        return false;
    }

    private function sendEmail($to, $mailMsg)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Budapest Weather')
            ->setFrom(array("havelant.mate@gmail.com" => "Havelant Máté"))
            ->setTo(array($to => "Receiver"))
            ->setBody($mailMsg);

        //Return with the number of e-mails sent
        $sentCount = $this->get('mailer')->send($message);
        //Send the email by flushing the spool
        $spool = $this->get('mailer')->getTransport()->getSpool();
        $spool->flushQueue($this->get('swiftmailer.transport.real'));
        
        return $sentCount;
    }
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

    private function emailMsgFactory($to, $why)
    {
        $currTemp = $this->getCurrentTemperature()["message"];
        $msg = "Hello, " . $to . "!\n"
            . "\nThank you for ".trim($why)." the API."
            . "\nThe temperature in budapest is currently $currTemp degree celsius.";
        
        return $msg;
    }
}
