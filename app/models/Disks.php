<?php

namespace Chell\Models;

use stdClass;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 * @suppress PHP2414
 */
class Disks extends BaseModel
{
    private $diskspace;

    /**
     * uses lsblk to determine all disks on Linux.
     *
     * @return array    An array with objects representing each mountpoint.
     */
    public function getStats() : array
    {
        $disks = json_decode(shell_exec('lsblk -J -O -b'))->blockdevices;
        $disks = array_filter($disks, fn ($disk) => $disk->type == 'disk');
        $result = [];

        foreach ($disks as $disk)
        {
            $diskResult = new stdClass();

            if ($raid = $this->isRaid($disk))
            {
                if (!isset($result[$raid->name]))
                {
                    $result[$raid->name] = $raid;
                }

                $diskResult->standby = $this->getSpindownStatsForDisk($disk->name);
                $diskResult->name = $disk->name;
                $result[$raid->name]->disks[] = $diskResult;
            }
            else
            {
                $standbyResult = new stdClass();
                $standbyResult->standby = $this->getSpindownStatsForDisk($disk->name);
                $standbyResult->name = $disk->name;
                $diskResult = $this->setAvailableSpace($disk, $diskResult);
                $diskResult->usage_percentage = round($diskResult->usage / $diskResult->size * 100) . '%';
                $diskResult->disks = [$standbyResult];
                $diskResult->name = $disk->name;
                $result[$disk->name] = $diskResult;
            }
        }

        ksort($result);
        return $result;
    }

    /**
     * Checks disk and it's children if they are part of a RAID array. If so retrieve the stats for the array
     *
     * @param stdClass $disk    The disk to check if it's part of a RAID array
     * @return bool|stdClass    Either an stdClass with size information of the RAID array, or false if the disk is not part of a RAID array.
     */
    private function isRaid(stdClass $disk)
    {
        if (isset($disk->children))
        {
            foreach ($disk->children as $child)
            {
                return $this->isRaid($child);
            }
        }
        else
        {
            if (stripos($disk->type, 'raid') !== false)
            {

                $fsuse = 'fsuse%';
                $diskResult = new stdClass();
                $diskResult->name = $disk->name;
                $diskResult->usage_percentage = $disk->$fsuse;
                $diskResult->size = $disk->fssize;
                $diskResult->available = $disk->fsavail;
                $diskResult->usage = $disk->fsused;
                return $diskResult;
            }
        }

        return false;
    }

    /**
     * Sets the disk usage of a standard Disk.
     *
     * @param stdClass $disk        The disk to set the stats for.
     * @param stdClass $diskResult  The stats of the disk to return.
     * @return stdClass             The stats of the given disk.
     */
    private function setAvailableSpace(stdClass $disk, stdClass $diskResult) : stdClass
    {
        if (isset($disk->children))
        {

            foreach ($disk->children as $child)
            {
                $diskResult = $this->setAvailableSpace($child, $diskResult);
            }
        }
        else
        {
            $diskResult->size = isset($diskResult->size) ? $diskResult->size + $disk->fssize : $disk->fssize;
            $diskResult->available = isset($diskResult->available) ? $diskResult->available + $disk->fsavail : $disk->fsavail;
            $diskResult->usage = isset($diskResult->usage) ? $diskResult->usage + $disk->fsused : $disk->fsused;
        }

        return $diskResult;
    }

    /**
     * Uses hdparm to retrieve spindown status of a disk.
     *
     * @param string $disk  the disk identifier, for example sda, used to query hdparm with.
     * @return bool         Whether or not the disk is spinned down.
     */
    private function getSpindownStatsForDisk(string $disk) : bool
    {
        $spindown_status = explode(PHP_EOL, shell_exec('hdparm -C /dev/' . escapeshellcmd($disk)));
        $spindown_status = array_filter($spindown_status, fn ($status) => !empty($status));
        return strripos(end($spindown_status), 'standby') != false;
    }
}
