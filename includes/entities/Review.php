<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Helper.php");

class Review extends Helper
{
    public function get()
    {
        if(!isset($_GET['room_id'])) {
            return false;
        }

        $reviews = $this->apiGet('reviews/list-room?room=' . $_GET['room_id']);

        foreach($reviews as $key => $review){
            if($review->rstatus == 0 || $review->rstatus == 11){
                unset($reviews[$key]);
                continue;
            }

            if($review->rstatus == 4){
                $reviews[$key]->fio .= '(изменено)';
            }

            $date = $this->setDateTime($review->date);
            $reviews[$key]->date = $date ? $date->format('d.m.Y') : null;

            $reviews[$key]->photoUrl = $review->photoUrl && $review->photoUrl != '*' ? LUCK_API_DOMAIN . IMG_PREFIX . $review->photoUrl : null;

            if($reviews[$key]->reply){
                $replyDate = $this->setDateTime($review->date);
                $reviews[$key]->reply->date = $replyDate ? $replyDate->format('d.m.Y') : null;
            }
        }

        return json_encode(['reviews' => array_values($reviews)]);
    }

    private function setDateTime($date)
    {
        $dateFormat = date("d.m.Y H:i:s", strtotime($date));
        return DateTime::createFromFormat("d.m.Y H:i:s", $dateFormat);
    }

    public function getReviewsRoom($room_id)
    {
        $reviews = $this->apiGet('reviews/list-room?room=' . $_GET['room_id']);
    }
}
