<?php

declare(strict_types=1);

namespace DLzer\examples;

use DLzer\MfpService;

class GetMfpMacros
{
    public function run()
    {
        $username   = 'yodaesu';
        $date       = '2019-12-12';

        $mfpService = new MfpService($username, $date);

        print_r($mfpService->fetch());
        return;
    }
}

$a = new GetMfpMacros();
$a->run();