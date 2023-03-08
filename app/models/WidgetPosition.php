<?php

namespace Chell\Models;

use Exception;
use Chell\Models\Devices;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;

/**
 * The model responsible for all actions related to WidgetPositions.
 *
 * @package Models
 */
class WidgetPosition extends BaseModel
{
    /**
     * Called by the Settings controller to reorder all widgets when saving the dashboard settings.
     *
     * @param SettingsContainer $settings   The current settings
     * @suppress PHP0407
     */
    public static function reorderPositions(SettingsContainer $settings)
    {
        $transactionManager = new TransactionManager();
        $transaction = $transactionManager->get();

        $widgetPositions = WidgetPosition::find(['order' => 'position']);
        $maxWidgetPosition = WidgetPosition::maximum(['column' => 'position']) ?? 0;

        try
        {
            foreach ($settings as $widget)
            {
                if ($widget->section->name === 'dashboard')
                {
                    $widgetPosition = current($widgetPositions->filter(function($position) use ($widget) {
                        if ($position->controller == $widget->name ||
                            ($position->controller == 'arr' && ($widget->name == 'sonarr' || $widget->name == 'radarr'))){
                            return $position;
                        }
                    }));

                    if ($widget->enabled && empty($widgetPosition))
                    {
                        $widgetPosition = new WidgetPosition();
                        $widgetPosition->controller = $widget->name == 'sonarr' || $widget->name == 'radarr' ? 'arr' : $widget->name;
                        $widgetPosition->position = ++$maxWidgetPosition;
                        $widgetPosition->setTransaction($transaction);
                        $widgetPosition->save();
                    }
                    else if(!$widget->enabled && $widgetPosition)
                    {
                        $widgetPositionsToUpdate = WidgetPosition::find([
                            'conditions' => 'position > ?1',
                            'bind'       => [1 => $widgetPosition->position]]);

                        foreach($widgetPositionsToUpdate as $widgetPositionToUpdate)
                        {
                            $widgetPositionToUpdate->position--;
                            $widgetPositionToUpdate->setTransaction($transaction);
                            $widgetPositionToUpdate->save();
                        };

                        $widgetPosition->setTransaction($transaction);
                        $widgetPosition->delete();
                    }
                }
            }

            $transaction->commit();
        }
        catch (Exception $exception)
        {
            $transaction->rollback();
            throw($exception);
        }
    }

    /**
     * When a new device is added in the settings, make sure the device widget is added to the widget positions.
     *
     * @param Devices $device   The device added.
     */
    public static function addDeviceWidgetPosition(Devices $device)
    {
        $transactionManager = new TransactionManager();
        $transaction = $transactionManager->get();

        try
        {
            $dashboardDeviceCount = Devices::count('show_on_dashboard = 1');
            $deviceWidgetAdded = WidgetPosition::findFirst(['conditions' => 'controller = "devices"']);

            if ($dashboardDeviceCount == 0 && !$deviceWidgetAdded)
            {
                $maxWidgetPosition = WidgetPosition::maximum(['column' => 'position']) ?? 1;
                $widgetPosition = new WidgetPosition();
                $widgetPosition->controller = 'devices';
                $widgetPosition->position = ++$maxWidgetPosition;
                $widgetPosition->setTransaction($transaction);
                $widgetPosition->save();
            }

            $device->setTransaction($transaction);
            $device->save();
            $transaction->commit();
        }
        catch (Exception $exception)
        {
            $transaction->rollback();
            throw($exception);
        }
    }
}