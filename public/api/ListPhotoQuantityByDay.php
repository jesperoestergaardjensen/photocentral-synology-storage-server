<?php

use PhotoCentralSynologyStorageServer\Controller\ListPhotoQuantityByDayController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(ListPhotoQuantityByDayController::class);
