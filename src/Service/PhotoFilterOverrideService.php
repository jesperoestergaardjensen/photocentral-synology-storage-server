<?php

namespace PhotoCentralSynologyStorageServer\Service;

use PhotoCentralStorage\Exception\PhotoCentralStorageException;
use PhotoCentralStorage\Model\PhotoFilter\PhotoCollectionIdFilter;
use PhotoCentralStorage\Model\PhotoFilter\PhotoDateTimeRangeFilter;
use PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride\PhotoCollectionIdFilterOverride;
use PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride\PhotoDateTimeRangeFilterOverride;

class PhotoFilterOverrideService
{
    /**
     * @throws PhotoCentralStorageException
     */
    public static function map(string $from_class_name): string {
        switch ($from_class_name) {

            case PhotoCollectionIdFilter::class :
                $to_class_name = PhotoCollectionIdFilterOverride::class;
            break;

            case PhotoDateTimeRangeFilter::class :
                $to_class_name = PhotoDateTimeRangeFilterOverride::class;
            break;

            default:
                throw new PhotoCentralStorageException("Unknown photo filter $from_class_name used");
        }

        return $to_class_name;
    }
}
