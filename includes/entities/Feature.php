<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");

class Feature extends Helper
{
    public function get()
    {
        if(!isset($_GET['sphere_id']) || !isset($_GET['city_id'])) {
            wp_die();
        }

        $results = $this->apiGet('rooms/search2', [
            'sphere' => $_GET['sphere_id'],
            'city' => $_GET['city_id']
        ]);
        
        return json_encode(['features' => $this->doDistribute($results->features)]);
    }

    private function doDistribute($features)
    {
        $results = [];

        foreach($features as $feature) {
            $results[$feature->category] = isset($results[$feature->category]) ? $results[$feature->category] : [
                'category' => $feature->category,
                'values' => []
            ];

            $results[$feature->category]['values'][] = [
                'feature_id' => $feature->id,
                'category_id' => $feature->categoryId,
                'name' => $feature->name,
            ];
        }
        
        return array_values($results);
    }
}
