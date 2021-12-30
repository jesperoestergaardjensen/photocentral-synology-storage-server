<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride;

use PhotoCentralStorage\Model\PhotoSorting\PhotoSorting;

class PhotoSortingOverrideFallback implements PhotoSortingOverride, PhotoSorting
{
    public function getSql(): string
    {
        return '';
    }

    public function toArray(): array
    {
        return[];
    }

    public static function fromArray($array, $return_class_override = self::class): PhotoSorting
    {
        return new self;
    }
}