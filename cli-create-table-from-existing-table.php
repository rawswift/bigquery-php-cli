<?php
/**
 * Save query result to a new table
 * Written by Ryan Yonzon <hello@ryanyonzon.com>
 */

set_include_path("vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path());

require_once 'Google/Client.php';
require_once 'Google/Service/Bigquery.php';

// configuration stuff
$config = require_once 'config/Credentials.php';

$project_id = 'PROJECT-ID-HERE';
$dataset_id = 'DATASET-ID-HERE';

$target_table_id = 'TARGET-TABLE-ID-HERE';
$destination_table_id = 'DESTINATION-TABLE-ID-HERE';

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
$sql_query = "SELECT email FROM $dataset_id.$target_table_id";

// dataset reference
$dataset_reference = new Google_Service_Bigquery_DatasetReference();
$dataset_reference->setDatasetId($dataset_id);
$dataset_reference->setProjectId($project_id);

// table reference
$table_reference = new Google_Service_Bigquery_TableReference();
$table_reference->setDatasetId($dataset_id);
$table_reference->setProjectId($project_id);
// set destination table
$table_reference->setTableId($destination_table_id);

// job configuration query service
$config_query = new Google_Service_Bigquery_JobConfigurationQuery();
$config_query->setDefaultDataset($dataset_reference);
$config_query->setDestinationTable($table_reference);
$config_query->setQuery($sql_query);

// job configuration
$job_config = new Google_Service_Bigquery_JobConfiguration();
$job_config->setQuery($config_query);

// job service
$job = new Google_Service_Bigquery_Job();
$job->setConfiguration($job_config);

// save query result to destination table, using insert call
$response = $service->jobs->insert($project_id, $job);

print_r($response);
