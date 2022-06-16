<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 * @suppress PHP2414
 */
class Disk extends BaseModel
{
    public function getSpindownStatsForMountpoint(string $mountPoint) : array
    {
        $disks = json_decode(shell_exec('lsblk -J'))->blockdevices;

        $disks = array_filter($disks, function ($disk) use($mountPoint) {
            $currentMountPoint = $this->getMountPountForDisk($disk);
            return in_array($mountPoint, $currentMountPoint);
        });
        $result = [];

        foreach ($disks as $disk)
        {
            $spindown_status = explode(PHP_EOL, shell_exec('sudo hdparm -C /dev/' . escapeshellcmd($disk->name)));
            $spindown_status = array_filter($spindown_status, fn ($status) => !empty($status));
            $result[$disk->name] = strripos(end($spindown_status), 'standby') != false;
        }

        return $result;
    }

    private function getMountPountForDisk(object $disk) : array
    {
        if (!empty($disk->mountpoint))
        {
            return [$disk->mountpoint];
        }

        foreach ($disk->children as $child)
        {
            $result[] = $this->getMountPountForDisk($child)[0];
        }

        return $result;
    }
}
