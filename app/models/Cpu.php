<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to CPU.
 *
 * @package Models
 * @suppress PHP2414
 */
class Cpu extends BaseModel
{
    /**
     * Retrieves the CPU usage for either Windows or Linux.
     *
     * @return mixed    The CPU usage in percent.
     */
    public function getCurrentCpuUsage()
    {
        if (stristr(PHP_OS, 'win'))
        {
            return $this->getCpuUsageWindows();
        }
        else if (is_readable('/prochost/stat'))
        {
            return $this->getCpuUsageLinux(fn() => @file_get_contents('/prochost/stat'));
        }
        else if (is_readable('/proc/stat'))
        {
            return $this->getCpuUsageLinux(fn() => @file_get_contents('/proc/stat'));
        }

        return false;
    }

    /**
     * Retrieves the CPU usage in percentage for Linux.
     *
     * @param mixed $getProcStatFunction    A function to retrieve the output of /proc/stat by.
     * @return bool|float                   A CPU usage percentage as float (0-100) or false if failed.
     */
    public static function getCpuUsageLinux($getProcStatFunction)
    {
        // Collect 2 samples - each with 1 second period
        // See: https://de.wikipedia.org/wiki/Load#Der_Load_Average_auf_Unix-Systemen
        $statData1 = self::getServerLoadLinuxData($getProcStatFunction());
        sleep(1);
        $statData2 = self::getServerLoadLinuxData($getProcStatFunction());

        if (!is_null($statData1) && !is_null($statData2))
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
            return round(100 - ($statData2[3] * 100 / $cpuTime), 2);
        }

        return false;
    }

    /**
     * Retrieves the CPU usage in percentage for Windows.
     *
     * @return string|bool   Either a string of usage percentage, or false if failed.
     */
    private function getCpuUsageWindows()
    {
        $cmd = 'wmic cpu get loadpercentage /all';
        @exec($cmd, $output);

        if ($output)
        {
            foreach ($output as $line)
            {
                if ($line && preg_match('/^[0-9]+\$/', $line))
                {
                    return $line;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves the CPU load for Linux.
     *
     * @return array|bool   Either an array of load, or false if failed.
     */
    private static function getServerLoadLinuxData(string $stats)
    {
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

        return false;
    }
}