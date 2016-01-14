<?php

/**
 * The model repsonisble for all actions related to harddisks.
 * 
 * @package Models
 */
class Diskdrives extends BaseModel
{
    /**
     * Calculates the total- and free space/percentage for each drive configured in the database.
     * 
     * @return array    An array with harddisk usage information.
     */
    public function DiskStatisticsLocal()
    {
        $drivespace = array();
        $drives = Diskdrives::find(array('order' => 'name ASC'));

        foreach($drives as $drive)
        {
            $drivespace[$drive->name] = array();
            $drivespace[$drive->name]['total_space']     = number_format(disk_total_space($drive->mountpath) / 1024 / 1024 / 1024, 2);
            $drivespace[$drive->name]['free_space']      = number_format(diskfreespace($drive->mountpath) / 1024 / 1024 / 1024, 2);
            $drivespace[$drive->name]['free_percentage'] = diskfreespace($drive->mountpath) / disk_total_space($drive->mountpath);
            $drivespace[$drive->name]['used_percentage'] = (disk_total_space($drive->mountpath) - diskfreespace($drive->mountpath)) / disk_total_space($drive->mountpath);
            $drivespace[$drive->name]['used_percentage'] = number_format($drivespace[$drive->name]['used_percentage'] * 100, 0);
            $drivespace[$drive->name]['free_percentage'] = number_format($drivespace[$drive->name]['free_percentage'] * 100, 0);

            $drivespace[$drive->name]['class'] = 'success';

            if($drivespace[$drive->name]['used_percentage'] > 90)
            {
                $drivespace[$drive->name]['class'] = 'danger';
            }
            else if($drivespace[$drive->name]['used_percentage'] > 70)
            {
                $drivespace[$drive->name]['class'] = 'warning';
            }
            else if($drivespace[$drive->name]['used_percentage'] > 50)
            {
                $drivespace[$drive->name]['class'] = 'info';
            }
        }
        
        return $drivespace;
    }
}