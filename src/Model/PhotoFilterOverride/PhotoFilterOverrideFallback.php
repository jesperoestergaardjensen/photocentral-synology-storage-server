<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride;

class PhotoFilterOverrideFallback implements PhotoFilterOverride
{
    public function getSql(): string
    {
        return '';
    }
}