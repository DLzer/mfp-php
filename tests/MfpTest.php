<?php

use PHPUnit\Framework\TestCase;

final class MfpTest extends TestCase
{

    protected $client;
    protected $mfp;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://www.myfitnesspal.com'
        ]);

    }

    public function testDateCanBeFormatted()
    {

        $this->assertEquals(
            true,
            (new MfpService('test', '2019-12-12'))->checkDateFormat()
        );

    }

    public function testMfpPositiveResponse()
    {

        $response = $this->client->get('/reports/printable_diary');

        $this->assertEquals(200, $response->getStatusCode());

    }

    public function testMfpStructureExists()
    {

        $this->mfp = new MfpService('yodaesu', '2019-12-12');
        $response = $this->mfp->fetch();

        $response = json_decode(json_encode($response), true);

        $this->assertArrayHasKey('user', $response);
        $this->assertArrayHasKey('carbs', $response['user']);
        $this->assertArrayHasKey('fat', $response['user']);
        $this->assertArrayHasKey('protein', $response['user']);

    }

}