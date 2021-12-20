<?php
namespace PhotoCentralSynologyStorageServer\config;

use PhotoCentralSynologyStorageServer\Model\DatabaseConnection\SimpleDatabaseConnection;
use PhotoCentralSynologyStorageServer\Provider;

const PHOTOCENTRAL_INSTALLATIONS_UUID = '20c8a0ba-40c5-49f5-81df-696fed72d21c';

$database_connection = new SimpleDatabaseConnection('localhost', 'photocentral', 'iD9Q!Cyqgp^A8r', 'photocentral_storage');

$provider = new Provider($database_connection);
$provider->initialize();
// include_once('SynologyNasMariaDBConf.php');
