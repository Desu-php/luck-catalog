<?php require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/PageBuilder.php"); ?>
<?php $pageBuilder = new PageBuilder(); ?>
<?php $catalogData = $pageBuilder->getCatalogRoomData(false, true); ?>
<?php
function getNoun($number, $one, $two, $five)
{
    $n = ceil($number);
    $n %= 100;
    if ($n >= 5 && $n <= 20) {
        return $five;
    }
    $n %= 10;
    if ($n === 1) {
        return $one;
    }
    if ($n >= 2 && $n <= 4) {
        return $two;
    }
    return $five;
}

$helper = new Helper();
$reviews = $helper->getRequest('https://hendrix.musbooking.com/api/reviews/list-base?base='.$catalogData['base']['base_id']);
$room = new Room();
$rooms = $room->getRoomsWithBaseId($catalogData['base']['base_id']);
$images = [];
foreach ($rooms as $key => $room) {
    $rooms[$key]['images'] = json_decode($room['images']);
    $images = array_merge($rooms[$key]['images'], $images);
}
$order_text = 'Оставить заявку';
if ($catalogData['base']['isRequest'] == 0) {
    $order_text = 'забронировать';
}
?>
<!--<modal v-if="modalShow" @close="modalShow = false" width="width:400px">-->
<!--    <div slot="body">-->
<!--        <div class="desu-modal-img">-->
<!--            <img src="https://widget.musbooking.com/catalog/view/image/Logo_mb_start.png" alt="MusBooking">-->
<!--        </div>-->
<!--        <form @submit.prevent="sendOrder">-->
<!--            <label for="name">Имя</label>-->
<!--            <input type="text" id="name" placeholder="Имя" class="desu-input" required>-->
<!--            <label for="telephone">Телефон</label>-->
<!--            <div class="desu-form-phone">-->
<!--                <span>+7</span>-->
<!--                <input type="tel" id="telephone" v-maska="'##########'" v-model="phone" placeholder="Телефон"-->
<!--                       class="desu-input" required>-->
<!--            </div>-->
<!--            <span style="color: red" v-if="phoneError">Номер телефона не правильного формата</span>-->
<!--            <div class="object-area__btn" style="text-align: end">-->
<!--                <button type="submit">Отправить</button>-->
<!--            </div>-->
<!--        </form>-->
<!--    </div>-->
<!--</modal>-->

<modal v-if="modalShow" @close="modalShow = false" width="width:400px">
    <div slot="body">
        <div class="desu-modal-img">
            <img v-if="equipment.image.indexOf('https://')===-1"
                 :src="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/res/' + equipment.image + '?width=90&height=90'"
                 :alt="equipment.description">
            <img v-else
                 :src="'https://aosnxmcciq.cloudimg.io/v7/' + equipment.image + '?width=90&height=90'"
                 :alt="equipment.description">
        </div>
        <div class="equipment_description">
            {{equipment.description}}
        </div>
    </div>
</modal>

<style>
    @media (max-width: 780px) {
        .breadcrumbs {
            display: none;
        }
    }
</style>
<div id="js_info" style="display: none;"
     data-socket_url="<?php echo LUCK_SOCKET_URL; ?>"
     data-widget_url="<?php echo LUCK_WIDGET_URL; ?>"
     data-sphere_id="<?php echo $catalogData['base']['sphere_id']; ?>"
     data-base_id="<?php echo $catalogData['base']['base_id']; ?>">
