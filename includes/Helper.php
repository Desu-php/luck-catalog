<?php

class Helper
{
    protected $wpdb;

    const REPETITORS_SLUG = 'repetitors';
    const CATALOG_SLUG = 'katalog';

    public function __construct()
    {
        $this->wpdb = $GLOBALS['wpdb'];
    }

    protected function translit($text)
    {
        $ru = explode('-', "А-а-Б-б-В-в-Ґ-ґ-Г-г-Д-д-Е-е-Ё-ё-Є-є-Ж-ж-З-з-И-и-І-і-Ї-ї-Й-й-К-к-Л-л-М-м-Н-н-О-о-П-п-Р-р-С-с-Т-т-У-у-Ф-ф-Х-х-Ц-ц-Ч-ч-Ш-ш-Щ-щ-Ъ-ъ-Ы-ы-Ь-ь-Э-э-Ю-ю-Я-я");
        $en = explode('-', "A-a-B-b-V-v-G-g-G-g-D-d-E-e-E-e-E-e-ZH-zh-Z-z-I-i-I-i-I-i-J-j-K-k-L-l-M-m-N-n-O-o-P-p-R-r-S-s-T-t-U-u-F-f-H-h-TS-ts-CH-ch-SH-sh-SCH-sch---Y-y---E-e-YU-yu-YA-ya");

        $res = str_replace($ru, $en, $text);
        $res = preg_replace("/[\s]+/ui", '-', $res);
        $res = strtolower(preg_replace("/[^0-9a-zа-я\-]+/ui", '', $res));
        return $res;
    }

    public function apiGet($link, $params = [])
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

    protected function exists($item = object, $value = null)
    {
        return isset($item->{$value}) && !empty($item->{$value});
    }

    public static function isRepetitorsPage($url = '')
    {
        return stristr($url, self::REPETITORS_SLUG);
    }

    public static function isCatalogPage($url = '')
    {
        return self::isRepetitorsPage($url) || stristr($url, self::CATALOG_SLUG);
    }

    public function getSlug($url = '')
    {
        $slug = '';

        if (!$url) {
            return $slug;
        }

        $expUrl = explode('/', $url);
        $slugWithParams = array_pop($expUrl);
        
        $urlItems = explode('?', $slugWithParams);
        $slug = array_shift($urlItems);

        return $slug;
    }
}
