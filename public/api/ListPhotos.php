<?php

use PhotoCentralSynologyStorageServer\Controller\ListPhotosController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(ListPhotosController::class);