</div>
<div class="section full-width">
    <div class="container-w1450">
        <div class="special-heading title">
            <h1 class="title title__custom-lg title__custom__with_btn"
                style="color: black"><?php echo get_the_title(); ?></h1>
        </div>
        <div class="vc_row">
            <div class="wpb_column vc_column_container">
                <div class="wpb_wrapper my-wrapper">
                    <div class="base-details">
                        <div class='base-details__item base-details__address'>
                            <div class='base-details__item-inner details-inner change'>
                                <div class='address-item change'>
                                    <span class='address-item__title'><img
                                                src="<?php echo wp_upload_dir()['baseurl'] . '/2019/04/baseline-location_on-24px.svg' ?>"/></span>
                                    <span class='address-item__value'><a
                                                href="https://maps.yandex.ru/?text=<?php echo $catalogData['base']['gpsLat']; ?>,<?php echo $catalogData['base']['gpsLong']; ?>&z=16&l=map"
                                                target="_blank"
                                                onClick="yaCounter41615739.reachGoal('go_to_cart', {sphere_id: '<?php echo $catalogData['base']["sphere_id"]; ?>'})"><?php echo $catalogData['base']['address']; ?></a></span>
                                </div>
                                <div class='address-item change'>
                                    <span class='address-item__title'><img
                                                src="<?php echo wp_upload_dir()['baseurl'] . '/2019/04/metro-logo-vector-basic.svg' ?>"/></span>
                                    <span class='address-item__value'><?php echo $catalogData['base']['metro']; ?></span>
                                </div>
                                <div class='address-item change address-item__time'>
                                    <span class='address-item__title'><img
                                                src="<?php echo wp_upload_dir()['baseurl'] . '/2019/04/baseline-access_time-24px.svg' ?>"/></span>
                                    <?php
                                    if ($catalogData['base']['work_time'] == 'Круглосуточно' && $catalogData['base']['weekend_time'] == 'Круглосуточно') {
                                        $work_time = 'Работает круглосуточно';
                                    } else {
                                        $work_time = '<span>Будни:</span> ' . $catalogData['base']['work_time'] . ' <span>Выходные:</span> ' . $catalogData['base']['weekend_time'];
                                    }
                                    ?>
                                    <span class='address-item__value'><?= $work_time ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <section class="main-img">
                        <div class="main-img__item">
                            <a class="fancy-box-gallery" data-fancybox="images"
                               href="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= isset($images[0]) ? $images[0] : '' ?>">
                                <img src="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= isset($images[0]) ? $images[0] : '' ?>"
                                     alt="img">
                            </a>
                        </div>
                        <div class="main-img__col">
                            <a class="main-img__col-item fancy-box-gallery" data-fancybox="images"
                               href="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= isset($images[1]) ? $images[1] : '' ?>">
                                <img src="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= isset($images[1]) ? $images[1] : '' ?>"
                                     alt="img">
                            </a>
                            <a class="main-img__col-item fancy-box-gallery" data-fancybox="images"
                               href="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= isset($images[2]) ? $images[2] : '' ?>">
                                <?php if (count($images) - 3 > 0): ?>
                                    <div class="main-img__col-text">
                                        <p>ещё <?= count($images) - 3 ?> фото</p>
                                    </div>
                                <?php endif; ?>
                                <img src="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= isset($images[2]) ? $images[2] : '' ?>"
                                     alt="img">
                            </a>
                        </div>
                        <?php foreach ($images as $key => $image) : ?>
                            <?php if ($key > 2): ?>
                                <a style="display: none" class="fancy-box-gallery" data-fancybox="images"
                                   href="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= $image ?>">
                                    <img src="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= $image ?>"
                                         alt="img">
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </section>
                    <section class="object-area">
                        <h2 class="object-area__title title">Площадки объекта</h2>
                        <div class="object-area__inner">
                            <?php foreach ($rooms as $key => $room): ?>
                                <?php $urls = $helper->apiGet('rooms/search2?room=' . $room['room_id']) ?>
                                <!--                            --><?php //var_dump($urls->rooms[0]->urls);  ?>
                                <div class="container_object_area_line">
                                    <div class="object-area__line" id="room<?= $key ?>">
                                        <div class="object-area__size object-area__col">
                                            <div>
                                                <h3 class="object-area__size-title"><?= $room['name'] ?></h3>
                                                <?php if ($room['reviews_count'] > 0): ?>
                                                    <div class="reviews_values">
                                                        <star-rating :increment="0.5"
                                                                     :rating="<?= $room['reviews_value'] ?>"
                                                                     :read-only="true" :show-rating="false"
                                                                     :star-size="20"
                                                                     active-color="#EC8000"
                                                        >

                                                        </star-rating>
                                                        <div class="reviews_text"><?= $room['reviews_count'] ?> <?= getNoun($room['reviews_count'], 'отзыв', 'отзыва', 'отзывов') ?></div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="object-area__size-num"><?= $room['square'] ?> м²</div>
                                        </div>
                                        <div class="object-area__images object-area__col">
                                            <?php foreach ($room['images'] as $image): ?>
                                                <div class="object-area__images-item">
                                                    <a data-fancybox="<?= $key ?>"
                                                       href="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= $image ?>?width=1920&height=1080"
                                                       class="fancy-box-gallery">
                                                        <img src="https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/<?= $image ?>?width=122&height=69"
                                                             alt="img">
                                                    </a>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="object-area__about object-area__col">
                                            <div class="text-container text-line-4">
                                                <span style="font-weight: bold">О площадке</span><br>
                                                <?= nl2br($room['raider']) ?>
                                            </div>
                                            <button data-open-text="еще" data-close-text="Свернуть" class="minimize-btn"
                                                    data-parent="#room<?= $key ?>">еще
                                            </button>
                                        </div>
                                        <div class="object-area__btn">
                                            <button @click="ShowModal"
                                                    data-room_id="<?= $room['room_id'] ?>"
                                                    data-remodal-target="vue-modal"
                                                    class="desktop-order"

                                            ><?= $order_text ?></button>
                                            <a class="mobile-order"
                                               href="https://widget.musbooking.com/?room=<?= $room['room_id'] ?>&source=1&optionChange=1&rg=1">
                                                <?= $order_text ?>
                                            </a>
                                        </div>
                                    </div>
                                    <?php if (!empty($urls->rooms[0]->urls)): ?>
                                        <div class="urls_container">
                                            <?php foreach ($urls->rooms[0]->urls as $url): ?>
                                                <a class="urls_btn"
                                                   href="<?= $url->value ?>"><?= $url->description ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    <!--                    --><?php //preg_match_all('/\./', $catalogData['base']['description'], $matches) ?>
                    <!--                    --><?php //var_dump($matches); ?>
                    <section class="description" id="description_base">
                        <h2 class="description__title title">Описание</h2>
                        <div class="text-container text-line-7">
                            <?= $catalogData['base']['description'] ?>
                        </div>
                        <a href="#" data-open-text="Читать полностью" data-close-text="Свернуть" class="minimize-btn"
                           data-parent="#description_base">Читать полностью</a>
                    </section>
                    <section class="equipment" v-if="Object.keys(equipments).length">
                        <h2 class="equipment__title title">
                            Оборудование и услуги
                        </h2>
                        <div class="equipment__line" v-for="(equipment, groupName) in equipments">
                            <div class="equipment__line-title">{{groupName}}</div>
                            <div class="equipment__line-items">
                                <div class="equipment-item" v-for="item in equipment" :key="item.id"
                                     @click="showEquipmentDescription(item.description,item.photoUrl)">
                                    <div class="equipment-item__img">
                                        <img v-if="item.photoUrl.indexOf('https://')===-1"
                                             :src="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/res/' + item.photoUrl + '?width=90&height=90'"
                                             :alt="item.name">
                                        <img v-else
                                             :src="'https://aosnxmcciq.cloudimg.io/v7/' + item.photoUrl + '?width=90&height=90'"
                                             :alt="item.name">
                                    </div>
                                    <div class="equipment-item__title">{{item.name}}</div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php if (count($reviews) > 0): ?>
                        <section class="base_reviews_section">
                            <h2 class="equipment__title title">
                                Отзывы о площадке
                            </h2>
                            <?php foreach ($reviews as $review): ?>
                                <div class="base_reviews_container mt-28">
                                    <div>
                                        <div class="base_reviews_avatar_container">
                                            <img src="<?= (empty($review->photoUrl) || $review->photoUrl == '*' )?plugins_url() . '/luck-catalog/assets/img/reviews_placeholder.jpg':'https://hendrix.musbooking.com/res/'.$review->photoUrl?>"
                                                 alt="avatar">
                                        </div>
                                    </div>
                                    <div class="base_reviews_item_container">
                                        <div class="base_reviews_info_header">
                                            <div><?=$review->name?>, <?=date('m.d.Y', strtotime($review->date))?></div>
                                            <div>
                                                <svg class="reviews_icon" viewBox="0 0 20 20" fill="none"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M18.3 6.99995H12.9L11.2 1.59995C10.8 0.399951 9.20005 0.399951 8.80005 1.59995L7.10005 6.99995H1.70005C0.500049 6.99995 4.8697e-05 8.49995 1.00005 9.19995L5.40005 12.6L3.70005 18.1C3.30005 19.3 4.70005 20.2 5.70005 19.5L10 16.1L14.3 19.4C15.3 20.1 16.6 19.2 16.3 18L14.6 12.5L19 9.09995C20 8.49995 19.5 6.99995 18.3 6.99995Z"
                                                          fill="#EC8000"/>
                                                </svg>
                                                <?=$review->value?>
                                            </div>
                                            <div>
                                                <?php foreach ( $rooms as $room){
                                                    if ($room['room_id'] == $review->roomId){
                                                        $room['name'];
                                                    }
                                                } ?>

                                            </div>
                                        </div>

                                        <div class="base_reviews_text">
                                            <?=$review->text?>
                                        </div>
                                        <?php if (!is_null($review->reply)): ?>
                                        <div class="base_reviews_container mt-28">
                                            <div>
                                                <div class="base_reviews_avatar_container">
                                                    <img src="<?= plugins_url() . '/luck-catalog/assets/img/reviews_placeholder.jpg' ?>"
                                                         alt="avatar">
                                                </div>
                                            </div>
                                            <div class="object_reviews_text_container">
                                                <div class="object_reviews_title"><?=$review->reply->sender?>, <?=date('m.d.Y', strtotime($review->reply->date))?></div>
                                                <div class="object_reviews_thx">
                                                    <?=$review->reply->text?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </section>
                    <?php endif; ?>

                    <!---->
                    <!--                    --><?php //if (!$catalogData['base']['isArchive']) { ?>
                    <!---->
                    <!--                        --><?php //if ($catalogData['base']['isRequest']) { ?>
                    <!--                        --><?php //} else { ?>
                    <!--                            <div class="widget-block">-->
                    <!--                                                    <iframe v-if="rooms.length > 0" id="widget"-->
                    <!--                                                            :src="`-->
                    <!--                    -->
                    <?php //echo LUCK_WIDGET_URL; ?><!--/?room=${rooms[0]['room_id']}&source=1&optionChange=1&disableLinkLogo=1`"-->
                    <!--                                                            width="1366" height="768">-->
                    <!--                                                        Ваш браузер не поддерживает плавающие фреймы!-->
                    <!--                                                    </iframe>-->
                    <!--                            </div>-->
                    <!--                        --><?php //} ?>
                    <!--                    --><?php //} ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!--<div data-remodal-id="vue-modal" class="remodal remodal-is-initialized remodal-is-closed" tabindex="-1"-->
