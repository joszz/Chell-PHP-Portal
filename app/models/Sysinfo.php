<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to Sysinfo.
 *
 * @package Models
 * @suppress PHP2414
 */
class Sysinfo extends BaseModel
{
    /**
     * Calls the various methods in this class and returns an array with all the data.
     *
     * @return array    Various system statistics in an array.
     */
    public function getData()
    {
        return [
            'uptime' => $this->getUptime(),
            'hostname' => $this->getHostname(),
            'linux_kernel_version' => $this->getLinuxAndKernelVersion(),
            'memoryinfo' => $this->getMemoryInfo()
        ];
    }

    /**
     * Retrieves the current uptime from /proc/uptime (or /prochost/uptime in Docker).
     *
     * @return array<string>    An array with uptime and idletime.
     */
    private function getUptime()
    {
        $uptime = explode(' ', $this->getProcContents('uptime'));
        return [
            'uptime'    => $uptime[0],
            'idle'      => trim($uptime[1])
        ];
    }

    /**
     * Retrieves the hostname from /proc/sys/kernel/hostname (or /prochost/sys/kernel/hostname in Docker).
     *
     * @return string   The hostname
     */
    private function getHostname()
    {
        return trim($this->getProcContents('sys/kernel/hostname'));
    }

    /**
     * Retrieves the Linux and kernel version from /proc/version (or /prochost/version in Docker).
     *
     * @return string   The Linux and kernel version
     */
    private function getLinuxAndKernelVersion()
    {
        $version = $this->getProcContents('version');
        $regex =
            '/Linux version (\S+) ' .               /* group 1: "3.0.31-g6fb96c9" */
            '\((\S+?)\) ' .                         /* group 2: "x@y.com" (kernel builder) */
            '\((gcc.+)\) ' .                        /* group 3: GCC version information */
            '(#\d+) ' .                             /* group 4: "#1" */
            '(.*) ' .                               /* group 5: optional SMP, PREEMPT, and any CONFIG_FLAGS */
            '((Sun|Mon|Tue|Wed|Thu|Fri|Sat).+)/';   /* group 6: "Thu Jun 28 11:02:39 PDT 2022" */
        preg_match($regex, $version, $matches);
        return $matches[1];

    }

    /**
     * Retrieves the current memory statistics from /proc/meminfo (or /prochost/meminfo in Docker).
     *
     * @return array    An array with memory information.
     */
    private function getMemoryInfo()
    {
        $meminfo = explode(PHP_EOL, $this->getProcContents('meminfo'));
        $meminfoParsed = [];
        foreach ($meminfo as $row)
        {
            if ($row)
            {
                $explodedRow = explode(':', $row);
                $meminfoParsed[$explodedRow[0]] = trim(str_replace(' kB', '',$explodedRow[1]));
            }
        }

        $meminfoParsed['percentused'] = round((1 - $meminfoParsed['MemAvailable'] / $meminfoParsed['MemTotal']) * 100, 2);
        return $meminfoParsed;
    }

    /**
     * Retrieves the contents of the given $file from /proc/ (or /prochost/ in Docker).
     * 
     * @param string $file      The file in /proc to retrieve.
     * @return bool|string      The contents of the file in /proc.
     */
    private function getProcContents(string $file)
    {
        return is_dir('/prochost/') ? file_get_contents('/prochost/' . $file) : file_get_contents('/proc/' . $file);
    }
}