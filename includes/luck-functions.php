<?php

add_action('admin_menu', function (){
    add_submenu_page(
        'options-general.php',
        'Сatalog Settings',
        'Каталог',
        'manage_options',
        LUCK_DIR_INCLUDES . '/luck-admin-page.php'
    );
});

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Socket.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Base.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Room.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Feature.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Review.php");

add_action('wp_ajax_get_cities', 'get_cities');
add_action('wp_ajax_nopriv_get_cities', 'get_cities');
add_action('wp_ajax_get_bases', 'get_bases');
add_action('wp_ajax_nopriv_get_bases', 'get_bases');
add_action('wp_ajax_get_filtered_bases', 'get_filtered_bases');
add_action('wp_ajax_nopriv_get_filtered_bases', 'get_filtered_bases');
add_action('wp_ajax_get_spheres', 'get_spheres');
add_action('wp_ajax_nopriv_get_spheres', 'get_spheres');
add_action('wp_ajax_get_options', 'get_options');
add_action('wp_ajax_nopriv_get_options', 'get_options');

//katalog
add_action('wp_ajax_get_cities_katalog', 'get_cities_katalog');
add_action('wp_ajax_nopriv_get_cities_katalog', 'get_cities_katalog');
add_action('wp_ajax_get_bases_katalog', 'get_bases_katalog');
add_action('wp_ajax_nopriv_get_bases_katalog', 'get_bases_katalog');

add_action('wp_ajax_get_bases_katalog__with_rooms', 'getBasesWithRooms');
add_action('wp_ajax_nopriv_get_bases_katalog_with_rooms', 'getBasesWithRooms');

add_action('wp_ajax_get_features_katalog', 'get_features_katalog');
add_action('wp_ajax_nopriv_get_features_katalog', 'get_features_katalog');
add_action('wp_ajax_get_rooms', 'get_rooms');
add_action('wp_ajax_nopriv_get_rooms', 'get_rooms');
add_action('wp_ajax_get_reviews', 'get_reviews');
add_action('wp_ajax_nopriv_get_reviews', 'get_reviews');
add_action('wp_ajax_socket', 'socket');
add_action('wp_ajax_nopriv_socket', 'socket');
add_action('wp_ajax_get_square_interval', 'get_square_interval');
add_action('wp_ajax_nopriv_get_square_interval', 'get_square_interval');
add_action('wp_ajax_get_min_price_interval', 'get_min_price_interval');
add_action('wp_ajax_nopriv_get_min_price_interval', 'get_min_price_interval');
add_action('wp_ajax_get_city_session', 'get_city_session');
add_action('wp_ajax_nopriv_get_city_session', 'get_city_session');
add_action('wp_ajax_get_total_bases', 'getTotalBases');
add_action('wp_ajax_nopriv_get_total_bases', 'getTotalBases');
add_action('wp_ajax_nopriv_get_url_sphere', 'getUrlSphere');
//add_action('wp_enqueue_scripts',  'luck_catalog_scripts');
//
//function luck_catalog_scripts ()
//{
//    wp_enqueue_script('luck-catalog-vue-masket', 'https://cdnjs.cloudflare.com/ajax/libs/imask/6.0.7/imask.min.js', [], null, true);
//
//}

function get_cities()
{
    global $wpdb;

    $cities = $wpdb->get_results("SELECT* FROM wp_luck_cities", "ARRAY_A");

    if(exists($_SESSION, 'user_city')){
        wp_send_json(['cities' => $cities, 'city' => $_SESSION['user_city']]);
        wp_die();
    }

    $response = @file_get_contents("http://api.sypexgeo.net/json/" . $_SERVER['HTTP_X_FORWARDED_FOR']);

    $info = json_decode($response);
    $city_name = isset($info->city->name_ru) && $info->country->name_ru == 'Россия' ? $info->city->name_ru : "Москва";

    $city = $wpdb->get_row("SELECT * FROM wp_luck_cities WHERE name LIKE '%". $city_name ."%'", "ARRAY_A");
    $_SESSION['user_city'] = isset($city['city_id']) ? $city : null;

    wp_send_json(['cities' => $cities, 'city' => $_SESSION['user_city']]);
    wp_die();
}

