<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Sphere.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/City.php");

class Base extends Helper
{
    private $repetitors_term_id = null;

    public function __construct()
    {
        parent::__construct();

        $repetitors_term = $this->wpdb->get_row("SELECT term_id FROM wp_terms WHERE name LIKE '%Преподаватели творческих дисциплин Каталог%'", 'ARRAY_A');
        $this->repetitors_term_id = isset($repetitors_term['term_id']) ? $repetitors_term['term_id'] : null;
    }

    public function getList($request)
    {

        $sql = "SELECT DISTINCT b.* FROM wp_luck_bases b LEFT JOIN wp_luck_rooms r ON(b.base_id = r.base_id) WHERE r.room_id != '' AND b.isArchive = false";
        $sql .= $this->doFilter($request);
        $sql .= $this->doSort($request);

        if ($this->isLimitNeeded($request)) {
            $sql .= $this->setLimit($request);
        }

        return $this->wpdb->get_results($sql, "ARRAY_A");
    }

    public function total($request)
    {
        $sql = "SELECT COUNT(DISTINCT b.base_id) as total FROM wp_luck_bases b LEFT JOIN wp_luck_rooms r ON(b.base_id = r.base_id) WHERE r.room_id != '' AND b.isArchive = false";
        $sql .= $this->doFilter($request);

        return ['total' => (int)$this->wpdb->get_var($sql)];
    }

    public function showList($request)
    {
        $bases = $this->getBasesForMapsListWithRooms($request);

        $this->setSession($request);

        $bases = $this->changeImages($bases);

        return json_encode(['bases' => $bases]);
    }

    public function show($id)
    {
        $bases = $this->curl_get("/api/bases/list-city");

        $ids = array_column($bases, 'id');

        if (in_array($id, $ids)) {
            $key = array_search($id, $ids);
            return (object)$bases[$key];
        }

        return [];
    }

    public function updateOrCreate($base)
    {
        $old_base = $this->get($base->id);

        if (!empty($old_base)) {
            $this->update($base, $old_base);
        } else {
            $this->create($base);
        }
    }

    private function hasOnlinePay($base)
    {
        $hasOnlinePay = 0;
        foreach ($base->channels as $channel){
            if ($channel->kind == 3){
                $hasOnlinePay = 1;
                break;
            }
        }
        return $hasOnlinePay;
    }

    public function update($base, $old_base)
    {
        if ($base->sphereId == null || $base->cityId == null) {
            return false;
        }

        $sphere = $this->wpdb->get_row("SELECT * FROM wp_luck_spheres WHERE sphere_id = '" . $base->sphereId . "'", 'ARRAY_A');
        $sphere_name = Sphere::getName($sphere['name']);

        $post_info = $this->getPostInfo($base, $sphere);
        $base->post_title = $post_info['post_title'];
        $post_info['ID'] = $old_base['post_id'];
        wp_update_post($post_info);
        $this->wpdb->update('wp_luck_bases',
            [
                'city_id' => $base->cityId ? $base->cityId : '',
                'domain_id' => $base->domainId,
                'sphere_id' => $base->sphereId,
                'sphere' => $sphere_name,
                'name' => $base->name,
                'icon' => Sphere::getIcon($base->sphereId),
                'link' => $this->getBaseLink($base),
                'image' => $base->logo == null || $base->logo == '404.png' ? $this->setImage($base->id) : LUCK_API_DOMAIN . '/res/' . $base->logo,
                'description' => str_replace("\n", "<br>", $base->description),
                'address' => $base->address,
                'metro' => $base->metro,
                'work_time' => $base->workTime,
                'weekend_time' => $base->weekendTime,
                'gpsLat' => (float)$base->gpsLat,
                'gpsLong' => (float)$base->gpsLong,
                'minPrice' => (int)$base->minprice,
                'maxPrice' => (int)$base->maxprice,
                'rating' => $base->review ? (int)$base->review->value : 0,
                'reviews' => $base->review ? (int)$base->review->count : 0,
                'maxPointsPcPay' => (int)$base->maxPointsPcPay,
                'hasPrepay' => $this->hasPrepay($base->channels),
                'isRequest' => (int)$base->isRequest,
                'isArchive' => (int)$base->isArchive,
                'bookingPointsPc' => (int)$base->bookingPointsPc,
                'hasOnlinePay' => $this->hasOnlinePay($base)
            ],
            ['base_id' => $base->id]
        );
    }

