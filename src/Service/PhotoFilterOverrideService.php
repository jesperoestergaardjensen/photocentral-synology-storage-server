<?php

namespace PhotoCentralSynologyStorageServer\Service;

use PhotoCentralStorage\Model\PhotoFilter\PhotoCollectionIdFilter;
use PhotoCentralStorage\Model\PhotoFilter\PhotoDateTimeRangeFilter;
use PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride\PhotoCollectionIdFilterOverride;
use PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride\PhotoDateTimeRangeFilterOverride;
use PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride\PhotoFilterOverrideFallback;

class PhotoFilterOverrideService
{
    public static function map(string $from_class_name): string {
        switch ($from_class_name) {
            case PhotoCollectionIdFilter::class :
                $to_class_name = PhotoCollectionIdFilterOverride::class;
            break;
            case PhotoDateTimeRangeFilter::class :
                $to_class_name = PhotoDateTimeRangeFilterOverride::class;
            break;

            default:
                $to_class_name = PhotoFilterOverrideFallback::class;
        }

        return $to_class_name;
    }
}
