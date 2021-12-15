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

    public function svgMarker($svgmarker)
    {
        return $this->withMeta(['svgMarker' => $svgmarker]);
    }

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
    public function svgCar($svgCar)
    {
        return $this->withMeta(['svgCar' => $svgCar]);
    }
    public function gpsDriverPoints(array $gpsDriverPoints = [])
    {
        return $this->withMeta(['gpsDriverPoints' => $gpsDriverPoints]);
    }


    public function addCircleToMap($circle_lat, $circle_lng, $circle_radius, $circle_color = "rgba(220,220,220,0.8)", $circle_stroke_color = "rgba(220,220,220,0.8)", $line_width = 0)
    {
        return $this->withMeta([
            'circle_lat' => $circle_lat,
            'circle_lng' => $circle_lng,
            'circle_radius' => $circle_radius,
            'circle_color' => $circle_color,
            'circle_stroke_color' => $circle_stroke_color,
            'line_width' => $line_width,
        ]);
    }



    public function routeFinishAtOrigin(bool $routeFinishAtOrigin = true)
    {
        return $this->withMeta(['routeFinishAtOrigin' => $routeFinishAtOrigin]);
    }
}