    public function create($base)
    {
        if ($base->sphereId == null || $base->cityId == null) {
            return false;
        }

        $sphere = $this->wpdb->get_row("SELECT * FROM wp_luck_spheres WHERE sphere_id = '" . $base->sphereId . "'", 'ARRAY_A');
        $sphere_name = Sphere::getName($sphere['name']);

        $post_info = $this->getPostInfo($base, $sphere);
        $base->post_title = $post_info['post_title'];
        $post_id = wp_insert_post($post_info);

        $this->wpdb->insert('wp_luck_bases',
            [
                'base_id' => $base->id,
                'post_id' => $post_id,
                'city_id' => $base->cityId ? $base->cityId : '',
                'domain_id' => $base->domainId,
                'sphere_id' => $base->sphereId,
                'sphere' => $sphere_name,
                'name' => $base->name,
                'link' => $this->getBaseLink($base),
                'icon' => Sphere::getIcon($base->sphereId),
                'image' => $base->logo == null || $base->logo == '404.png' ? $this->setImage($base->id) : LUCK_API_DOMAIN . '/res/' . $base->logo,
                'description' => str_replace("\n", "<br>", $base->description),
                'address' => $base->address,
                'metro' => $base->metro,
                'work_time' => $base->workTime,
                'weekend_time' => $base->weekendTime,
                'gpsLat' => (float)$base->gpsLat,
                'gpsLong' => (float)$base->gpsLong,
                'minPrice' => (int)$base->minprice,
                'maxPrice' => (int)$base->maxprice,
                'rating' => $base->review ? (int)$base->review->value : 0,
                'reviews' => $base->review ? (int)$base->review->count : 0,
                'maxPointsPcPay' => (int)$base->maxPointsPcPay,
                'hasPrepay' => $this->hasPrepay($base->channels),
                'isRequest' => (int)$base->isRequest,
                'isArchive' => (int)$base->isArchive,
                'bookingPointsPc' => (int)$base->bookingPointsPc,
                'hasOnlinePay' => $this->hasOnlinePay($base)
            ]
        );
    }

    public function archiveAll()
    {
        $this->wpdb->query("UPDATE wp_luck_bases SET isArchive = true");
    }

    public function getBySlug($slug)
    {
        $base = $this->wpdb->get_row("SELECT * FROM wp_luck_bases WHERE link LIKE '%" . $slug . "%' ORDER BY link", "ARRAY_A");

        if (!empty($base)) {
            $base['work_time'] = $base['work_time'] == "0-24" ? "Круглосуточно" : $base['work_time'];
            $base['weekend_time'] = $base['weekend_time'] == "0-24" ? "Круглосуточно" : $base['weekend_time'];
        }

        return $base;
    }

    public function archive($id)
    {
        $old_base = $this->wpdb->get_row("SELECT post_id FROM wp_luck_bases WHERE base_id = '" . $id . "'", 'ARRAY_A');

        if (!empty($old_base)) {
            $this->wpdb->query("DELETE FROM wp_posts WHERE ID = '" . (int)$old_base['post_id'] . "'");
            $this->wpdb->query("DELETE FROM wp_postmeta WHERE post_id = '" . (int)$old_base['post_id'] . "'");
            $this->wpdb->query("DELETE FROM wp_term_relationships WHERE object_id = '" . (int)$old_base['post_id'] . "'");
        }

        $this->wpdb->update('wp_luck_bases', ['isArhive' => true], ['base_id' => $id]);

        return true;
    }

