<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride;

use PhotoCentralStorage\Model\PhotoFilter\PhotoFilter;

class PhotoFilterOverrideFallback implements PhotoFilterOverride, PhotoFilter
{
    public function getSql(): string
    {
        return '';
    }

    public function toArray(): array
    {
        return[];
    }

    public static function fromArray($array, $return_class_override = self::class): PhotoFilter
    {
        return new self;
    }

}