<!--     data-remodal-options="hashTracking: false, closeOnOutsideClick: false, stack:true">-->
<!--    <button data-remodal-action="close" class="remodal-close"></button>-->
<!--    <div class="widget-block">-->
<!--        <iframe id="widget" :src="`--><?php //echo LUCK_WIDGET_URL; ?><!--?room=${room_id}&source=1&optionChange=1&disableLinkLogo=1&rg=1`"-->
<!--                width="1366" height="768">-->
<!--            Ваш браузер не поддерживает плавающие фреймы!-->
<!--        </iframe>-->
<!--    </div>-->
<!--</div>-->

<modal v-show="modalOrderShow" @close="closeModal" width="width:100%">
   <div slot="body" style="height: 100vh; overflow-y: scroll">
       <div class="widget-block">
           <iframe v-if="rooms.length > 0" id="widget"
                   :src="`
                <?php echo LUCK_WIDGET_URL; ?>/?room=${room_id}&source=1&optionChange=1&disableLinkLogo=1&rg=1`"
                   width="1366" height="768">
               Ваш браузер не поддерживает плавающие фреймы!
           </iframe>
       </div>
   </div>
</modal>

<script type="text/x-template" id="modal-template">
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container" style="position: relative;" :style="width">
                    <span @click="$emit('close')" class="modal-close"><i class="fa fa-times"
                                                                         aria-hidden="true"></i></span>
                    <div class="modal-header">
                        <slot name="header">

                        </slot>
                    </div>

                    <div class="modal-body">
                        <slot name="body">

                        </slot>
                    </div>

                    <div class="modal-footer">
                        <slot name="footer">

                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</script>


