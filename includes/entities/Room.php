<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Base.php");

class Room extends Helper
{
    public function getList($request)
    {
        $sql = "SELECT * FROM wp_luck_rooms WHERE 1";
        $sql .= $this->doFilter($request);

        return $this->wpdb->get_results($sql, "ARRAY_A");
    }

    public function showList($request)
    {
        $rooms = $this->getList($request);

        return json_encode(['rooms' => $rooms]);
    }

    public function show($id)
    {
        $response = $this->apiGet("rooms/search2");

        $ids = array_column($response->rooms, 'id');

        if (in_array($id, $ids)) {
            $key = array_search($id, $ids);
            return (object)$response->rooms[$key];
        }

        return [];
    }

    public function getImagesBySlug($slug)
    {
        $baseClass = new Base();
        $base = $baseClass->getBySlug($slug);

        $rooms = $this->getList(['base_id' => $base['base_id']]);

        $images = [];

        foreach ($rooms as $room) {
            $decoded_images = json_decode($room['image']);
            foreach ($decoded_images as $image) {
                $images[] = $image;
            }
        }

        return $images;
    }

    public function save($room)
    {
        $images = [];
        if ($this->exists($room, 'images')) {
            foreach ($room->images as $image) {
                $images[] = LUCK_API_DOMAIN . ROOM_IMG_PREFIX . $image->value;
                $api_images[] = $image->value;
            }
        }

        $this->wpdb->insert('wp_luck_rooms',
            [
                'room_id' => $room->id,
                'base_id' => $room->baseId,
                'name' => $room->name,
                'image' => json_encode($images),
                'square' => $room->square,
                'raider' => $room->raider,
                'feature_ids' => json_encode($room->features),
                'images' => json_encode($api_images),
                'reviews_count' => !is_null($room->review)?$room->review->count:0,
                'reviews_value' => !is_null($room->review)?$room->review->value:0,
            ]
        );
    }

    public function archive($id)
    {
        if (!empty($id)) {
            $this->wpdb->update('wp_luck_rooms', ['isArhive' => true], ['room_id' => $id]);

            return true;
        }

        return false;
    }

    public function delete($id)
    {
        $this->wpdb->query("DELETE FROM wp_luck_rooms WHERE room_id = '" . $id . "'");

        return true;
    }

    public function deleteAll()
    {
        $this->wpdb->query("DELETE FROM wp_luck_rooms");
    }

    public function getSquareInterval($request)
    {
        $sql = "SELECT MAX(r.square) as max, MIN(r.square) as min FROM wp_luck_rooms r LEFT JOIN wp_luck_bases b ON (r.base_id = b.base_id) WHERE 1";

        if(isset($request['sphere_id'])){
            $sql .= " AND b.sphere_id = '". $request['sphere_id'] ."'";
        }

        if(isset($request['city_id'])){
            $sql .= " AND b.city_id = '". $request['city_id'] ."'";
        }

        return $this->wpdb->get_row($sql, "ARRAY_A");
    }

    private function doFilter($request)
    {
        $sql = '';

        if(isset($request['base_id'])){
            $sql .= " AND base_id = '". $request['base_id'] ."'";
        }

        return $sql;
    }

    public function getRoomsWithBaseId($base_id)
    {
        $sql = "SELECT * FROM wp_luck_rooms WHERE base_id = '$base_id'";
        return $this->wpdb->get_results($sql, "ARRAY_A");
    }
}
