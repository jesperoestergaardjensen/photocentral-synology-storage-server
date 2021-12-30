<?php

use PhotoCentralSynologyStorageServer\Controller\DisplayPhotoController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(DisplayPhotoController::class);
