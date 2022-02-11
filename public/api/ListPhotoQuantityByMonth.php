<?php

use PhotoCentralSynologyStorageServer\Controller\ListPhotoQuantityByMonthController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(ListPhotoQuantityByMonthController::class);
