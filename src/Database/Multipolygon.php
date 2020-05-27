<?php

namespace Grizmio\SpatialDataTypes\Database;

// Our value object is immutable.
class Multipolygon {
    protected $_polygons; // arreglo de polygon

    // Factory method.
    public static function parse($value) {
        return new static($value);
    }

    public function __construct($polygons)
    {
        $this->_polygons = $polygons;
    }

    public function getPolygons()
    {
        return $this->_polygons;
    }
}