    public function delete($id)
    {
        $old_base = $this->wpdb->get_row("SELECT post_id FROM wp_luck_bases WHERE base_id = '" . $id . "'", 'ARRAY_A');

        if (!empty($old_base)) {
            $this->wpdb->query("DELETE FROM wp_posts WHERE ID = '" . (int)$old_base['post_id'] . "'");
            $this->wpdb->query("DELETE FROM wp_postmeta WHERE post_id = '" . (int)$old_base['post_id'] . "'");
            $this->wpdb->query("DELETE FROM wp_term_relationships WHERE object_id = '" . (int)$old_base['post_id'] . "'");
        }

        $this->wpdb->query("DELETE FROM wp_luck_bases WHERE base_id = '" . $id . "'");

        return true;
    }

    public function deleteAll()
    {
        $old_posts = $this->wpdb->get_results("SELECT ID FROM wp_posts WHERE post_author = 0", 'ARRAY_A');

        foreach ($old_posts as $old_post) {
            $this->wpdb->query("DELETE FROM wp_posts WHERE ID = '" . (int)$old_post['ID'] . "'");
            $this->wpdb->query("DELETE FROM wp_postmeta WHERE post_id = '" . (int)$old_post['ID'] . "'");
            $this->wpdb->query("DELETE FROM wp_term_relationships WHERE object_id = '" . (int)$old_post['ID'] . "'");
        }

        $this->wpdb->query("DELETE FROM wp_luck_bases");
    }

    public function getMinPriceInterval($request)
    {
        $sql = "SELECT MAX(b.maxPrice) as max, MIN(b.minPrice) as min FROM wp_luck_bases  b LEFT JOIN wp_luck_rooms r ON(b.base_id = r.base_id) WHERE r.room_id != ''";

        if (isset($request['sphere_id'])) {
            $sql .= " AND b.sphere_id = '" . $request['sphere_id'] . "'";
        }

        if (isset($request['city_id'])) {
            $sql .= " AND b.city_id = '" . $request['city_id'] . "'";
        }

        return $this->wpdb->get_row($sql, "ARRAY_A");
    }

    private function getCategoryId($sphere)
    {
        $category_term_id = $sphere['term_id'];

        if ($sphere['kind'] == 2) {
            $category_term_id = $this->repetitors_term_id;
        }

        return $category_term_id;
    }

    private function setSession($request)
    {
        $slug = $request['slug'];
        $city = $request['city'];

        $_SESSION['katalog'][$slug]['city'] = $request['city'];

        if (isset($request['square'])) {
            $_SESSION['katalog'][$slug][$city]['square'] = json_encode($request['square']);
        } else {
            unset($_SESSION['katalog'][$slug][$city]['square']);
        }

        if (isset($request['minPrice'])) {
            $_SESSION['katalog'][$slug][$city]['minPrice'] = json_encode($request['minPrice']);
        } else {
            unset($_SESSION['katalog'][$slug][$city]['minPrice']);
        }

        if (isset($request['features'])) {
            $_SESSION['katalog'][$slug][$city]['features'] = json_encode($request['features']);
        } else {
            unset($_SESSION['katalog'][$slug][$city]['features']);
        }

        if (isset($request['filters'])) {
            $_SESSION['katalog'][$slug]['filters'] = json_encode($request['filters']);
        } else {
            unset($_SESSION['katalog'][$slug]['filters']);
        }
    }

