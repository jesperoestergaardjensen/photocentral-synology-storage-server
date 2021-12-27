<?php

use PhotoCentralSynologyStorageServer\Provider;

include_once(dirname(__FILE__,2) . '/config/config.php');

/** @var Provider $provider */
$provider->importPhotos();
