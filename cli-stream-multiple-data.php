<?php
/**
 * Insert multiple row of data
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

/**
 * references:
 *      https://developers.google.com/bigquery/docs/reference/v2/tabledata/insertAll
 *      http://stackoverflow.com/questions/21795683/bigquery-php-insertall-error-no-records-present-in-table-data-append-request
 */
$data = array(
            array(
                'email' => 'johnsmith@gmail.com',
                'firstname' => 'John',
                'lastname' => 'Smith'
            ),
            array(
                'email' => 'spidey@gmail.com',
                'firstname' => 'Peter',
                'lastname' => 'Parker'
            ),
            array(
                'email' => 'tincan@gmail.com',
                'firstname' => 'Tony',
                'lastname' => 'Stark'
            )
);

$rows = array();
foreach ($data as $k => $v) {
    $row = new Google_Service_Bigquery_TableDataInsertAllRequestRows();
    $row->setJson($v);
    // $row->setInsertId( strtotime('now') );
    $rows[] = $row;
}

$request = new Google_Service_Bigquery_TableDataInsertAllRequest();
$request->setKind('bigquery#tableDataInsertAllRequest');
$request->setRows($rows);

// stream data (multiple rows)
$options = array();
$response = $service->tabledata->insertAll($project_id, $dataset_id, $table_id, $request, $options);

print_r($response);
