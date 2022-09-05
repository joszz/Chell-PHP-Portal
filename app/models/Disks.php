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
        $disks = json_decode(shell_exec('lsblk -J'))->blockdevices;

        $disks = array_filter($disks, fn ($disk) => $disk->type == 'disk');
        $result = [];

        foreach ($disks as $disk)
        {
            $mountPoints = $this->getMountPountsForDisk($disk);
            $mountPoints = array_filter($mountPoints, fn ($mountPoint) => stripos($mountPoint, '/boot/') === false && stripos($mountPoint, '/tmp') === false);

            foreach ($mountPoints as $mountPoint)
            {
                if ($mountPoint)
                {
                    if (!isset($result[$mountPoint]))
                    {
                        $result[$mountPoint] = $this->getDiskSpaceForMountpoint($mountPoint);
                    }

                    $diskResult = new stdClass();
                    $diskResult->standby = $this->getSpindownStatsForDisk($disk->name);
                    $diskResult->name = $disk->name;
                    $result[$mountPoint]->disks[] = $diskResult;
                }
            }
        }

        ksort($result);
        return array_values($result);
    }

    /**
     * Uses hdparm to retrieve spindown status of a disk.
     *
     * @param string $disk  the disk identifier, for example sda, used to query hdparm with.
     * @return bool         Whether or not the disk is spinned down.
     */
    private function getSpindownStatsForDisk(string $disk) : bool
    {
        $spindown_status = explode(PHP_EOL, shell_exec('sudo hdparm -C /dev/' . escapeshellcmd($disk)));
        $spindown_status = array_filter($spindown_status, fn ($status) => !empty($status));
        return strripos(end($spindown_status), 'standby') != false;
    }

    /**
     * Recursively retrieves mountpoints for a disk. When a disk is part of a RAID array, the children field needs to be checked.
     *
     * @param object $disk  The current disk object, retrieved from lsblk.
     * @return array        All the mountpoints for the given disk.
     */
    private function getMountPountsForDisk(object $disk) : array
    {
        $result = [];
        $mountPoints = array_filter($disk->mountpoints);

        if (!empty($mountPoints) && count($mountPoints))
        {
            return $mountPoints;
        }

        if(isset($disk->children))
        {
            foreach ($disk->children as $child)
            {
                $result = array_merge($result, $this->getMountPountsForDisk($child));
            }
        }

        return $result;
    }

    /**
     * Uses df to retrieve usage of a given partition.
     *
     * @param string $mountPoint    The mountpoint to retrieve usage for.
     * @return object               An object containing information form df.
     */
    private function getDiskSpaceForMountpoint(string $mountPoint) : object
    {
        if (!$this->diskspace)
        {
            $output = shell_exec('df $1  | gawk \'
            BEGIN { ORS = ""; print " [ "}
            /Filesystem/ {next}
            { printf "%s{\"name\": \"%s\", \"size\": \"%s\", \"usage\": \"%s\", \"available\": \"%s\", \"usage_percentage\": \"%s\", \"mount_point\": \"%s\"}",
                separator, $1, $2, $3, $4, $5, $6
              separator = ", "
            }
            END { print " ] " }\'');
            $output = json_decode($output);

            foreach ($output as $mountpoint)
            {
                $mountpoint->usage_percentage = substr($mountpoint->usage_percentage, 0, -1);
            }

            $this->diskspace = $output;
        }

        return current(array_filter($this->diskspace, fn($disk) => $disk->mount_point == $mountPoint));
    }
}