function get_bases()
{
    global $wpdb;

    if(!exists($_POST, 'sphere_id')){
        wp_send_json_error('sphere_id is required', 400);
        wp_die();
    }

    if(!exists($_POST, 'city_id')){
        wp_send_json_error('city_id is required', 400);
        wp_die();
    }

    $sql = "SELECT DISTINCT b.* FROM wp_luck_bases b LEFT JOIN wp_luck_rooms r ON(b.base_id = r.base_id) WHERE r.room_id != '' AND b.sphere_id = '". $_POST['sphere_id'] ."' AND b.city_id = '". $_POST['city_id'] . "' AND b.isArchive = false";
    $bases = $wpdb->get_results($sql, 'ARRAY_A');

    $data = [];
    if(isset($_POST['date']) && isset($_POST['hours']) && isset($_POST['options'])){
        $_SESSION['options'] = $_POST['options'];
        $data = array(
            'sphere'    => $_POST['sphere_id'],
            'date'      => $_POST['date'],
            'hours'     => $_POST['hours'],
            'options'   => $_POST['options'],
            'city'      => $_POST['city_id']
        );
    }elseif(!empty($_SESSION['options']) && !empty($_POST['is_rep'])){
        $data = array(
            'sphere'    => $_POST['sphere_id'],
            'date'      => $_POST['date'],
            'hours'     => $_POST['hours'],
            'options'   => $_SESSION['options'],
            'city'      => $_POST['city_id']
        );
    }else{
        unset($_SESSION['options']);
    }

    //фильтруем по опциям которые фильтруются только по запросу rooms/search и на основании уже отфильтрованых комнат ищем базы...(
    if(!empty($data)){
        $helper = new Helper();
        $response = $helper->apiGet('rooms/search2', $data);

        $bases_ids = [];
        foreach($response->rooms as $room){
            if(!in_array($room->baseId, $bases_ids) && !$room->price->errors){
                $bases_ids[] = $room->baseId;
            }
        }

        foreach($bases as $key => $base){
            if(!in_array($base['base_id'], $bases_ids)){
                unset($bases[$key]);
            }
        }
    }

    if(isset($_POST['remove_features'])){
        unset($_SESSION['features_rep']);
    }

    if(isset($_POST['features']) && !empty($_POST['features'])){
        foreach($bases as $base_key => $base){
            $sql = "SELECT * FROM wp_luck_rooms WHERE base_id = '". $base['base_id'] ."'";
            foreach($_POST['features'] as $feature_id){
                $sql .= " AND feature_ids LIKE '%". $feature_id ."%'";
            }
            $isset_features = $wpdb->get_results($sql, "ARRAY_A");

            if(empty($isset_features)){
                unset($bases[$base_key]);
            }
        }
    }

    $bases = array_values($bases);
    $_SESSION['bases'] = $bases;

    wp_send_json($bases);
    wp_die();
}

function get_filtered_bases()
{
    global $wpdb;
    if($_SESSION['bases']){
        $bases = $_SESSION['bases'];
        if(!empty($_POST['features'])){
            foreach($bases as $base_key => $base){
                $sql = "SELECT * FROM wp_luck_rooms WHERE base_id = '". $base['base_id'] ."'";
                foreach($_POST['features'] as $key => $feature){
                    $sql .= " AND feature_ids LIKE '%". $feature['feature_id'] ."%'";
                }
                $isset_features = $wpdb->get_results($sql, "ARRAY_A");

                if(empty($isset_features)){
                    unset($bases[$base_key]);
                }
            }
        }
        wp_send_json($bases);
    }
    wp_die();
}

function get_spheres()
{
    global $wpdb;

    if(!exists($_POST, 'city_id')){
        wp_send_json_error(['error' => 'city_id is required'], 400);
        wp_die();
    }

    if(!exists($_POST, 'kind')){
        wp_send_json_error(['error' => 'kind is required'], 400);
        wp_die();
    }

    $spheres = $wpdb->get_results("SELECT DISTINCT s.* FROM wp_luck_bases b LEFT JOIN wp_luck_spheres s ON (b.sphere_id = s.sphere_id) WHERE b.city_id = '". $_POST['city_id'] ."' AND s.kind = '". $_POST['kind'] ."'", 'ARRAY_A');

    wp_send_json($spheres);
    wp_die();
}

