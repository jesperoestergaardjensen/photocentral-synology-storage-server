<?php

namespace PhotoCentralSynologyStorageServer\Model\FileSystemDiffReport;

use PhotoCentralSynologyStorageServer\Model\FileSystemDiffReportLine;
use PhotoCentralSynologyStorageServer\Model\LinuxFile;

class FileMoved
{
    private FileSystemDiffReportLine $from;

    private FileSystemDiffReportLine $to;

    private ?LinuxFile $linux_file;

    public function __construct(LinuxFile $linux_file, FileSystemDiffReportLine $from, FileSystemDiffReportLine $to)
    {
        $this->linux_file = $linux_file;
        $this->from = $from;
        $this->to = $to;
    }

    public function getTo(): FileSystemDiffReportLine
    {
        return $this->to;
    }

    public function getFrom(): FileSystemDiffReportLine
    {
        return $this->from;
    }

    public function getLinuxFile(): ?LinuxFile
    {
        return $this->linux_file;
    }
}
