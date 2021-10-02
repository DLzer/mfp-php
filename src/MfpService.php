<?php

namespace DLzer;

use StdClass;
use DOMDocument;
use DateTime;

/** 
 *  MfpService
 * 
 *  Manages requests to MyFitnessPal
 */
class MfpService {

    /** @var string */
    private $username;
    
    /** @var string */
    private $date;

    /** @var string */
    private $baseUrl;

    /** @var stdClass */
    private $response;

    /**
     * Constructor
     *
     * @param string $username The MFP Username.
     * @param string $date The date in YYYY-MM-DD format. 
     */
    public function __construct(
        string $username,
        string $date
        ) {
        $this->username     = $username;
        $this->date         = $date;
        $this->baseUrl      = "https://www.myfitnesspal.com/reports/printable_diary/";
    }

    /**
     * Process a request for a single date of MFP macro data
     */
    public function fetch()
    {
        $this->checkDateFormat();
        $this->makeRequest();
        return $this->parseResponse();
    }

    /**
     * Format the request URL
     *
     * @return string The request URL
     */
    private function formatRequestString(): string
    {
        return $this->request = "{$this->baseUrl}{$this->username}?from={$this->date}&to={$this->date}";
    }

    /**
     * Make a request using the formatted URL
     */
    private function makeRequest(): void
    {

        // The pre-formatted request string
        $requestString = $this->formatRequestString();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestString);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response   = curl_exec($ch);
        $result     = json_decode($response);

        curl_close($ch);

        $this->response = $result;


        return;
    }

    /**
     * Load the response variable into DOMDocument and navigate through the DOM elements
     * to craft the parsedResponse return. If the response contains a privacy strings, return
     * and error about the "Diary is Private".
     */
    private function parseResponse()
    {

        $macro_array = array();
        $this->parsedResponse = new StdClass;

        $doc = new DOMDocument;
        $doc->preserveWhiteSpace = false;
        libxml_use_internal_errors(true); // Hush errors about invalid HTML. Invalid HTML is quite common, this just silences the warnings.
        $doc->loadHTML((string)$this->response);

        // Check to see if username exists
        $mtitle = $doc->getElementById('settings')->childNodes;
        if ($mtitle != null) {
            foreach( $mtitle as $node ) {
                if( $node->nodeValue == "This Username is Invalid") {
                    return ['error' => "This Username is Invalid"];
                }
            }
        }
    

        // Check if the diary is set to private
        $title = $doc->getElementById('settings')->childNodes;
        if ( $title != null ) {
            foreach( $title as $node ) {
                if ($node->nodeValue == "This Diary is Private") {
                    return ['error' => "Diary is Private"];
                }
            }
        }
        

        // Check if their are any entries for the date range
        $dateEl = $doc->getElementById('date');
        if( $dateEl != null ) {
            if($dateEl->textContent == "No diary entries were found for this date range.") {
                return ['error' => "No diary entries were found for this date."];
            }
        }
        

        // Find the macro table row
        $tables = $doc->getElementsByTagName('tfoot');
        $rows = $tables->item(0)->getElementsByTagName('tr');
        foreach ( $rows as $row ) {
            $cols = $row->getElementsByTagName('td');
            foreach ( $cols as $col ) {
                array_push($macro_array, $col->textContent);
            }
        }

        $this->parsedResponse->username = $this->username;
        $this->parsedResponse->date     = $this->date;
        $this->parsedResponse->calories = (int)$this->parseLargeInt($macro_array[1]);
        $this->parsedResponse->carbs    = (int)$this->parseLargeInt(substr($macro_array[2], 0, -1));
        $this->parsedResponse->fat      = (int)$this->parseLargeInt(substr($macro_array[3], 0, -1));
        $this->parsedResponse->protein  = (int)$this->parseLargeInt(substr($macro_array[4], 0, -1));
        $this->parsedResponse->cholest  = (int)$this->parseLargeInt(substr($macro_array[5], 0, -2));
        $this->parsedResponse->sodium   = (int)$this->parseLargeInt(substr($macro_array[6], 0, -2));
        $this->parsedResponse->sugars   = (int)$this->parseLargeInt(substr($macro_array[7], 0, -1));
        $this->parsedResponse->fiber    = (int)$this->parseLargeInt(substr($macro_array[8], 0, -1));
        
        return ['success' => $this->parsedResponse];

    }

    /**
     * Using REGEX to confirm the date is in a correct format
     */
    public function checkDateFormat()
    {

        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $this->date)) {
            return true;
        } else {
            return ['error' => 'Incorrect date format'];
        }

    }

    /**
     * Convert large integers with a comma to INT
     * 
     * @param int
     */
    public function parseLargeInt($int)
    {

        if(preg_match("/^[0-9,]+$/", $int)) {
            $int = str_replace(',', '', $int);
        }
        
        return $int;

    }

}