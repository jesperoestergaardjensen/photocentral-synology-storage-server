<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride;

interface PhotoFilterOverride
{
    public function getSql(): string;
}