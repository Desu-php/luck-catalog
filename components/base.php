<?php require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/PageBuilder.php"); ?>
<?php $pageBuilder = new PageBuilder(); ?>
<?php $categoryData = $pageBuilder->getCategoryData(); ?>
<div id="js_info" style="display: none;" data-slug='<?php echo $categoryData['slug']; ?>' data-base_url="<?php echo wp_upload_dir()['baseurl']; ?>" data-sphere_id="<?php echo $categoryData['sphere']['sphere_id']; ?>" data-city_id="<?php echo isset($categoryData['cityId']) ? $categoryData['cityId'] : ''; ?>" data-square='<?php echo isset($categoryData['square']) ? $categoryData['square'] : '[]'; ?>' data-min_price='<?php echo isset($categoryData['minPrice']) ? $categoryData['minPrice'] : '[]'; ?>' data-features='<?php echo isset($categoryData['features']) ? $categoryData['features'] : '[]'; ?>' data-filters='<?php echo isset($categoryData['session']['filters']) ? $categoryData['session']['filters'] : '[]'; ?>' data-limit='<?php echo isset($categoryData['session']['limit']) ? $categoryData['session']['limit'] : null; ?>' data-socket_url="<?php echo LUCK_SOCKET_URL; ?>" data-widget_url="<?php echo LUCK_WIDGET_URL; ?>"></div>
<div>
    <?php
    if (function_exists('yoast_breadcrumb')) {
        yoast_breadcrumb('<div class="breadcrumbs"><row><div><span>OMg</span></div>','</row></div>');
    }
    ?>
    <div class="special-heading align-center filter-head">
        <div class="filter-head-items">
            <div class="catalog_left__top--item catalog_left__top--item_filter filter-btn">
                <button @click="toggleFilters"><img src="<?php echo plugins_url('luck-catalog/assets/img/filter-icon.png') ?>" />Фильтры
                </button>
            </div>
            <div class="filter-city">
                <span class="filter-city-title">Ваш город:</span>
                <v-select v-model="city" :options="cities" @input="changeCity" placeholder="Город" label="name"></v-select>
            </div>
            <div class="desctop-show">
                <div class='total_items total-item-filter'>
                    <span>Количество найденных вариантов: <span class="total-item-searched"> {{ basesTotal }}</span></span>
                </div>
                <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                    <button @click="onlyListView" :class='{ active: listView }'>Списком</button>
                    <button @click="onlyMapView" :class='{ active: mapView }'>На карте</button>
                </div>
            </div>
        </div>
        <?php if ($categoryData['sphere']['sphere_id'] == '0be020e3-34dd-432b-ba93-4a7cc45a2777') { ?>
            <!-- <h1 class="title title__custom-lg"><?php echo 'Лофты и ' . mb_strtolower($categoryData['sphere']['name']); ?>
                в аренду MUSbooking</h1> -->
        <?php } else { ?>
            <!-- <h1 class="title title__custom-lg"><?php echo $categoryData['sphere']['name']; ?> в аренду MUSbooking</h1> -->
            <div class="selectable-title">
                <v-select @input="changeSphere" v-model="sphere" :searchable="false" :options="spheres" label="value" placeholder="Сферы"></v-select>
            </div>
        <?php } ?>
    </div>
    <div class="container sl-katalog filter-container">
        <div class="catalog_wrap">
            <div class="catalog_wrap__left" v-show='listView || (!listView && !mapView)' :class="listView && !mapView ? 'fullwidth' : ''">
                <div id="left">
                    <div class="catalog_left__body filter-left-body">
                        <?php include 'filter.php' ?>

                        <div class="base-container-block">
                            <div class="bases_preview_wrap" v-if='bases' id="bases">
                                <template v-for="(base,index) in bases" >
                                    <div class='bases_preview__item' :key="base.base_id">
                                        <a :href="base.link" @mousedown="sendYaOnSelectBase(base)" class="base-link kalbosa" :data-key="base.base_id">
                                            <div class="base-link-head">
                                                <h3 class="base_preview__name">{{ base.sphere }} {{ base.name }}</h3>
                                                <div class="bases-count">
                                                    {{base.rooms.length + " " + getNoun(base.rooms.length,'площадка', 'площадки', 'площадок')}}
                                                </div>
                                            </div>
                                            <div class="base-link-images">
                                                <template v-for="(room, index) in base.rooms">
                                                    <template v-for="(image, image_index) in room.images">
                                                        <template v-if="base.rooms.length === 1">
                                                            <a v-show="image_index <= 2" :data-fancybox="base.base_id" :href="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/'+image + '?width=1920&height=1080'">
                                                                <img :src="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/'+image  + '?width=150&height=150'" :key="image" :alt="base.name" />
                                                            </a>
                                                        </template>
                                                        <template v-else-if="base.rooms.length === 2">
                                                            <a v-show="index === 0 && image_index === 0
                                                                        || index === 0 && image_index === 1 && base.rooms[1].images.length === 1
                                                                        || index === 1 && image_index <= 1 && room.images.length >= 1"
                                                               :data-fancybox="base.base_id" :href="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/'+image  + '?width=1920&height=1080'">
                                                                <img :src="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/'+image + '?width=150&height=150'" :key="image" :alt="base.name" />
                                                            </a>
                                                        </template>
                                                        <template v-else>
                                                            <a v-show="index <= 2 && image_index === 0" :data-fancybox="base.base_id" :href="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/'+image  + '?width=1920&height=1080'">
                                                                <img :src="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/'+image  + '?width=150&height=150'" :key="image" :alt="base.name" />
                                                            </a>
                                                        </template>
                                                    </template>
                                                </template>
                                            </div>
                                            <div class="base-info-container">
                                                <div class="base-prev-list">
                                                    <ul>
                                                        <li class='base_preview_list__item'>
                                                            <span class="base_preview_title"><img src="<?php echo wp_upload_dir()['baseurl'] . '/2019/04/baseline-location_on-24px.svg' ?>" /></span>
                                                            <span class="base_preview_value" :title="base.address">{{ base.address }}</span>
                                                        </li>
                                                        <li class='base_preview_list__item'>
                                                            <span class="base_preview_title"><img src="<?php echo wp_upload_dir()['baseurl'] . '/2019/04/metro-logo-vector-basic.svg' ?>" /></span>
                                                            <span class="base_preview_value" :title="base.metro">{{ base.metro }}</span>
                                                        </li>
                                                        <li class='base_preview_list__item'>
                                                            <span class="base_preview_title"><img src="<?php echo wp_upload_dir()['baseurl'] . '/2019/04/baseline-access_time-24px.svg' ?>" /></span>
                                                            <span class="base_preview_value">
                                                                <template v-if="base.work_time == '0-24' && base.weekend_time == '0-24'">
                                                                    <span title="круглосуточно">круглосуточно</span>
                                                                </template>
                                                                <template v-else>
                                                                    <span :title="base.work_time"><span>Буд:</span> {{ base.work_time }}</span>
                                                                    <span :title="base.weekend_time"><span>Вых:</span> {{ base.weekend_time }}</span>
                                                                </template>
                                                            </span>
                                                        </li>
                                                        <li v-if="base.hasOnlinePay  > 0" class='base_preview_list__item'>
                                                            <span class="base_preview_title"><img src="<?php echo plugins_url('luck-catalog/assets/img/pay-online.png') ?>" /></span>
                                                            <span class="base_preview_value">Оплата онлайн</span>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="base-price-info">
                                                    <div class="base-cashbak" v-if="base.bookingPointsPc > 2">
                                                        <span>вернём {{base.bookingPointsPc}}% баллами</span>
                                                    </div>
                                                    <div class="base-sale" style="display: none"><span>акция</span>
                                                    </div>
                                                    <div class="base-points" v-if="base.maxPointsPcPay > 0">
                                                        <span>принимаем {{base.maxPointsPcPay}}% баллами</span>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="otziv-price">
                                            <span class="base_preview_value base_preview_value__reviews">
                                                                <star-rating :increment="0.5" :rating="base.rating" :read-only="true" :show-rating="false" :star-size="20"></star-rating>
                                                                <span><span>{{base.reviews}} </span>отзывов</span>
                                                            </span><div class="base-price">{{base.minPrice}}-{{base.maxPrice}} ₽/ч
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </template>
                                    <div  v-for="i in 10" style="max-width: 476px;padding: 15px 4px" class="base-link bases_preview__item" :key="i" v-show="loading">
                                        <skeleton theme="opacity" shape="radius" bg-color="#dcdcdc">
                                            <row style="padding-left: 10px; border-radius: 5px;">
                                                <i-col :span="12">
                                                    <tb-skeleton  :aspect-ratio="0.05"></tb-skeleton>
                                                </i-col>
                                            </row>
                                            <row>
                                                <i-col :span="8" style="padding-left: 10px;margin-top: 5px" v-for="i in 3">
                                                    <tb-skeleton :span="20" style=";margin-top: 5px" :aspect-ratio="1" shape="square">
                                                    </tb-skeleton>
                                                </i-col>
                                            </row>
                                            <row style="margin-top: 10px">
                                                <i-col :span="12" v-for="i in 2">
                                                    <i-col :span="24" style="padding-left: 10px; margin-top: 15px" v-for="i in 4">
                                                        <tb-skeleton :aspect-ratio="0.05"></tb-skeleton>
                                                    </i-col>
                                                </i-col>
                                            </row>
                                        </skeleton>
                                    </div>
                            </div>
                            <div class="catalog_left__footer" v-show="listView || (!listView && !mapView)">
                                <button v-if="isHideShowMore" @click="showMore">Показать еще</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="catalog_wrap__right filter-map" v-show="mapView || (!listView && !mapView)" :class="mapView && !listView ? 'fullwidth' : ''">
                <?php
                $r = true;
                include 'filter.php'
                ?>
                <div id="right">
                    <div class='base-details__item base-details__map filter-map-inner'>
                        <div class="base-details__item-inner">
                            <div id="katalog-map" style="position: relative; height: 430px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="category-description_wrap">
        <?php echo category_description(); ?>
    </div>
</div>
