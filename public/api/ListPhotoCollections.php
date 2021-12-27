<?php

use PhotoCentralSynologyStorageServer\Controller\ListPhotoCollectionsController;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(ListPhotoCollectionsController::class);
