<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to devices.
 *
 * @package Models
 * @suppress PHP2414
 */
class Disks extends BaseModel
{
    public function getStats() : array
    {
        $result = [];

        if (is_file('/.dockerenv'))
        {
            $mountpoints = glob('/mnt/*', GLOB_ONLYDIR);

            foreach ($mountpoints as $mountpoint)
            {
                $diskResult = json_decode(shell_exec('df '  . $mountpoint .' | awk \'BEGIN {}{if($1=="Filesystem")next;if(a)print",";print"{\"mount\":\""$6"\",\"size\":\""$2"\",\"used\":\""$3"\",\"available\":\""$4"\",\"usage\":\""$5"\"}";a++;}END{}\''));
                $result[] = $diskResult;
            }
        }
        else
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

                    $diskResult->mount = $disk->mountpoint;
                    $result[]->disks[] = $diskResult;
                }
                else
                {
                    $diskResult = $this->setAvailableSpace($disk, $diskResult);
                    $diskResult->usage = round($diskResult->usage / $diskResult->size * 100) . '%';
                    $diskResult->mount = $disk->mountpoint;
                    $result[] = $diskResult;
                }
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
        else if (stripos($disk->type, 'raid') !== false)
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
            $diskResult->used = isset($diskResult->usage) ? $diskResult->usage + $disk->fsused : $disk->fsused;
        }

        return $diskResult;
    }
}
