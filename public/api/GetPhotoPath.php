<?php

use PhotoCentralSynologyStorageServer\Controller\GetPhotoPath;
use PhotoCentralSynologyStorageServer\Provider;

include_once('../../config/config.php');

/** @var Provider $provider */
$provider->runController(GetPhotoPath::class);
