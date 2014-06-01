<?php
/**
 * Create BigQuery dataset
 * Written by Ryan Yonzon <hello@ryanyonzon.com>
 */

set_include_path("vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path());

require_once 'Google/Client.php';
require_once 'Google/Service/Bigquery.php';

// configuration stuff
$config = require_once 'config/Credentials.php';

$project_id = 'PROJECT-ID-HERE';
$dataset_id = 'DATASET-ID-HERE';

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
 * dataset related stuff
 * https://developers.google.com/bigquery/docs/reference/v2/datasets
 */

// reference
$dataset_reference = new Google_Service_Bigquery_DatasetReference();
$dataset_reference->setProjectId($project_id);
$dataset_reference->setDatasetId($dataset_id);

// dataset
$dataset = new Google_Service_Bigquery_Dataset();
$dataset->setDatasetReference($dataset_reference);
$dataset->setDescription('Sample Dataset');

// create dataset
$options = array();
$response = $service->datasets->insert($project_id, $dataset, $options);

print_r($response);
