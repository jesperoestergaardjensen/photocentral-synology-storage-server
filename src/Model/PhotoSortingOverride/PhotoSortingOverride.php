<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride;

interface PhotoSortingOverride
{
    public function getSql(): string;
}