function get_options()
{
    global $wpdb;

    if(!isset($_POST['sphere_id'])) {
        wp_send_json_error(['error' => 'sphere_id is required'], 400);
        wp_die();
    }

    $sphere = $wpdb->get_row("SELECT options FROM wp_luck_spheres WHERE sphere_id = '". $_POST['sphere_id'] ."'", "ARRAY_A");

    if(!exists($sphere, 'options')) {
        wp_send_json_error(['error' => 'sphere doesnt have any options'], 422);
        wp_die();
    }

    $options = json_decode($sphere['options']);
    $response = ['options' => $options];

    if(exists($_SESSION, 'options')){
        $response['selected'] = $_SESSION['options'];
    }

    wp_send_json($response);
    wp_die();
}

function exists($item, $value)
{
    return isset($item[$value]) && !empty($item[$value]);
}

//////katalog

function get_cities_katalog()
{
    global $wpdb;

    $city = $wpdb->get_row("SELECT * FROM wp_luck_cities WHERE name LIKE '%Москва%'", "ARRAY_A");
    $user_city = isset($city['city_id']) ? $city : null;
    $_SESSION['user_city'] = $user_city;

    $cities = $wpdb->get_results("SELECT* FROM wp_luck_cities", "ARRAY_A");

    wp_send_json(['cities' => $cities, 'city' => $_SESSION['user_city']]);
}

function get_bases_katalog()
{
    $base = new Base();
    echo $base->showList($_GET);
    wp_die();
}
function getBasesWithRooms()
{
    $base = new Base();
    wp_send_json($base->getBasesList($_GET));
    wp_die();
}

function get_features_katalog()
{
    $feature = new Feature();
    echo $feature->get();
    wp_die();
}

function get_rooms()
{
    $room = new Room();
    echo $room->showList($_GET);
    wp_die();
}

function get_reviews()
{
    $review = new Review();
    echo $review->get($_GET);
    wp_die();
}

function get_city_session()
{
    $helper = new Helper();
    $slug = $_GET['slug'];
    $city = $_GET['city_id'];

    $results = [
        'features' => isset($_SESSION['katalog'][$slug][$city]['features']) ? json_decode($_SESSION['katalog'][$slug][$city]['features']) : [],
        'square' => isset($_SESSION['katalog'][$slug][$city]['square']) ? json_decode($_SESSION['katalog'][$slug][$city]['square']) : [],
        'minPrice' => isset($_SESSION['katalog'][$slug][$city]['minPrice']) ? json_decode($_SESSION['katalog'][$slug][$city]['minPrice']) : [],
    ];

    wp_send_json($results);
}

function socket()
{
    /*$socket = new Socket($_POST['data']);
    $socket->change();

    wp_send_json_success();
    wp_die();*/
}

function get_square_interval()
{
    $room = new Room();
    wp_send_json($room->getSquareInterval($_GET));
}

function get_min_price_interval()
{
    $base = new Base();
    wp_send_json($base->getMinPriceInterval($_GET));
}

function getTotalBases()
{
    $base = new Base();
    wp_send_json($base->total($_GET));
}

function getUrlSphere()
{
    $sphere = new Sphere();
    wp_send_json($sphere->getLink($_GET['sphere_id']));
}

function apiGet($link, $params = [])
{
    $curl = curl_init();

    $base_link = LUCK_API_DOMAIN . '/api/' . $link;
    $url = !empty($params) ? $base_link . '?' . http_build_query($params) : $base_link;

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

    $out = curl_exec($curl);

    return json_decode($out);
}

function updateBases()
{
    $base = new Base();
    $base->archiveAll();

    $bases = apiGet("bases/list-city?allarchive=false&nomobile=false");

    foreach ($bases as $key => $item) {
        $base->updateOrCreate($item);
    }
}

function updateRooms()
{
    $room = new Room();
    $room->deleteAll();

    $response = apiGet("rooms/search2");

    foreach ($response->rooms as $item) {
        $room->save($item);
    }
}


