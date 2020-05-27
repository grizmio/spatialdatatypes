<?php
declare(strict_types=1);

use Cake\Database\Type;

Type::map('Polygon', 'Grizmio\SpatialDataTypes\Database\Type\PolygonType');
Type::map('Multipolygon', 'Grizmio\SpatialDataTypes\Database\Type\MultipolygonType');

