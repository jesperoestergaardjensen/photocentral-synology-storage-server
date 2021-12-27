<?php

use PhotoCentralSynologyStorageServer\Controller\GetPhotoController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(GetPhotoController::class);
