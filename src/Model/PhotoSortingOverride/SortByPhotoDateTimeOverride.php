<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride;

use PhotoCentralStorage\Model\PhotoSorting\SortByPhotoDateTime;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;

class SortByPhotoDateTimeOverride extends SortByPhotoDateTime implements PhotoSortingOverride
{
    public function getSql(): string
    {
        return PhotoDatabaseTable::ROW_PHOTO_DATE_TIME . " " . $this->getDirection();
    }
}