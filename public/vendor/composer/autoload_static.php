<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb4cf5b06d1a8ad275a8d91a9bdaee356
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Palasthotel\\WordPress\\TrafficIncidents\\' => 39,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Palasthotel\\WordPress\\TrafficIncidents\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Palasthotel\\WordPress\\TrafficIncidents\\Assets' => __DIR__ . '/../..' . '/classes/Assets.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Data\\IncidentsDatabase' => __DIR__ . '/../..' . '/classes/Data/IncidentsDatabase.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Data\\PostTypeTraffic' => __DIR__ . '/../..' . '/classes/Data/PostTypeTraffic.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Model\\BoundingBox' => __DIR__ . '/../..' . '/classes/Model/BoundingBox.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Model\\IncidentEntity' => __DIR__ . '/../..' . '/classes/Model/IncidentEntity.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Model\\TomTomIncidentRequestArgs' => __DIR__ . '/../..' . '/classes/Model/TomTomIncidentRequestArgs.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Model\\TomTomIncidentsResponse' => __DIR__ . '/../..' . '/classes/Model/TomTomIncidentsResponse.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Model\\TomTomTrafficIncidentResponse' => __DIR__ . '/../..' . '/classes/Model/TomTomTrafficIncidentResponse.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Repository' => __DIR__ . '/../..' . '/classes/Repository.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Schedule' => __DIR__ . '/../..' . '/classes/Schedule.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\Service\\TomTomTrafficIncidents' => __DIR__ . '/../..' . '/classes/Service/TomTomTrafficIncidents.php',
        'Palasthotel\\WordPress\\TrafficIncidents\\_Component' => __DIR__ . '/../..' . '/classes/_Component.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb4cf5b06d1a8ad275a8d91a9bdaee356::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb4cf5b06d1a8ad275a8d91a9bdaee356::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitb4cf5b06d1a8ad275a8d91a9bdaee356::$classMap;

        }, null, ClassLoader::class);
    }
}