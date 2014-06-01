<?php
/**
 * Create BigQuery table
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
 * table related stuff
 * https://developers.google.com/bigquery/docs/reference/v2/tables
 */

// reference
$table_reference = new Google_Service_Bigquery_TableReference();
$table_reference->setDatasetId($dataset_id);
$table_reference->setProjectId($project_id);
$table_reference->setTableId($table_id);

// table schema service
$table_schema = new Google_Service_Bigquery_TableSchema();

// construct table schema
/**
 * name => String - Column name
 * type => String, Integer, Boolean, Timestamp or Record (field contains a nested schema)
 * mode => Nullable, Required or Repeated (Optional)
 * description => String - Column description
 */
$schema = array(
                array(
                    'name' => 'email',
                    'type' => 'string',
                    'mode' => 'required',
                    // 'description' => 'Sample description' // optional
                ),
                array(
                    'name' => 'firstname',
                    'type' => 'string',
                    'mode' => 'required',
                    // 'description' => 'Sample description' // optional
                ),
                array(
                    'name' => 'lastname',
                    'type' => 'string',
                    'mode' => 'required',
                    // 'description' => 'Sample description' // optional
                )
    );

$table_schema->setFields($schema);

$table = new Google_Service_Bigquery_Table();
$table->setDescription('Sample table');
$table->setTableReference($table_reference);
$table->setSchema($table_schema);

// create the table with schema
$options = array();
$response = $service->tables->insert($project_id, $dataset_id, $table, $options);

print_r($response);
