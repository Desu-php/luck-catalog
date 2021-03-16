<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");

class City extends Helper
{
    public function save($city)
    {
        $this->wpdb->insert('wp_luck_cities',
            [
                'city_id' => $city->id,
                'name' => $city->name,
                'slug' => $this->translit($city->name),
                'gpsLat' => $city->gpsLat,
                'gpsLong' => $city->gpsLong
            ]
        );
    }

    public function getName($cityId = null)
    {
        if (!$cityId) {
            return null;
        }

        $result = $this->wpdb->get_row("SELECT name FROM wp_luck_cities WHERE city_id = '" . $cityId . "'", 'ARRAY_A');

        return !empty($result) ? $result['name'] : null;
    }

    public function get($cityId = null)
    {
        return $this->wpdb->get_row("SELECT * FROM wp_luck_cities WHERE city_id = '" . $cityId . "'", 'ARRAY_A');
    }

    public function getBySlug($slug = '')
    {
        if (!$slug) {
            return null;
        }

        return $this->wpdb->get_row("SELECT * FROM wp_luck_cities WHERE slug = '" . $slug . "'", 'ARRAY_A');
    }

    public function deleteAll()
    {
        $this->wpdb->query("DELETE FROM wp_luck_cities");
    }
}
