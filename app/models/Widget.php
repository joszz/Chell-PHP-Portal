<?php

namespace Chell\Models;

class Widget
{
    public string $viewFileName;
    public int $xs;
    public int $sm;
    public int $md;
    public bool $hasSubWidgets;

    public function __construct(int $xs = 12, int $sm = 0, int $md = 0, $hasSubWidgets = false)
    {
        $this->xs = $xs;
        $this->sm = $sm;
        $this->md = $md;
        $this->hasSubWidgets = $hasSubWidgets;
    }

    public function getClass($renderSubWidgets = false)
    {
        if ($this->hasSubWidgets && !$renderSubWidgets)
        {
            return '';
        }

        $class = 'col-xs-' . $this->xs;
        $class .=  $this->sm != 0 ? ' col-sm-' . $this->sm : null;
        $class .=  $this->md != 0 ? ' col-md-' . $this->md : null;

        return $class;
    }

    public function calculateColumnCounts(&$columnCountSm, &$columnCountMd, $renderSubWidgets = false)
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