<?php

namespace PhotoCentralSynologyStorageServer\Model\FileSystemDiffReport;

use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReportLine;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;

class FileAdded
{
    private FileSystemDiffReportLine $added_to;

    private ?LinuxFile $linux_file;

    public function __construct(LinuxFile $linux_file, FileSystemDiffReportLine $added_to)
    {
        $this->linux_file = $linux_file;
        $this->added_to = $added_to;
    }

    public function getAddedTo(): FileSystemDiffReportLine
    {
        return $this->added_to;
    }

    /**
     * @return LinuxFile|null
     */
    public function getLinuxFile(): ?LinuxFile
    {
        return $this->linux_file;
    }
}