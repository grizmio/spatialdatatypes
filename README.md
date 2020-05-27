# Grizmio/SpatialDataTypes plugin for CakePHP

## Crear proyecto
composer -vvv create-project --prefer-dist cakephp/app:4.\* mycakephpproject

## Installation
```
cd mycakephpproject
# modificar composer.json agregando al final:
"repositories": [ {
        "type":"path",
        "url":"/home/grizmio/composer_private/grizmio/spatialdatatypes"
    }]

# usar require NO install
composer require -vvv grizmio/spatialdatatypes
```

## Configuration using BirdsTable
edit ./src/Model/Table/BirdsTable.php

En la tabla: src/Model/Table/BirdsTable.php
```
public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('birds');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');
        $this->addBehavior('Grizmio/SpatialDataTypes.Spatial', [
            'spacial_fields' => ["Birds.polygons"]
            ]);
    }

protected function _initializeSchema(TableSchemaInterface $schema) : TableSchemaInterface {
        $schema->setColumnType('polygons', 'multipolygon');
        return $schema;
    }
```

class Application extends BaseApplication
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * @return void
     */
    public function bootstrap(): void
    {
        $this->addPlugin('Grizmio/SpatialDataTypes', ['bootstrap'=> true, 'routes' => false]);

views:
$bird->polygons->getPolygons()

