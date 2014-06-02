<?php
/**
 * Query data from BigQuery (with friendly result)
 * Written by Ryan Yonzon <hello@ryanyonzon.com>
 */

set_include_path("vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path());

require_once 'Google/Client.php';
require_once 'Google/Service/Bigquery.php';

// configuration stuff
$config = require_once 'config/Credentials.php';

$project_id = 'PROJECT-ID-HERE';
$dataset_id = 'DATASET-ID-HERE';
$table_id = 'TABLE-ID-HERE';

// create Google Client
$client = new Google_Client();
$client->setApplicationName("BigQuery CLI");

$key = file_get_contents('key/' . $config->key_filename);

$client->setAssertionCredentials(new Google_Auth_AssertionCredentials(
    $config->service_account_name,
    array(
        'https://www.googleapis.com/auth/bigquery'
        ),
        $key
    )
);
$client->setClientId($config->client_id);

// create BigQuery service
$service = new Google_Service_Bigquery($client);

// construct SQL query statement
$sql = "SELECT email, firstname, lastname FROM $dataset_id.$table_id";

// query request service
$query = new Google_Service_Bigquery_QueryRequest();
$query->setQuery($sql);

$response = $service->jobs->query($project_id, $query);

/**
 * format to a much friendlier result
 */

$columns = $rows = array();

// get columns...
$fields = $response->getSchema()->getFields();
foreach ($fields as $field) {
    $columns[] = $field->getName();
}

// ...then get rows
foreach ($response->getRows() as $row) {
    $index = 0; // reset value
    $data = array(); // reset value
    foreach ($row->getF() as $k => $v) {
        $data[$columns[$index]] = $v->getV();
        $index++;
    }
    $rows[] = $data;
}

print_r($rows);
