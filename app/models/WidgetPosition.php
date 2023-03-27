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
        $generalCategories = ['application', 'redis', 'imageproxy', 'duo', 'hibp'];

        try
        {
            foreach ($settings as $widget)
            {
                if (!in_array($widget->name,  $generalCategories))
                {
                    $widgetPosition = current($widgetPositions->filter(function($position) use ($widget) {
                        if ($position->controller == $widget->name ||
                            ($position->controller == 'arr' && ($widget->name == 'sonarr' || $widget->name == 'radarr'))){
                            return $position;
                        }
                    }));

                    if ($widget->enabled->value && !$widgetPosition)
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
}