    private function changeImages($bases)
    {
        $dir = wp_upload_dir();
        $width = 640;
        $height = 432;

        foreach ($bases as $key => $base) {
            $exp = explode('/', $base['image']);
            $exp = explode('\\', end($exp));
            $img = end($exp);

            if (!stristr($img, '.')) {
                continue;
            }

            $exp_img = explode('.', $img);
            $img_name = $exp_img[0] == '404' ? 'default' : $exp_img[0];
            $format = $exp_img[1];

            $path_to_file = '/bases/' . $img_name . '_' . $width . '-' . $height . '.' . $format;
            $image_dir = $dir['basedir'] . $path_to_file;

            if (!file_exists($image_dir)) {
                $this->cropImage($base['image'], $image_dir, $width, $height);
            }

            $bases[$key]['image'] = $dir['baseurl'] . $path_to_file;
        }

        return $bases;
    }

    /**
     * @param string $aInitialImageFilePath - строка, представляющая путь к обрезаемому изображению
     * @param string $aNewImageFilePath - строка, представляющая путь куда нахо сохранить выходное обрезанное изображение
     * @param int $aNewImageWidth - ширина выходного обрезанного изображения
     * @param int $aNewImageHeight - высота выходного обрезанного изображения
     */
    private function cropImage($aInitialImageFilePath, $aNewImageFilePath, $aNewImageWidth, $aNewImageHeight)
    {
        if (($aNewImageWidth < 0) || ($aNewImageHeight < 0)) {
            return false;
        }

        // Массив с поддерживаемыми типами изображений
        $lAllowedExtensions = array(1 => "gif", 2 => "jpeg", 3 => "png");

        $aInitialImageFilePath = str_replace('\\', '/', $aInitialImageFilePath);

        // Получаем размеры и тип изображения в виде числа
        list($lInitialImageWidth, $lInitialImageHeight, $lImageExtensionId) = getimagesize($aInitialImageFilePath);

        if (!array_key_exists($lImageExtensionId, $lAllowedExtensions)) {
            return false;
        }
        $lImageExtension = $lAllowedExtensions[$lImageExtensionId];

        // Получаем название функции, соответствующую типу, для создания изображения
        $func = 'imagecreatefrom' . $lImageExtension;
        // Создаём дескриптор исходного изображения
        $lInitialImageDescriptor = $func($aInitialImageFilePath);

        // Определяем отображаемую область
        $lCroppedImageWidth = 0;
        $lCroppedImageHeight = 0;
        $lInitialImageCroppingX = 0;
        $lInitialImageCroppingY = 0;
        if ($aNewImageWidth / $aNewImageHeight > $lInitialImageWidth / $lInitialImageHeight) {
            $lCroppedImageWidth = floor($lInitialImageWidth);
            $lCroppedImageHeight = floor($lInitialImageWidth * $aNewImageHeight / $aNewImageWidth);
            $lInitialImageCroppingY = floor(($lInitialImageHeight - $lCroppedImageHeight) / 2);
        } else {
            $lCroppedImageWidth = floor($lInitialImageHeight * $aNewImageWidth / $aNewImageHeight);
            $lCroppedImageHeight = floor($lInitialImageHeight);
            $lInitialImageCroppingX = floor(($lInitialImageWidth - $lCroppedImageWidth) / 2);
        }

        // Создаём дескриптор для выходного изображения
        $lNewImageDescriptor = imagecreatetruecolor($aNewImageWidth, $aNewImageHeight);
        imagecopyresampled($lNewImageDescriptor, $lInitialImageDescriptor, 0, 0, $lInitialImageCroppingX, $lInitialImageCroppingY, $aNewImageWidth, $aNewImageHeight, $lCroppedImageWidth, $lCroppedImageHeight);
        $func = 'image' . $lImageExtension;

        // сохраняем полученное изображение в указанный файл
        return $func($lNewImageDescriptor, $aNewImageFilePath);
    }

    private function imagecreatefromfile($filename, $format)
    {
        switch ($format) {
            case 'jpeg':
                return @imagecreatefromjpeg($filename);
                break;
            case 'jpg':
                return @imagecreatefromjpeg($filename);
                break;
            case 'png':
                return @imagecreatefrompng($filename);
                break;
            case 'PNG':
                return @imagecreatefrompng($filename);
                break;
            case 'gif':
                return @imagecreatefromgif($filename);
                break;
            default:
                return false;
                break;
        }
    }

