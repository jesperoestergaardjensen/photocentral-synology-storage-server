<?php

namespace PhotoCentralSynologyStorageServer\Model;

class FileSystemDiffReportList
{
    /**
     * @var FileSystemDiffReport[]
     */
    private array $complete_result_map = [];

    public function add(FileSystemDiffReport $collection_folder_diff_result, string $synology_photo_collection_id)
    {
        $this->complete_result_map[$synology_photo_collection_id] = $collection_folder_diff_result;
    }

    public function get(string $synology_photo_collection_id): FileSystemDiffReport
    {
        return $this->complete_result_map[$synology_photo_collection_id];
    }
}
