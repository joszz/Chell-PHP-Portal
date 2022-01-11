<?php

namespace Chell\Models;

/**
 * The model responsible for all actions related to dashboard widgets.
 *
 * @package Models
 */
class Widget
{
    public int $id;
    public string $partial;

    /**
     * Sets the defaulot column sizes and whether or not this widget contains other subwidgets.
     *
     * @param int $xs                   The Bootstrap XS column size
     * @param int $sm                   The Bootstrap SM column size
     * @param int $md                   The Bootstrap MD column size
     * @param mixed $hasSubWidgets      Has sub widgets
     */
    public function __construct(public int $xs = 12, public int $sm = 0, public int $md = 0, public $hasSubWidgets = false)
    {
        $this->xs = $xs;
        $this->sm = $sm;
        $this->md = $md;
        $this->hasSubWidgets = $hasSubWidgets;
    }

    /**
     * Gets the CSS class with the bootstrap column sizes
     *
     * @param bool $renderSubWidgets    Whether or not to render the subwidgets when this widget has subwidgets. If not render, return an empty class.
     * @return string                   The CSS class for the panel.
     */
    public function getPanelClass(bool $renderSubWidgets = false) : string
    {
        if ($this->hasSubWidgets && !$renderSubWidgets)
        {
            return '';
        }

        $class = 'widget col-xs-' . $this->xs;
        $class .=  $this->sm != 0 ? ' col-sm-' . $this->sm : null;
        $class .=  $this->md != 0 ? ' col-md-' . $this->md : null;

        return $class;
    }

    /**
     * Gets the CSS class for the seperator between rows of widgets.
     * Uses 12 columns since rows can't be used with variable column sizes.
     *
     * @param int $columnCountSm    Total amount of SM columns displayed so far.
     * @param int $columnCountMd    Total amount of MD columns displayed so far.
     * @return bool|string          A row seperator, visible for SM, MD, and LG depending on the column counts.
     */
    public function getRowSeperatorClass(int $columnCountSm, int $columnCountMd) : bool|string
    {
        if ($columnCountSm % 12 == 0 || $columnCountMd % 12 == 0)
        {
            return 'col-xs-12 ' . ($columnCountSm % 12 == 0 ? 'visible-sm ' : null) . ($columnCountMd % 12 == 0 ? 'visible-md visible-lg' : null);
        }

        return false;
    }

    /**
     * Calculates the total column count.
     *
     * @param int $columnCountSm        Total amount of SM columns displayed so far, passed by reference.
     * @param int $columnCountMd        Total amount of MD columns displayed so far, passed by reference.
     * @param bool $renderSubWidgets    Whether or not this call is rendering subwidgets.
     */
    public function calculateColumnCounts(int &$columnCountSm, int &$columnCountMd, bool $renderSubWidgets = false)
    {
        if (!$this->hasSubWidgets || $renderSubWidgets)
        {
            $sm = $this->sm != 0 ? $this->sm : $this->xs;
            $md = $this->md != 0 ? $this->md : $sm;
            $columnCountSm += $sm;
            $columnCountMd += $md;
        }
    }
}