    private function doFilter($request)
    {
        $sql = '';
        if (isset($request['city'])) {
            $sql .= " AND b.city_id = '" . $request['city'] . "'";

            if (isset($request['features'])) {
                $features = $this->getListByFeatures($request['features']) ;
                if (empty($features)){
                    $sql .= " AND b.base_id = 'undefined'";
                }else{
                    $sql .= " AND b.base_id IN(" .$features. ")";
                }
            }

            if (isset($request['square'])) {
                $sql .= " AND r.square BETWEEN " . $request['square'][0] . " AND " . $request['square'][1];
            }

            if (isset($request['minPrice'])) {
                $sql .= " AND b.minPrice >= " . $request['minPrice'][0];
                $sql .= " AND b.maxPrice <= " . $request['minPrice'][1];
            }
        }

        if (isset($request['sphere'])) {
            $sql .= " AND b.sphere_id = '" . $request['sphere'] . "'";
        }

        if (isset($request['without_ids'])) {
            $sql .= " AND b.base_id NOT IN ('" . implode($request['without_ids'], "', '") . "')";
        }

        if (isset($request['filters']) && !empty($request['filters'])) {
            if (in_array('hasPrepay', $request['filters'])) {
                $sql .= " AND b.hasPrepay = " . (int)$request['hasPrepay'];
            }

            if (in_array('isRequest', $request['filters'])) {
                $sql .= " AND b.isRequest = " . (int)$request['isRequest'];
            }

            if (in_array('hasPointsPay', $request['filters'])) {
                $sql .= " AND b.maxPointsPcPay > 0";
            }
            if (in_array('hasOnlinePay', $request['filters'])) {
                $sql .= " AND b.hasOnlinePay > 0";
            }
        }

        return $sql;
    }

    private function doSort($request)
    {
        if (!isset($request['sort_order']) || empty($request['sort_order'])) {
            return ' ORDER BY b.bookingPointsPc DESC, RAND()';
        }

        return ' ORDER BY b.' . $request['sort_order'] . ' ' . $request['sort_direction'];
    }

    private function setLimit($request)
    {
        $sql = '';
        if (isset($request['limit'])) {
            $sql .= " LIMIT " . $request['limit'];
        }

        return $sql;
    }

    private function toString($bases)
    {
        $result = [];

        foreach ($bases as $base) {
            $result[] = "'" . $base . "'";
        }

        return implode(", ", $result);
    }

    private function isLimitNeeded($request)
    {
        return !isset($request['limit']) || empty($request['limit']) || $request['limit'] != -1;
    }

    private function getListByFeatures($features)
    {
        $sql = 'SELECT DISTINCT base_id FROM wp_luck_rooms';

        foreach ($features as $key => $feature) {
            if ($key == 0) {
                $sql .= " WHERE feature_ids LIKE '%" . $feature . "%'";
            } else {
                $sql .= " AND feature_ids LIKE '%" . $feature . "%'";
            }
        }

        $bases = $this->wpdb->get_col($sql);

        if (!empty($bases)) {
            return $this->toString($bases);
        }

        return '';
    }

    private function get($id)
    {
        return $this->wpdb->get_row("SELECT * FROM wp_luck_bases WHERE base_id = '" . $id . "'", "ARRAY_A");
    }

    private function getPostInfo($base, $sphere)
    {
        if ($base->sphereId == null || $base->cityId == null) {
            return [];
        }

        $category_term_id = $this->getCategoryId($sphere);

        if ($category_term_id == false) {
            return [];
        }

        $sphere_name = Sphere::getName($sphere['name']);

        $post_title_prefix = $sphere['kind'] == 2 ? 'Преподаватель' : $sphere_name;
        $post_title = wp_strip_all_tags($post_title_prefix . ' ' . $base->name);

        $post_content = $base->address ? $post_title . ' аренда в ' . $base->address : $post_title;
        //если сфера == обучение
        if ($sphere['kind'] == 2) {
            $post_content = $base->description ? $post_title . '. ' . $base->description : $post_title;
        }

        return [
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_name' => $this->translit($post_title),
            'post_status' => 'publish',
            'post_author' => 0,
            'post_category' => [$category_term_id]
        ];
    }

