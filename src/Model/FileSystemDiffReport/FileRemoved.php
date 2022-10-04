<?php

namespace PhotoCentralSynologyStorageServer\Model\FileSystemDiffReport;

use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReportLine;

class FileRemoved
{
    private FileSystemDiffReportLine $removed_from;

    public function __construct(FileSystemDiffReportLine $removed_from)
    {
        $this->removed_from = $removed_from;
    }

    public function getRemovedFrom(): FileSystemDiffReportLine
    {
        return $this->removed_from;
    }
}
