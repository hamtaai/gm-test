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

    private function functionBodyFactory($output_text, $method, $input_url, $input_data, $expected)
    {
        print $output_text;

        $crawler = $this->client->request(
            $method, $input_url, array(), // request params
            array(), // files
            array(), $input_data
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
