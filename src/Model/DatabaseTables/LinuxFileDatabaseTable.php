<?php

namespace PhotoCentralSynologyStorageServer\Model\DatabaseTables;

class LinuxFileDatabaseTable
{
    public const NAME = 'LinuxFile';

    public const ROW_FILE_UUID                    = 'file_uuid';
    public const ROW_SYNOLOGY_PHOTO_COLLECTION_ID = 'synology_photo_collection_id';
    public const ROW_INODE_INDEX                  = 'inode_index';
    public const ROW_LAST_MODIFIED_DATE           = 'last_modified_date';
    public const ROW_FILE_NAME                    = 'file_name';
    public const ROW_FILE_PATH                    = 'file_path';
    public const ROW_IMPORTED                     = 'imported';
    public const ROW_IMPORT_DATE_TIME             = 'import_date_time';
    public const ROW_ROW_ADDED_DATA_TIME          = 'row_added_date_time';
    public const ROW_PHOTO_UUID                   = 'photo_uuid';
    public const ROW_DUPLCIATE                    = 'duplicate';
    public const ROW_SKIPPED                      = 'skipped';
    public const ROW_SKIPPED_ERROR                = 'skipped_error';
    public const ROW_SCHEDULED_FOR_DELETION       = 'scheduled_for_deletion';
}