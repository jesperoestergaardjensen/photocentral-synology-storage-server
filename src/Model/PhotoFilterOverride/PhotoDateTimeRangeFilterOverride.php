<?php

namespace PhotoCentralSynologyStorageServer\Model\PhotoFilterOverride;

use PhotoCentralStorage\Model\PhotoFilter\PhotoDateTimeRangeFilter;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\PhotoDatabaseTable;

class PhotoDateTimeRangeFilterOverride extends PhotoDateTimeRangeFilter implements PhotoFilterOverride
{
    public function getSql(): string
    {
        return PhotoDatabaseTable::ROW_PHOTO_DATE_TIME . " BETWEEN({$this->getStartTimestamp()}) AND ({$this->getEndTimestamp()})";
    }
}