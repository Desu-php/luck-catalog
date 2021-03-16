<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");

class Sphere extends Helper
{
    public function getList($request = [])
    {
        $sql = "SELECT * FROM wp_luck_spheres WHERE 1";
        $sql .= $this->doFilter($request);

        return $this->wpdb->get_results($sql, 'ARRAY_A');
    }

    public function getRepetitorsSphere()
    {
        return $this->wpdb->get_row("SELECT * FROM wp_luck_spheres WHERE kind = 2", 'ARRAY_A');
    }

    public function getByUrl($url = '')
    {
        $spheres = $this->getList();

        if (count(explode('/', $url)) > 1) {
            $exp = explode('/', $url);
            $url = end($exp);
        }

        foreach ($spheres as $sphere) {
            if ($url === $this->translit($sphere['name']) || ($url == 'muzykalnyj-klass' && $sphere['sphere_id'] == '38f7aeac-d462-41a2-b4e7-f2c642cb9225')) {
                return $sphere;
            }
        }

        return [];
    }

    public function get($sphere_id)
    {
        return $this->wpdb->get_row("SELECT * FROM wp_luck_spheres WHERE sphere_id = '". $sphere_id ."'", "ARRAY_A");
    }

    public function save($sphere)
    {
        $catalog = $this->wpdb->get_row("SELECT term_id FROM wp_terms WHERE name LIKE '%Каталог творческих площадок%'", 'ARRAY_A');
        if (empty($catalog)) {
            throw new Exception('Должна быть создана рубрика "Каталог творческих площадок" со ссылкой: "katalog"');
        }

        $options = [];
        if ($sphere->kind == 2) {
            $spheres_options = $this->apiGet("groups/names?type=14");
            foreach ($spheres_options as $spheres_option) {
                if (in_array($spheres_option->id, $sphere->options)) {
                    $options[] = $spheres_option;
                }
            }
        }

        $updated_data = [
            'name' => $sphere->value,
            'link' => $this->translit($sphere->value),
            'icon' => LUCK_API_DOMAIN . IMG_PREFIX . str_replace("\\", '/', $sphere->icon),
            'image' => $this->getImage($sphere->id),
            'options' => json_encode($options),
            'kind' => $sphere->kind
        ];

        $this->wpdb->update('wp_luck_spheres', $updated_data, ['sphere_id' => $sphere->id]);
    }

    public static function getIcon($sphere_id)
    {
        if ($sphere_id == '0676eaa9-4ddb-495c-8980-06250d7d5f4a') { //репетиционные базы
            return plugins_url() . '/luck-catalog/assets/img/RehMap.png';
        } elseif ($sphere_id == '41ce174b-2964-416b-9830-3468bf15dba3') { //студии звукозаписи
            return plugins_url() . '/luck-catalog/assets/img/StudioMap.png';
        } elseif ($sphere_id == '36e5c7bc-f45c-4251-9552-456664c55c22') { //танцевальрные залы
            return plugins_url() . '/luck-catalog/assets/img/DanceMap.png';
        } elseif ($sphere_id == 'b2c8f9ff-a0f0-42f8-be3e-c061d5c1144c') { //фотостудии
            return plugins_url() . '/luck-catalog/assets/img/PhotoMap.png';
        } elseif ($sphere_id == '38f7aeac-d462-41a2-b4e7-f2c642cb9225') { //муз классы
            return plugins_url() . '/luck-catalog/assets/img/ClassMap.png';
        } else {
            return plugins_url() . '/luck-catalog/assets/img/LoftMap.png';
        }
    }

    public static function getName($name)
    {
        $sphere_names = [
            'Репетиционные базы' => 'Репетиционная база',
            'Музыкальные классы' => 'Музыкальный класс',
            'Танцевальные залы' => 'Танцевальный зал',
            'Студии звукозаписи' => 'Студия звукозаписи',
            'Фотостудии' => 'Фотостудия',
            'Площадки для мероприятий' => 'Лофт'
        ];

        return isset($sphere_names[$name]) ? $sphere_names[$name] : $name;
    }

    public function getLink($sphere_id = null)
    {
        $sphere = $this->wpdb->get_row("SELECT term_id FROM wp_luck_spheres WHERE sphere_id = '". $sphere_id ."'", 'ARRAY_A');

        if(empty($sphere)){
            return '/';
        }

        $term = get_term_link((int)$sphere['term_id']);

        return !is_wp_error($term) ? $term : '/';
    }

    public function getH2($sphere_name = '')
    {
        $sphere_names = [
            'Репетиционные базы' => 'репетиционной базы',
            'Музыкальные классы' => 'музыкального класса',
            'Танцевальные залы' => 'танцевального зала',
            'Студии звукозаписи' => 'студии звукозаписи',
            'Фотостудии' => 'фотостудии',
            'Площадки для мероприятий' => 'лофта'
        ];

        if (isset($sphere_names[$sphere_name])) {
            return 'Описание ' . $sphere_names[$sphere_name];
        }

        return 'Описание базы';
    }

    private function doFilter($request)
    {
        $sql = '';

        if(isset($request['kind'])){
            $sql .= " AND kind = '". $request['kind'] ."'";
        }

        return $sql;
    }

    private function getImage($sphere_id)
    {
        if ($sphere_id == '0676eaa9-4ddb-495c-8980-06250d7d5f4a') {//репетиционные базы
            return plugins_url() . '/luck-catalog/assets/img/spheres/rep.jpg';
        } elseif ($sphere_id == '41ce174b-2964-416b-9830-3468bf15dba3') {//студии звукозаписи
            return plugins_url() . '/luck-catalog/assets/img/spheres/studio.jpg';
        } elseif ($sphere_id == '36e5c7bc-f45c-4251-9552-456664c55c22') {//танцевальные залы
            return plugins_url() . '/luck-catalog/assets/img/spheres/dance.jpg';
        } elseif ($sphere_id == 'b2c8f9ff-a0f0-42f8-be3e-c061d5c1144c') {//фотостудии
            return plugins_url() . '/luck-catalog/assets/img/spheres/photo.jpg';
        } elseif ($sphere_id == '38f7aeac-d462-41a2-b4e7-f2c642cb9225') {//муз классы
            return plugins_url() . '/luck-catalog/assets/img/spheres/vocal.png';
        } else {
            return plugins_url() . '/luck-catalog/assets/img/spheres/loft.jpg';
        }
    }
}
