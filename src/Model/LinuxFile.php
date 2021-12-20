<?php

namespace PhotoCentralSynologyStorageServer\Model;

use PhotoCentralSynologyStorageServer\Service\UUIDService;

class LinuxFile
{
    public const DB_ROW_FILE_UUID              = 'file_uuid';
    public const DB_ROW_PHOTO_SOURCE_UUID      = 'photo_source_uuid';
    public const DB_ROW_INODE_INDEX            = 'inode_index';
    public const DB_ROW_LAST_MODIFIED_DATE     = 'last_modified_date';
    public const DB_ROW_FILE_NAME              = 'file_name';
    public const DB_ROW_FILE_PATH              = 'file_path';
    public const DB_ROW_IMPORTED               = 'imported';
    public const DB_ROW_IMPORT_DATE_TIME       = 'import_date_time';
    public const DB_ROW_ROW_ADDED_DATA_TIME    = 'row_added_date_time';
    public const DB_ROW_PHOTO_UUID             = 'photo_uuid';
    public const DB_ROW_SKIPPED_ERROR          = 'skipped_error';
    public const DB_ROW_SCHEDULED_FOR_DELETION = 'scheduled_for_deletion';

    private bool $imported = false;
    private ?int $import_date;
    private string $file_name;
    private string $file_uuid;
    private string $photo_source_uuid;
    private int $inode_index;
    private int $last_modified_date;
    private int $row_added_date_time;
    private ?string $photo_uuid;
    private string $file_path;
    private ?string $skipped_error;
    private string $scheduled_for_deletion;

    public function __construct(
        string $photo_source_uuid,
        int $inode_index,
        int $last_modified_date,
        string $file_name,
        string $file_path,
        string $photo_uuid = null,
        string $file_uuid = null
    ) {
        $this->photo_source_uuid = $photo_source_uuid;
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

    public function getPhotoSourceUUID(): string
    {
        return $this->photo_source_uuid;
    }

    public function getInodeIndex(): int
    {
        return $this->inode_index;
    }

    public function getLastModifiedDate(): int
    {
        return $this->last_modified_date;
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

    public function getStorageType(): string
    {
        return LinuxFileSystem::class;
    }

    /**
     * @return string
     */
    public function getFileUuid(): string
    {
        return $this->file_uuid;
    }

    public static function fromArray($array): self
    {
        $self = new self(
            $array[self::DB_ROW_PHOTO_SOURCE_UUID],
            $array[self::DB_ROW_INODE_INDEX],
            $array[self::DB_ROW_LAST_MODIFIED_DATE],
            $array[self::DB_ROW_FILE_NAME],
            $array[self::DB_ROW_FILE_PATH],
            $array[self::DB_ROW_PHOTO_UUID],
            $array[self::DB_ROW_FILE_UUID]
        );

        $self->setImportDate($array[self::DB_ROW_IMPORT_DATE_TIME]);
        $self->setImported($array[self::DB_ROW_IMPORTED]);
        $self->setRowAddedDateTime($array[self::DB_ROW_ROW_ADDED_DATA_TIME]);
        $self->setSkippedError($array[self::DB_ROW_SKIPPED_ERROR]);
        $self->setSceduledForDeletion($array[self::DB_ROW_SCHEDULED_FOR_DELETION]);

        return $self;
    }

    public function setSceduledForDeletion(bool $scheduled_for_deletion): void
    {
        $this->scheduled_for_deletion = $scheduled_for_deletion;
    }

    public function toArray(): array
    {
        return [
            self::DB_ROW_FILE_UUID              => $this->file_uuid,
            self::DB_ROW_PHOTO_SOURCE_UUID      => $this->photo_source_uuid,
            self::DB_ROW_INODE_INDEX            => $this->inode_index,
            self::DB_ROW_LAST_MODIFIED_DATE     => $this->last_modified_date,
            self::DB_ROW_FILE_NAME              => $this->file_name,
            self::DB_ROW_FILE_PATH              => $this->file_path,
            self::DB_ROW_IMPORTED               => $this->imported,
            self::DB_ROW_IMPORT_DATE_TIME       => $this->import_date,
            self::DB_ROW_ROW_ADDED_DATA_TIME    => $this->row_added_date_time,
            self::DB_ROW_PHOTO_UUID             => $this->photo_uuid,
            self::DB_ROW_SKIPPED_ERROR          => $this->skipped_error,
            self::DB_ROW_SCHEDULED_FOR_DELETION => $this->scheduled_for_deletion,
            'storage_type'                      => $this->getStorageType(),
        ];
    }
}