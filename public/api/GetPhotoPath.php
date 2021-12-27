<?php

use PhotoCentralSynologyStorageServer\Controller\GetPhotoPathController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(GetPhotoPathController::class);
