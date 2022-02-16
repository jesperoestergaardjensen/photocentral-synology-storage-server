<?php

namespace PhotoCentralSynologyStorageServer\Model;

use PhotoCentralSynologyStorageServer\Model\DatabaseTables\LinuxFileDatabaseTable;
use PhotoCentralSynologyStorageServer\Service\UUIDService;

class LinuxFile
{
    private bool $imported = false;
    private ?int $import_date = null;
    private string $file_name;
    private string $file_uuid;
    private string $synology_photo_collection_id;
    private int $inode_index;
    private int $last_modified_date;
    private int $row_added_date_time;
    private ?string $photo_uuid;
    private string $file_path;
    private ?string $skipped_error = null;
    private bool $scheduled_for_deletion = false;

    public function __construct(
        string $synology_photo_collection_id,
        int $inode_index,
        int $last_modified_date, // TODO : Rename to $last_modified_date_time
        string $file_name,
        string $file_path,
        string $photo_uuid = null,
        string $file_uuid = null
    ) {
        $this->synology_photo_collection_id = $synology_photo_collection_id;
        $this->inode_index = $inode_index;
        $this->last_modified_date = $last_modified_date;
        $this->file_name = $file_name;
        $this->file_path = $file_path;
        $this->photo_uuid = $photo_uuid;
        $this->file_uuid = $file_uuid ?? UUIDService::create();
    }

    public function getFileName(): string
    {
        return $this->file_name;
    }

    public function getSynologyPhotoCollectionId(): string
    {
        return $this->synology_photo_collection_id;
    }

    public function getInodeIndex(): int
    {
        return $this->inode_index;
    }

    public function getLastModifiedDate(): int
    {
        return $this->last_modified_date;
    }

    public function getRowAddedDateTime(): int
    {
        return $this->row_added_date_time;
    }

    public function setRowAddedDateTime(int $row_added_date_time): void
    {
        $this->row_added_date_time = $row_added_date_time;
    }

    public function setImported(bool $imported): void
    {
        $this->imported = $imported;
    }

    public function setImportDate(?int $import_date): void
    {
        $this->import_date = $import_date;
    }

    public function setPhotoUuid(string $photo_uuid): void
    {
        $this->photo_uuid = $photo_uuid;
    }

    public function getFilePath(): string
    {
        return $this->file_path;
    }

    public function getPhotoUuid(): ?string
    {
        return $this->photo_uuid;
    }

    public function setSkippedError(?string $skipped_error): void
    {
        $this->skipped_error = $skipped_error;
    }

    /**
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->imported;
    }

    /**
     * @return int
     */
    public function getImportDate(): ?int
    {
        return $this->import_date ?? null;
    }

    /**
     * @return string
     */
    public function getFileUuid(): string
    {
        return $this->file_uuid;
    }

    public function getFullFileNameAndPath(string $base_path): string
    {
        return $base_path . $this->file_path . $this->file_name;
    }

    public static function fromArray($array): self
    {
        $self = new self(
            $array[LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID],
            $array[LinuxFileDatabaseTable::ROW_INODE_INDEX],
            $array[LinuxFileDatabaseTable::ROW_LAST_MODIFIED_DATE],
            $array[LinuxFileDatabaseTable::ROW_FILE_NAME],
            $array[LinuxFileDatabaseTable::ROW_FILE_PATH],
            $array[LinuxFileDatabaseTable::ROW_PHOTO_UUID],
            $array[LinuxFileDatabaseTable::ROW_FILE_UUID]
        );

        $self->setImportDate($array[LinuxFileDatabaseTable::ROW_IMPORT_DATE_TIME]);
        $self->setImported($array[LinuxFileDatabaseTable::ROW_IMPORTED]);
        $self->setRowAddedDateTime($array[LinuxFileDatabaseTable::ROW_ROW_ADDED_DATA_TIME]);
        $self->setSkippedError($array[LinuxFileDatabaseTable::ROW_SKIPPED_ERROR]);
        $self->setSceduledForDeletion($array[LinuxFileDatabaseTable::ROW_SCHEDULED_FOR_DELETION]);

        return $self;
    }

    public function setSceduledForDeletion(bool $scheduled_for_deletion): void
    {
        $this->scheduled_for_deletion = $scheduled_for_deletion;
    }

    public function toArray(): array
    {
        return [
            LinuxFileDatabaseTable::ROW_FILE_UUID                    => $this->file_uuid,
            LinuxFileDatabaseTable::ROW_SYNOLOGY_PHOTO_COLLECTION_ID => $this->synology_photo_collection_id,
            LinuxFileDatabaseTable::ROW_INODE_INDEX                  => $this->inode_index,
            LinuxFileDatabaseTable::ROW_LAST_MODIFIED_DATE           => $this->last_modified_date,
            LinuxFileDatabaseTable::ROW_FILE_NAME                    => $this->file_name,
            LinuxFileDatabaseTable::ROW_FILE_PATH                    => $this->file_path,
            LinuxFileDatabaseTable::ROW_IMPORTED                     => $this->imported,
            LinuxFileDatabaseTable::ROW_IMPORT_DATE_TIME             => $this->import_date,
            LinuxFileDatabaseTable::ROW_ROW_ADDED_DATA_TIME          => $this->row_added_date_time,
            LinuxFileDatabaseTable::ROW_PHOTO_UUID                   => $this->photo_uuid,
            LinuxFileDatabaseTable::ROW_SKIPPED_ERROR                => $this->skipped_error,
            LinuxFileDatabaseTable::ROW_SCHEDULED_FOR_DELETION       => $this->scheduled_for_deletion,
        ];
    }
}
