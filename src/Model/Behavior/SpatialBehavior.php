<?php

namespace Grizmio\SpatialDataTypes\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Cake\Event\EventInterface;
use ArrayObject;
use Cake\ORM\Query;

/**
 * Spatial behavior
 * 
 * Make sure spatial columns are loaded as text when needed.
 * 
 * @property \Cake\ORM\Table $_table
 */
class SpatialBehavior extends Behavior
{

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];
    public $spacial_fields;

    public function initialize(array $config): void {
        if(array_key_exists('spacial_fields', $config))
            $this->spacial_fields = $config['spacial_fields'];
        else
            $this->spacial_fields = [];
    }

    /**
     * Callback before each find is executed
     * 
     * @param Event $event
     * @param Query $query
     * @param ArrayObject $options
     * @param type $primary
     */
    public function beforeFind(EventInterface $event, Query $query, \ArrayObject $options, $primary)
    {
        $query->traverse(
            function (&$value) use ($query) {
                if(!is_array($value)){
                    return $value;
                }
                if (is_array($value) && empty($value)) {
                    $value = $query->aliasFields($this->_table->getSchema()->columns());
                }
                
                foreach ($value as $key => $field) {
                    if(in_array($field, $this->spacial_fields)) {
                        $value[$key] = $query->func()->ST_AsText([
                            $this->_table->aliasField($field) => 'identifier'
                        ]);
                    }
                }
                $query->select($value);
            },
            ['select']
        );
        return $query;
    }
}