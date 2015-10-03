<?php namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{

    protected $client;
    protected $postInputData;

    public function setUp()
    {
        print " ApiControllerTest.php: ";
        parent::setUp();

        $this->client = static::createClient();
    }

    public function tearDown()
    {
        parent::tearDown();

        print "\n";
    }

    public function testGetTemperature()
    {
        $this->functionBodyFactory(
            "Testing /current_temperature. ", 'GET', '/api/current_temperature', NULL, 200
        );
    }

    public function testEmailTemperatureValid()
    {
        $email = "template.name@test.rest";
        $input = json_encode(array("to" => $email));

        $this->functionBodyFactory(
            "Testing /email_temperature (valid data). ", 'POST', '/api/email_temperature', $input, 200
        );
    }

    public function testEmailTemperatureInvalid()
    {
        $email = "template.name";
        $input = json_encode(array("to" => $email));

        $this->functionBodyFactory(
            "Testing /email_temperature (invalid data). ", 'POST', '/api/email_temperature', $input, 400
        );
    }

    public function testSubscribeTemperatureValid()
    {
        $email = "template.name@test.rest";
        $input = json_encode(array("to" => $email));

        $this->functionBodyFactory(
            "Testing /subscribe_temperature (valid data). ", 'POST', '/api/subscribe_temperature', $input, 200
        );
    }

    public function testSubscribeTemperatureValidAgain()
    {
        $email = "template.name@test.rest";
        $input = json_encode(array("to" => $email));

        $this->functionBodyFactory(
            "Testing /subscribe_temperature (same-as-before valid data). ", 'POST', '/api/subscribe_temperature', $input, 422
        );

        //Cleanup
        $this->removeEmailFromDatabase($email);
    }

    private function functionBodyFactory($output_text, $method, $input_url, $input_data, $expected)
    {
        print $output_text;

        $crawler = $this->client->request(
            $method, //Method of the request 
            $input_url, //where to send the request
            array(), // params
            array(), // files
            array(
            'CONTENT_TYPE' => 'application/json',
            ), // server
            $input_data //input data
        );

        $this->assertJsonResponse($this->client->getResponse(), $expected);
    }

    private function removeEmailFromDatabase($email)
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $kernel->getContainer()
            ->get('doctrine')
            ->getEntityManager();

        //Remove from email table
        $repo = $em->getRepository('AppBundle:Email');

        foreach ($repo->findAll() as $entity) {
            if ($entity->getTo() == $email) {
                $em->remove($entity);
            }
        }

        //Remove from crontasks
        $repo = $em->getRepository('AppBundle:CronTask');

        foreach ($repo->findAll() as $entity) {
            if ($entity->getName() == $email) {
                $em->remove($entity);
            }
        }

        //Apply changes
        $em->flush();
    }

    protected function assertJsonResponse($response, $statusCode = 200)
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(), $response->getContent()
        );
    }
}
