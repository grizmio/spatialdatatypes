<?php

namespace Grizmio\SpatialDataTypes\Database;

// Our value object is immutable.
class Polygon{
    protected $_coordinates;

    // Factory method.
    public static function parse($value) {
        return new static($value);
    }

    public function __construct($coordinates)
    {
        $this->_coordinates = $coordinates;
    }

    public function getCoordinates()
    {
        return $this->_coordinates;
    }
}
