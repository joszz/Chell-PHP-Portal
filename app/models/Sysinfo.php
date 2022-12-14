<?php

namespace Chell\Models;

/**
 * @package Models
 * @suppress PHP2414
 */
class Sysinfo extends BaseModel
{
    public function getData()
    {
        return [
            'uptime' => $this->getUptime(),
            'hostname' => $this->getHostname(),
            'linux_kernel_version' => $this->getLinuxAndKernelVersion(),
            'memoryinfo' => $this->getMemoryInfo()
        ];
    }

    private function getUptime()
    {
        $uptime = explode(' ', file_get_contents('/prochost/uptime'));
        return [
            'uptime'    => $uptime[0],
            'idle'      => trim($uptime[1])
        ];
    }

    private function getHostname()
    {
        return trim(file_get_contents('/prochost/sys/kernel/hostname'));
    }

    private function getLinuxAndKernelVersion()
    {
        $version = file_get_contents('/prochost/version');
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

    private function getMemoryInfo()
    {
        $meminfo = explode(PHP_EOL, file_get_contents('/prochost/meminfo'));
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
}