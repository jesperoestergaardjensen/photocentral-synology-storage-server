<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoSortingOverride;

use PhotoCentralStorage\Model\PhotoSorting\SortByCreatedTimestamp;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;

class SortByCreatedTimestampOverride extends SortByCreatedTimestamp implements PhotoSortingOverride
{
    public function getSql(): string
    {
        return PhotoDatabaseTable::ROW_PHOTO_DATE_TIME . " " . $this->getDirection();
    }
}
