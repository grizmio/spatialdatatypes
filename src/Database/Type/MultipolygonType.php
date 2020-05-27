<?php

namespace Grizmio\SpatialDataTypes\Database\Type;

use Grizmio\SpatialDataTypes\Database\Multipolygon;
use Grizmio\SpatialDataTypes\Database\Polygon;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\ExpressionTypeInterface;
use Cake\Database\ExpressionInterface;
use PDO;


class MultipolygonType extends BaseType implements ExpressionTypeInterface {
    public function toPHP($value, DriverInterface $d) {
        $polygons = self::parse_sql_multipolygon_string($value);
        return new Multipolygon($polygons);
    }

    public static function parse_sql_multipolygon_string($value){
        if(strlen($value) === 0 || strtoupper(substr($value, 0, 12)) !== 'MULTIPOLYGON' ){
            return null;
        }
        // $value = "MULTIPOLYGON(((-31.615965936476 -72.158203125,-30.732392734006 -70.6201171875,-31.615965936476 -72.158203125,-33.192730941907 -71.015625,-31.615965936476 -72.158203125,-32.990235559651 -73.037109375,-31.615965936476 -72.158203125,-32.472695022061 -72.00439453125,-31.615965936476 -72.158203125)),((-30.82678090478 -67.1044921875,-32.731840896866 -68.203125,-30.82678090478 -67.1044921875,-32.082574559546 -65.41259765625,-30.82678090478 -67.1044921875,-30.845647420183 -65.45654296875,-30.82678090478 -67.1044921875,-31.709476360019 -67.08251953125,-30.82678090478 -67.1044921875,-32.194208672875 -67.25830078125,-30.82678090478 -67.1044921875)))";
        
        $start_pos = strpos($value, '(((') + 3;
        $len = strrpos($value, ')))') - $start_pos;

        $vagi_clean = substr($value, $start_pos, $len);

        $polygons_exp = explode(')),((', $vagi_clean);
        $polygons = [];
        foreach ($polygons_exp as $polygon) {
            $coordinates = [];
            $polygon = explode(',', $polygon);
            foreach ($polygon as $point) {
                $ps = explode(' ', $point);
                $coordinates[] = [doubleval($ps[0]), doubleval($ps[1])];
            }
            $polygons[] = $coordinates;
        }
        
        return $polygons;
    }

    public function marshal($value) {
        // Desde request al objeto correcto
        if(is_string($value)){
            $value = json_decode($value);
        }

        $results = [];
        foreach($value as $raw_pol){
            $results[] = new Polygon($raw_pol);
        }

        return new Multipolygon($results);
    }

    public function toExpression($value) : ExpressionInterface{
        // A una expresion SQL para ejecutarla
        if ($value instanceof Multipolygon) {
            $fe = new FunctionExpression(
                'ST_MPolyFromText',
                [
                    'MultiPolygon('.self::build_function_arg($value->getPolygons()).')'
                ]
            );
            
            return $fe;
        }
        
        if (is_array($value)) {
            return new FunctionExpression(
                'ST_MPolyFromText', 
                [
                    'MultiPolygon('.self::build_function_arg($value).')'
                ]
            );
        }
        return null;
    }

    public static function build_function_arg($polygons) {
        $str_polygons = [];
        foreach($polygons as $polygon){
            $coords_str_arr = [];
            foreach($polygon->getCoordinates() as $coord){
                if( ! is_numeric($coord[0]) || ! is_numeric($coord[1]) ){
                    return null;
                }
                $coords_str_arr[] = implode(' ', $coord);
                // Los poligonos deben ser cerrados, terminar en el punto de inicio
            }
            
            if ($coords_str_arr[0] != $coords_str_arr[count($coords_str_arr)-1]) {
                $coords_str_arr[] = $coords_str_arr[ 0 ];
            }
            
            $str_polygons[] = '(('.implode(',', $coords_str_arr).'))';
        }
        return implode(',', $str_polygons);
    }

    public function toStatement($value, DriverInterface $driver)
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }
        return PDO::PARAM_STR;
    }

    public function toDatabase($value, DriverInterface $driver)
    {
        return json_encode($value);
    }

}
