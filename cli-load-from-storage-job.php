<?php
/**
 * Create a job and load data from Google Cloud Storage
 * Written by Ryan Yonzon <hello@ryanyonzon.com>
 */

set_include_path("vendor/google/apiclient/src/" . PATH_SEPARATOR . get_include_path());

require_once 'Google/Client.php';
require_once 'Google/Service/Bigquery.php';

// configuration stuff
$config = require_once 'config/Credentials.php';

$project_id = 'PROJECT-ID-HERE';
$dataset_id = 'DATASET-ID-HERE';
$destination_table_id = 'DESTINATION-TABLE-ID-HERE';

// create Google Client
$client = new Google_Client();
$client->setApplicationName("BigQuery CLI");

$key = file_get_contents('key/' . $config->key_filename);

$client->setAssertionCredentials(new Google_Auth_AssertionCredentials(
    $config->service_account_name,
    array(
        'https://www.googleapis.com/auth/bigquery',
        'https://www.googleapis.com/auth/devstorage.read_write'        
        ),
        $key
    )
);
$client->setClientId($config->client_id);

// create BigQuery service
$service = new Google_Service_Bigquery($client);

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

// job configuration load
$load = new Google_Service_Bigquery_JobConfigurationLoad();
$load->setDestinationTable($table_reference);
$load->setSourceUris(array(
        'gs://mybucket/test.json.gz'
	));
$load->setSourceFormat('NEWLINE_DELIMITED_JSON');
// fail if error is encountered during loading process
$load->setMaxBadRecords(1);

// job configuration
$job_config = new Google_Service_Bigquery_JobConfiguration();
$job_config->setLoad($load);

// job service
$job = new Google_Service_Bigquery_Job();
$job->setConfiguration($job_config);
$job->setKind('load');

$response = $service->jobs->insert($project_id, $job);

print_r($response);

// optionally you can do an exponential backoff to check job status
/*
    $job_reference = $response->getJobReference();
    $job_id = $job_reference->jobId;

    for ($n = 0; $n < 5; ++$n) {
        $info = $service->jobs->get($project_id, $job_id);
        $status = $info->getStatus();
        if ($status->state == 'DONE') {
        	echo "\nJob Successful. Fin!\n";
            break;
        } else {
        	// apply exponential backoff
        	$delay = (1 << $n) * 1000 + rand(0, 1000);
        	echo "Retrying in " . round($delay, 3) . "\n";
    		usleep($delay);
        }
    }
*/
