<?php

namespace PhotoCentralSynologyStorageServer\Model;

class PhotoImportResult
{
    /**
     * @var PhotoCollectionFolderDiffResult[]
     */
    private array $complete_result_map = [];

    public function add(PhotoCollectionFolderDiffResult $collection_folder_diff_result, string $synology_photo_collection_id)
    {
        $this->complete_result_map[$synology_photo_collection_id] = $collection_folder_diff_result;
    }

    public function getCompleteResultMap(): array
    {
        return $this->complete_result_map;
    }

    public function getPhotoCollectionFolderDiffResult(string $synology_photo_collection_id): PhotoCollectionFolderDiffResult
    {
        return $this->complete_result_map[$synology_photo_collection_id];
    }
}
