<?php

namespace Grizmio\SpatialDataTypes\Database\Type;

use Grizmio\SpatialDataTypes\Database\Polygon;
use Cake\Database\Expression\FunctionExpression;
use Cake\Database\DriverInterface;
use Cake\Database\Type\BaseType;
use Cake\Database\Type\ExpressionTypeInterface;
use Cake\Database\ExpressionInterface;
use PDO;

// https://book.cakephp.org/3/en/orm/database-basics.html#mapping-custom-datatypes-to-sql-expressions
class PolygonType extends BaseType implements ExpressionTypeInterface {
    public function toPHP($value, DriverInterface $d) {
        $coordinates = self::parse_sql_polygon_string($value);   
        return new Polygon($coordinates);
    }

    public static function parse_sql_polygon_string($value){
        if(strlen($value) === 0 || substr($value, 0, 7) !== 'POLYGON' ){
            return null;
        }
        // $value = "POLYGON((-34.26175652446 -59.6337890625,-34.894942447397 -68.8623046875,-31.569175449071 -65.76416015625,-33.238687527574 -61.2158203125,-34.26175652446 -59.6337890625))";
        $start_pos = strpos($value, '((') + 2;
        $len = strpos($value, ')') - $start_pos;

        $vagi_clean = substr($value, $start_pos, $len);
        $points = explode(',', $vagi_clean);
        $coordinates = [];
        foreach($points as $point){
            $ps = explode(' ', $point);
            $coordinates[] = [doubleval($ps[0]), doubleval($ps[1])];
        }
        return $coordinates;
    }

    public function marshal($value) {
        // Desde request al objeto correcto
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        if (is_array($value)) {
            return new Polygon($value);
        }
        return null;
    }

    public function toExpression($value) : ExpressionInterface {
        // A una expresion SQL para ejecutarla
        //EJEMPLO: ST_GEOMFROMTEXT('POLYGON((99 76, 334 23, 99 76))')
        if ($value instanceof Polygon) {
            $fe = new FunctionExpression(
                'ST_PolyFromText',
                [
                    'Polygon(('.self::build_function_arg($value->getCoordinates()).'))'
                ]
            );
            $x = 'Polygon(('.self::build_function_arg($value->getCoordinates()).'))';
            return $fe;
        }
        
        if (is_array($value)) {
            return new FunctionExpression(
                'ST_PolyFromText', 
                [
                    'Polygon(('.self::build_function_arg($value).'))'
                ]
            );
        }
        return null;
    }

    public static function build_function_arg($coordinates) {
        $coords_str_arr = [];
        foreach($coordinates as $coord){
            if( ! is_numeric($coord[0]) || ! is_numeric($coord[1]) ){
                return null;
            }
            $coords_str_arr[] = implode(' ', $coord);
        }
        if($coords_str_arr[0] != $coords_str_arr[count($coords_str_arr)-1])
            $coords_str_arr[] = $coords_str_arr[ 0 ];
        $polygon_function_args = implode(',', $coords_str_arr);
        return $polygon_function_args;
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
