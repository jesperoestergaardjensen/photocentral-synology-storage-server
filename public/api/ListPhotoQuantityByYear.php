<?php

use PhotoCentralSynologyStorageServer\Controller\ListPhotoQuantityByYearController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(ListPhotoQuantityByYearController::class);
