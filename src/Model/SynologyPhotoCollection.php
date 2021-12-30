<?php

namespace PhotoCentralSynologyStorageServer\Model;

use JsonSerializable;
use PhotoCentralStorage\PhotoCollection;
use PhotoCentralSynologyStorageServer\Model\DatabaseTables\SynologyPhotoCollectionDatabaseTable;

class SynologyPhotoCollection extends PhotoCollection implements JsonSerializable
{
    private string $image_source_path;
    private string $status_files_path;

    public function __construct(
        string $id,
        string $name,
        bool $enabled,
        ?string $description,
        ?int $last_updated,
        string $image_source_path,
        string $status_files_path
    ) {
        parent::__construct($id, $name, $enabled, $description, $last_updated);
        $this->image_source_path = $image_source_path;
        $this->status_files_path = $status_files_path;
    }

    public function getImageSourcePath(): string
    {
        return $this->image_source_path;
    }

    public function getStatusFilesPath(): string
    {
        return $this->status_files_path;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return [
            SynologyPhotoCollectionDatabaseTable::ROW_ID                => $this->getId(),
            SynologyPhotoCollectionDatabaseTable::ROW_NAME              => $this->getName(),
            SynologyPhotoCollectionDatabaseTable::ROW_DESCRIPTION       => $this->getDescription(),
            SynologyPhotoCollectionDatabaseTable::ROW_ENABLED           => $this->isEnabled(),
            SynologyPhotoCollectionDatabaseTable::ROW_IMAGE_SOURCE_PATH => $this->getImageSourcePath(),
            SynologyPhotoCollectionDatabaseTable::ROW_STATUS_FILES_PATH => $this->getStatusFilesPath(),
            SynologyPhotoCollectionDatabaseTable::ROW_LAST_UPDATED      => $this->getLastUpdated(),
        ];
    }

    public static function fromArray(array $array): self
    {
        return new self(
            $array[SynologyPhotoCollectionDatabaseTable::ROW_ID],
            $array[SynologyPhotoCollectionDatabaseTable::ROW_NAME],
            $array[SynologyPhotoCollectionDatabaseTable::ROW_ENABLED],
            $array[SynologyPhotoCollectionDatabaseTable::ROW_DESCRIPTION],
            $array[SynologyPhotoCollectionDatabaseTable::ROW_LAST_UPDATED],
            $array[SynologyPhotoCollectionDatabaseTable::ROW_IMAGE_SOURCE_PATH],
            $array[SynologyPhotoCollectionDatabaseTable::ROW_STATUS_FILES_PATH]
        );
    }
}
