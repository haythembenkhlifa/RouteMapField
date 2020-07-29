<?php

namespace Haythem\RouteMapField;

use Laravel\Nova\Fields\Field;

class RouteMapField extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'route-map-field';

    public function apiKey($apikey)
    {
        return $this->withMeta(['apiKey' => $apikey]);
    }

    public function gpsPoints($gpsPoints)
    {
        return $this->withMeta(['gpsPoints' => $gpsPoints]);
    }

    // public function svgMarker($svgmarker)
    // {
    //     return $this->withMeta(['svgMarker' => $svgmarker]);
    // }

    public function svgOfficeMarker($svgofficemarker)
    {
        return $this->withMeta(['svgOfficeMarker' => $svgofficemarker]);
    }

    public function showSummary($showsummary = false)
    {
        return $this->withMeta(['showSummary' => $showsummary]);
    }

    public function showManuevers($showmanuevers = false)
    {
        return $this->withMeta(['showManuevers' => $showmanuevers]);
    }

    public function customErrorMessage($errormessage = "")
    {
        return $this->withMeta(['customErrorMessage' => $errormessage]);
    }
    public function showDistance($showdistance = false)
    {
        return $this->withMeta(['showdistance' => $showdistance]);
    }

    public function showTime($showtime = false)
    {
        return $this->withMeta(['time' => $showtime]);
    }
}
