<?php

namespace PhotoCentralSynologyStorageServer\Service;

use PhotoCentralStorage\Exception\PhotoCentralStorageException;
use PhotoCentralStorage\Model\PhotoSorting\SortByAddedTimestamp;
use PhotoCentralStorage\Model\PhotoSorting\SortByPhotoDateTime;
use PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride\SortByAddedTimestampOverride;
use PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride\SortByPhotoDateTimeOverride;

class PhotoSortingOverrideService
{
    public static function map(string $from_class_name): string {
        switch ($from_class_name) {
            case SortByAddedTimestamp::class :
                $to_class_name = SortByAddedTimestampOverride::class;
            break;

            case SortByPhotoDateTime::class :
                $to_class_name = SortByPhotoDateTimeOverride::class;
            break;

            default:
                throw new PhotoCentralStorageException("Unknown photo filter $from_class_name used");        }

        return $to_class_name;
    }
}
