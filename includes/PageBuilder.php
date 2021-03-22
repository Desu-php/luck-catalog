<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Room.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Sphere.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Base.php");

class PageBuilder extends Helper
{
    public function getRoomData()
    {
        $data = [];

        $slug = $this->getSlug($_SERVER['REQUEST_URI']);

        $data['isRepetitorsPage'] = $this->isRepetitorsPage($_SERVER['REQUEST_URI']);

        $room = new Room();
        $data['images'] = $room->getImagesBySlug($slug);

        return $data;
    }

    public function getCatalogRoomData($order = true, $corrected_slug = false)
    {
        $data = [];

        $slug = $this->getSlug($_SERVER['REQUEST_URI']);

        $base = new Base();
        $data['base'] = $base->getBySlug($slug, $order, $corrected_slug);
        $sphere = new Sphere();
        $data['sphere'] = $sphere->get($data['base']['sphere_id']);
        $data['back_href'] = $sphere->getLink($data['base']['sphere_id']);
        $data['h2'] = $sphere->getH2($data['sphere']['name']);

        return $data;
    }

    public function getRepetitorsRoomData()
    {
        $data = [];

        $slug = $this->getSlug($_SERVER['REQUEST_URI']);

        $base = new Base();
        $data['base'] = $base->getBySlug($slug);

        $sphere = new Sphere();
        $data['sphere'] = $sphere->get($data['base']['sphere_id']);
        $data['back_href'] = $sphere->getLink($data['base']['sphere_id']);

        $room = new Room();
        $data['rooms'] = $room->getList(['base_id' => $data['base']['base_id']]);

        return $data;
    }

    public function getCategoryData()
    {
        $data = [];

        $data['slug'] = $this->getSlug($_SERVER['REQUEST_URI']);

        $sphere = new Sphere();
        $data['sphere'] = $sphere->getByUrl($data['slug']);

        $data['session'] = isset($_SESSION['katalog'][$data['slug']]) ? $_SESSION['katalog'][$data['slug']] : [];
        $data['cityId'] = isset($_SESSION['katalog'][$data['slug']]['city']) ? $_SESSION['katalog'][$data['slug']]['city'] : '';

        if(isset($_GET['city'])){

            $city = new City();
            $cityData = $city->getBySlug($_GET['city']);

            if(isset($cityData['city_id'])){
                $cityId = $cityData['city_id'];
                $slug = $data['slug'];

                $data['square'] = $this->getSessionSquare($slug, $cityId);
                $data['minPrice'] = $this->getSessionMinPrice($slug, $cityId);
                $data['features'] = $this->getSessionFeatures($slug, $cityId);

                $data['cityId'] = $cityData['city_id'];
            }

        } else {
            $cityId = isset($data['session']['city']) ? $data['session']['city'] : null;
            $slug = $data['slug'];

            $data['square'] = $this->getSessionSquare($slug, $cityId);
            $data['minPrice'] = $this->getSessionMinPrice($slug, $cityId);
            $data['features'] = $this->getSessionFeatures($slug, $cityId);
        }

        return $data;
    }

    public function getCategoryTitle()
    {
        $slug = $this->getSlug($_SERVER['REQUEST_URI']);

        $sphere = new Sphere();
        $sphere = $sphere->getByUrl($slug);

        $title = wp_title('|', false);

        if(isset($_GET['city'])){
            $city = new City();
            $cityData = $city->getBySlug($_GET['city']);
            if($cityData['city_id'] == '898f482f-5fdd-4192-b4ed-d157e6952bc6' && $sphere['sphere_id'] == '36e5c7bc-f45c-4251-9552-456664c55c22'){
                $title = 'Танцевальные залы Каталог ' . $cityData['name'];
            }
        }

        return $title;
    }

    private function getSessionSquare($slug, $cityId)
    {
        if(isset($_SESSION['katalog'][$slug][$cityId]['square'])){
            return $_SESSION['katalog'][$slug][$cityId]['square'];
        }

        return null;
    }

    private function getSessionMinPrice($slug, $cityId)
    {
        if(isset($_SESSION['katalog'][$slug][$cityId]['minPrice'])){
            return $_SESSION['katalog'][$slug][$cityId]['minPrice'];
        }

        return null;
    }

    private function getSessionFeatures($slug, $cityId)
    {
        if(isset($_SESSION['katalog'][$slug][$cityId]['features'])){
            return $_SESSION['katalog'][$slug][$cityId]['features'];
        }

        return null;
    }
}
