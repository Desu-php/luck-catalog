<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Sphere.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/City.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Room.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Base.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Feature.php");

class Parser extends Helper
{
    public function __construct()
    {
        $this->updateSpheres();
        $this->updateCities();
        $this->updateRooms();
        $this->updateBases();
    }

    private function updateSpheres()
    {
        $sphere = new Sphere();

        $spheres = $this->apiGet("spheres/search");

        foreach ($spheres as $item) {
            $sphere->save($item);
        }
    }

    private function updateCities()
    {
        $city = new City();
        $city->deleteAll();

        $cities = $this->apiGet('groups/cities');

        foreach ($cities as $item) {
            $city->save($item);
        }
    }

    private function updateRooms()
    {
        $room = new Room();
        $room->deleteAll();

        $response = $this->apiGet("rooms/search2");

        foreach ($response->rooms as $item) {
            $room->save($item);
        }
    }

    private function updateBases()
    {
        $base = new Base();
        $base->archiveAll();

        $bases = $this->apiGet("bases/list-city?allarchive=false&nomobile=false");

        foreach ($bases as $key => $item) {
            $base->updateOrCreate($item);
        }
    }
}
