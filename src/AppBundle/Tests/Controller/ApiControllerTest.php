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
        $input = json_encode(array("to" => "template.name@test.rest"));
        
        $this->functionBodyFactory(
            "Testing /email_temperature (valid data). ", 'POST', '/api/email_temperature', $input, 200
        );
    }

    public function testEmailTemperatureInvalid()
    {
        $input = json_encode(array("to" => "template.name"));
        
        $this->functionBodyFactory(
            "Testing /email_temperature (invalid data). ", 'POST', '/api/email_temperature', $input, 400
        );
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

    protected function assertJsonResponse($response, $statusCode = 200)
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(), $response->getContent()
        );
    }
}
