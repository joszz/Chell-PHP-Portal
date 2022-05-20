<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to PHPSysinfo.
 *
 * @package Models
 * @suppress PHP2414
 */
class Cpu extends BaseModel
{
    public function getCurrentCpuUsage()
    {
        $load = null;

        if (stristr(PHP_OS, 'win'))
        {
            $cmd = 'wmic cpu get loadpercentage /all';
            @exec($cmd, $output);

            if ($output)
            {
                foreach ($output as $line)
                {
                    if ($line && preg_match('/^[0-9]+\$/', $line))
                    {
                        $load = $line;
                        break;
                    }
                }
            }
        }
        else
        {
            if (is_readable('/proc/stat'))
            {
                // Collect 2 samples - each with 1 second period
                // See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
                $statData1 = $this->getServerLoadLinuxData();
                sleep(1);
                $statData2 = $this->getServerLoadLinuxData();

                if(!is_null($statData1) && !is_null($statData2))
                {
                    // Get difference
                    $statData2[0] -= $statData1[0];
                    $statData2[1] -= $statData1[1];
                    $statData2[2] -= $statData1[2];
                    $statData2[3] -= $statData1[3];

                    // Sum up the 4 values for User, Nice, System and Idle and calculate
                    // the percentage of idle time (which is part of the 4 values!)
                    $cpuTime = $statData2[0] + $statData2[1] + $statData2[2] + $statData2[3];

                    // Invert percentage to get CPU time, not idle time
                    $load = 100 - ($statData2[3] * 100 / $cpuTime);
                }
            }
        }

        return $load;
    }

    private function getServerLoadLinuxData()
    {
        if (is_readable('/proc/stat'))
        {
            $stats = @file_get_contents('/proc/stat');

            if ($stats !== false)
            {
                // Remove double spaces to make it easier to extract values with explode()
                $stats = preg_replace('/[[:blank:]]+/', ' ', $stats);

                // Separate lines
                $stats = str_replace(array("\r\n", "\n\r", "\r"), "\n", $stats);
                $stats = explode("\n", $stats);

                // Separate values and find line for main CPU load
                foreach ($stats as $statLine)
                {
                    $statLineData = explode(' ', trim($statLine));

                    // Found!
                    if (count($statLineData) >= 5 && array_shift($statLineData) == 'cpu')
                    {
                        return $statLineData;
                    }
                }
            }
        }

        return null;
    }
}