    private function hasPrepay($channels)
    {
        foreach ($channels as $channel) {
            if ($channel->kind == 2 || $channel->kind == 3) {
                return true;
            }
        }

        return false;
    }

    private function setImage($baseId)
    {
        $images = $this->wpdb->get_row("SELECT image FROM wp_luck_rooms WHERE base_id = '" . $baseId . "' LIMIT 1", "ARRAY_A");

        if (!empty($images)) {
            $images = json_decode($images['image']);
            return !empty($images) ? $images[0] : '';
        }

        return '';
    }

    private function getBaseLink($base)
    {
        $siteUrl = get_site_url();

        //обучение
        $prefix = $base->sphereId == 'e6d1f71b-f1a0-4686-9279-08547b248c18' ? 'repetitors' : 'katalog';

        $sphere = (new Sphere())->get($base->sphereId);
        $sphereSlug = $this->translit($sphere['name']);

        $city = (new City())->get($base->cityId);
        $citySlug = $this->translit($city['name']);

        $baseSlug = $this->translit($base->post_title);

        return $siteUrl . '/' . $prefix . '/' . $sphereSlug . '/' . $citySlug . '/' . $baseSlug;
    }

    public function getBasesList($request)
    {
       $results = $this->getBasesListWithRooms($request);
        $this->setSession($request);
        return $results;
    }

    private function getBasesListWithRooms($request)
    {
        $fields = 'b.base_id, b.link, b.sphere, b.name, b.address, b.metro, b.work_time, b.weekend_time, b.hasOnlinePay, b.bookingPointsPc, b.maxPointsPcPay, b.reviews, b.minPrice, b.maxPrice';
        $sql = "SELECT DISTINCT $fields FROM wp_luck_bases b LEFT JOIN wp_luck_rooms r ON(b.base_id = r.base_id) WHERE r.room_id != '' AND b.isArchive = false";
        $sql .= $this->doFilter($request);
        $sql .= $this->doSort($request);

        if ($this->isLimitNeeded($request)) {
            $sql .= $this->setLimit($request);
        }

        $results = $this->wpdb->get_results($sql, "ARRAY_A");

        $rooms_id = [];
        foreach ($results as $result)
        {
            $rooms_id[] = $result['base_id'];
        }
        $rooms = $this->getRoomsIn($rooms_id);

        foreach ($results as $key => $result){
            foreach ($rooms as $room){
                if ($room['base_id'] == $result['base_id']){
                    $results[$key]['rooms'][] = $room;
                }
            }
        }
        return $results;
    }

    private function getBasesForMapsListWithRooms($request)
    {
        $results = $this->getList($request);

        if (isset($request['saved'])){
            $rooms_id = [];
            foreach ($results as $result)
            {
                $rooms_id[] = $result['base_id'];
            }
            $rooms = $this->getRoomsIn($rooms_id, 'wp_luck_rooms.*');

            foreach ($results as $key => $result){
                foreach ($rooms as $room){
                    if ($room['base_id'] == $result['base_id']){
                        $results[$key]['rooms'][] = $room;
                    }
                }
            }
        }

        return $results;
    }

    public function getRoomsIn(array $bases_id = [], string $fields = 'images, base_id')
    {
        $sql = 'SELECT '.$fields.' FROM wp_luck_rooms
                WHERE base_id IN ("'.(implode('", "', $bases_id).'")
                AND room_id != ""');

        return $this->wpdb->get_results($sql, "ARRAY_A");
    }
}
