<?php

use PhotoCentralSynologyStorageServer\Controller\SearchController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(SearchController::class);
