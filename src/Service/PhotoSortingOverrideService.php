<?php

namespace PhotoCentralSynologyStorageServer\Service;

use PhotoCentralStorage\Model\PhotoSorting\SortByAddedTimestamp;
use PhotoCentralStorage\Model\PhotoSorting\SortByCreatedTimestamp;
use PhotoCentralStorage\Model\PhotoSorting\SortByPhotoDateTime;
use PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride\PhotoSortingOverrideFallback;
use PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride\SortByAddedTimestampOverride;
use PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride\SortByCreatedTimestampOverride;
use PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride\SortByPhotoDateTimeOverride;

class PhotoSortingOverrideService
{
    public static function map(string $from_class_name): string {
        switch ($from_class_name) {
            case SortByAddedTimestamp::class :
                $to_class_name = SortByAddedTimestampOverride::class;
            break;

            case SortByCreatedTimestamp::class :
                $to_class_name = SortByCreatedTimestampOverride::class;
            break;

            case SortByPhotoDateTime::class :
                $to_class_name = SortByPhotoDateTimeOverride::class;
            break;

            default:
                $to_class_name = PhotoSortingOverrideFallback::class;
        }

        return $to_class_name;
    }
}
