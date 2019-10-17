<?php
// Retrieve UCD data at IMPC
$url= "http://api.mousephenotype.org/tracker/centre/xml?centre=Ucd";
// initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
// do not include headers in the response
curl_setopt($ch, CURLOPT_HEADER, 0);
// return the result to a variable
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
// get the results
$results_json = curl_exec($ch);
// turn the string into an object that PHP can work with
$results = json_decode($results_json);
// loop through the filenames we got and work with each of them.
foreach ($results as $result) {
    curl_setopt($ch, CURLOPT_URL, 'api.mousephenotype.org/tracker/xml/' . $result->filename);
    $file_contents = json_decode(curl_exec($ch));
    $summary = [];
    if (strpos($result->filename, 'experiment') !== false) { // experiment xml files
        foreach ($file_contents as $file_content) {
            if (!empty($file_content->experimentProcedures)) {
                $has_failed = false;
                foreach ($file_content->experimentProcedures as $experimentProcedure) {
                    if ($experimentProcedure->status === 'failed') {
                        $has_failed = true;
                        break; // exits out of the first enclosing foreach
                    }
                }
                if ($has_failed) {
                    // record the file_content that has failed status
                    $summary['ID'] = $file_content->id;
                    $summary['status'] = $file_content->status;
                    $summary['filename'] = $file_content->filename;
                    $summary['logs'] = $file_content->logs;
                    // record the experimentProcedures
                    foreach ($file_content->experimentProcedures as $experimentProcedure) {
                        $summary['experimentProcedures'][$experimentProcedure->id]['status'] = $experimentProcedure->status;
                        $summary['experimentProcedures'][$experimentProcedure->id]['experimentName'] = $experimentProcedure->experimentName;
                        $summary['experimentProcedures'][$experimentProcedure->id]['specimen'] = $experimentProcedure->specimen;
                        $summary['experimentProcedures'][$experimentProcedure->id]['log'] = $experimentProcedure->logs;
                        if ($experimentProcedure->status === 'failed') { // procedure that has failed status
                            $summary['experimentProcedures'][$experimentProcedure->id]['status'] = $experimentProcedure->status;
                            $summary['experimentProcedures'][$experimentProcedure->id]['experimentName'] = $experimentProcedure->experimentName;
                            $summary['experimentProcedures'][$experimentProcedure->id]['specimen'] = $experimentProcedure->specimen;
                            $summary['experimentProcedures'][$experimentProcedure->id]['log'] = $experimentProcedure->logs;
                        }

                    }
                    print json_encode($summary, JSON_PRETTY_PRINT);
                }
            }
        }
    } else { // specimen xml files
        foreach ($file_contents as $file_content) {
            // record the file_content that has failed status
            if ($file_content->status === 'failed') {
                $summary['ID'] = $file_content->id;
                $summary['status'] = $file_content->status;
                $summary['filename'] = $file_content->filename;
                $summary['logs'] = $file_content->logs;
                print json_encode($summary, JSON_PRETTY_PRINT);
            }
        }
    }
}
curl_close($ch);
?>
