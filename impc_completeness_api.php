<?php

// get IMPC phenotypic procedure completeness data

$json_file = 'impc_completeness.json';
$api_url = 'https://api.mousephenotype.org/completeness/colonyProcedureNumbers/UCD';
$hours = 24;
$data = get_content($json_file, $api_url, 24);
$string = file_get_contents($json_file);
 
/* gets the contents of a file if it exists, otherwise grabs and caches */
function get_content($file,$url,$hours) {
	$current_time = time(); 
    $expire_time = $hours * 60 * 60; 
	if(file_exists($file)) {
        $file_time = filemtime($file);
        if ($current_time - $expire_time < $file_time) {
    		//return content from cached file
    		return file_get_contents($file);
        }
    } else {
		$content = get_url($url);
		//return content from refreshed file
		return file_put_contents($file,$content);
    }
}

/* gets content from a URL via curl */
function get_url($url) {
        // initialize cURL
        $ch = curl_init();
        // target URL 
        curl_setopt($ch, CURLOPT_URL,$url);
        // do not include headers in the response
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // converts output to a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        // get the results
        $results = curl_exec($ch);
        // close a cURL session
        curl_close($ch);
        // JSON format
        $response = json_decode(json_encode($results),true);

        if(!$response){
            return array('error' => 'DCC API returned invalid JSON response');
        }
        return $response;
    }

?>
