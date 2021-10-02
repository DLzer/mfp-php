<?php

namespace App\Test\TestCase\Service;

use DLzer\MfpService;
use PHPUnit\Framework\TestCase;

class MfpServiceTest extends TestCase
{

    protected $mfp;

    public function testDateCanBeFormatted()
    {

        
        $this->mfp = new MfpService('yodaesu', '2019-12-12');
        $dateCheck = $this->mfp->checkDateFormat();

        $this->assertEquals(
            true,
            $dateCheck
        );

    }

    public function testMfpStructureExists()
    {

        $this->mfp = new MfpService('yodaesu', '2019-12-12');
        $response = $this->mfp->fetch();

        $response = json_encode($response);

        $this->assertArrayHasKey('user', $response);
        $this->assertArrayHasKey('carbs', $response['user']);
        $this->assertArrayHasKey('fat', $response['user']);
        $this->assertArrayHasKey('protein', $response['user']);

    }

    public function testLargeIntParsing() {

        $this->mfp = new MfpService('yodaesu', '2019-12-12');
        $response = $this->mfp->parseLargeInt('2,020');
        $response = (int)$response;

        $this->assertEquals(2020, $response);

    }

}