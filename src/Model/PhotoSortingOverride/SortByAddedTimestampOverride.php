<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride;

use PhotoCentralStorage\Model\PhotoSorting\SortByAddedTimestamp;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;

class SortByAddedTimestampOverride extends SortByAddedTimestamp implements PhotoSortingOverride
{
    public function getSql(): string
    {
        return PhotoDatabaseTable::ROW_PHOTO_ADDED_DATE_TIME . " " . $this->getDirection();
    }
}