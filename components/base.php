<?php require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/PageBuilder.php"); ?>
<?php $pageBuilder = new PageBuilder(); ?>
<?php $categoryData = $pageBuilder->getCategoryData(); ?>
<div id="js_info" style="display: none;" data-slug='<?php echo $categoryData['slug']; ?>' data-base_url="<?php echo wp_upload_dir()['baseurl']; ?>" data-sphere_id="<?php echo $categoryData['sphere']['sphere_id']; ?>" data-city_id="<?php echo isset($categoryData['cityId']) ? $categoryData['cityId'] : ''; ?>" data-square='<?php echo isset($categoryData['square']) ? $categoryData['square'] : '[]'; ?>' data-min_price='<?php echo isset($categoryData['minPrice']) ? $categoryData['minPrice'] : '[]'; ?>' data-features='<?php echo isset($categoryData['features']) ? $categoryData['features'] : '[]'; ?>' data-filters='<?php echo isset($categoryData['session']['filters']) ? $categoryData['session']['filters'] : '[]'; ?>' data-limit='<?php echo isset($categoryData['session']['limit']) ? $categoryData['session']['limit'] : null; ?>' data-socket_url="<?php echo LUCK_SOCKET_URL; ?>" data-widget_url="<?php echo LUCK_WIDGET_URL; ?>"></div>
<div id="katalog-vue">
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
                        <div class="filter-container-block" :class="{ 'active': showFilters }">
                            <div class="catalog_left__top filter-block">
                                <div class="mobile-show">
                                    <div class='total_items total-item-filter'>
                                        <span>Количество найденных вариантов: <span class="total-item-searched"> {{ basesTotal }}</span></span>
                                    </div>
                                    <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                                        <button @click="onlyListView" :class='{ active: listView }'>Списком</button>
                                        <button @click="onlyMapView" :class='{ active: mapView }'>На карте</button>
                                    </div>
                                </div>
                                <div class="catalog_left__top--item catalog_left__top--item_search filter-search-p">
                                    <p class="filter-title">
                                        Поиск по названию
                                    </p>
                                    <v-select v-model="partner" :options="basesSelect" @input="sendYaAndGoToBase(partner)" @change="location = partner.link" placeholder="Название площадки" label="name"></v-select>
                                </div>
                                <div class="sort-items" :class="sort.class">
                                    <p class="filter-title">Сортировать</p>
                                    <v-select v-model="sort" :options="sortItems" @input="changeSort" placeholder="по возрастанию цены" label="name"></v-select>
                                </div>
                                <div class="filter-check-block">
                                    <p class="filter-title">
                                        Быстрое бронирование
                                    </p>
                                    <div class="check-filter-item" v-for="n in 3">
                                        <div class="checkbox_custom">
                                            <input type="checkbox" :value="staticFilters[n - 1]" :id="staticFilters[n - 1].type" v-model="filter.static" :checked="staticFilters[n - 1].checked">
                                            <label :for="staticFilters[n - 1].type" class="check-filter-label">
                                                {{staticFilters[n - 1].name}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-check-block">
                                    <p class="filter-title">
                                        Акции и скидки
                                    </p>
                                    <div class="check-filter-item">
                                        <div class="checkbox_custom">
                                            <input type="checkbox" :value="staticFilters[3]" :id="staticFilters[3].type" v-model="filter.static" :checked="staticFilters[3].checked">
                                            <label :for="staticFilters[3].type" class="check-filter-label">
                                                {{staticFilters[3].name}}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="filter-input-block">
                                    <p class="filter-title">Стоимость, ₽/ч</p>
                                    <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                                        <input class="filter_scroll__input" type="number" v-model="minPrice.value[0]" @keyup="onChangeMinPrice" @change="onChangeMinPrice" />
                                        <input class="filter_scroll__input" type="number" v-model="minPrice.value[1]" @keyup="onChangeMinPrice" @change="onChangeMinPrice" />
                                    </div>
                                    <div class="input-drag-ball">
                                        <vue-slider v-model="minPrice.value" :min="minPrice.min" :max="minPrice.max" :interval="50" @drag-end="onChangeMinPrice"></vue-slider>
                                    </div>
                                </div>
                                <div class="filter-input-block">
                                    <p class="filter-title">Площадь, м²</p>
                                    <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                                        <input class="filter_scroll__input" type="number" v-model="square.value[0]" @keyup="onChangeSquare" @change="onChangeSquare" />
                                        <input class="filter_scroll__input" type="number" v-model="square.value[1]" @keyup="onChangeSquare" @change="onChangeSquare" />
                                    </div>
                                    <div class="input-drag-ball">
                                        <vue-slider v-model="square.value" :min="square.min" :max="square.max" @drag-end="onChangeSquare"></vue-slider>
                                    </div>
                                </div>
                                <div class="filter-drop-block">
                                    <div class="filter-drop-item" v-for="feature in features">
                                        <div class="filter-drop-btn filter-title">
                                            {{ feature.category }}
                                        </div>
                                        <div class="filter-drop-items">
                                            <div class="checkbox_custom__item check-filter-item" v-for="value in feature.values">
                                                <div class='checkbox_custom'>
                                                    <input :id='value.feature_id' type="checkbox" :value="value" v-model="filter.features" :checked="value.checked" />
                                                    <label :for='value.feature_id' class="check-filter-label">{{
                                                        value.name }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="catalog_filters__btns filter-submt-btn">
                                <button @click="clearFilters">Сбросить</button>
                                <button @click="applyFilters" v-if="filter.static.length > 0 || filter.features.length > 0" class="text-gold">Применить</button>
                                <button @click="applyFilters" v-else>Применить</button>
                            </div>
                        </div>
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
                                                            <a v-show="index <= 0 && image_index === 0 ||index === 1 && image_index <= 1 " :data-fancybox="base.base_id" :href="'https://aosnxmcciq.cloudimg.io/v7/https://partner.musbooking.com/files/res/'+image  + '?width=1920&height=1080'">
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
                                                                    <span :title="base.work_time"><span>Будни:</span> {{ base.work_time }}</span>
                                                                    <span :title="base.weekend_time"><span>Выходные:</span> {{ base.weekend_time }}</span>
                                                                </template>
                                                            </span>
                                                        </li>
                                                        <li v-if="base.hasOnlinePay  > 0" class='base_preview_list__item'>
                                                            <span class="base_preview_title"><img src="<?php echo plugins_url('luck-catalog/assets/img/pay-online.png') ?>" /></span>
                                                            <span class="base_preview_value">Оплата онлайн</span>
                                                        </li>
                                                        <!-- <li class='base_preview_list__item'>

                                                        </li> -->
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="catalog_wrap__right filter-map" v-show="mapView || (!listView && !mapView)" :class="mapView && !listView ? 'fullwidth' : ''">
                <div class="catalog_left__top filter-block filter-container-block " v-show="mapView && !listView" :class="{ 'active': showFilters }">
                    <div class="mobile-show">
                        <div class='total_items total-item-filter'>
                            <span>Количество найденных вариантов: <span class="total-item-searched"> {{ basesTotal }}</span></span>
                        </div>
                        <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                            <button @click="onlyListView" :class='{ active: listView }'>Списком</button>
                            <button @click="onlyMapView" :class='{ active: mapView }'>На карте</button>
                        </div>
                    </div>
                    <div class="catalog_left__top--item catalog_left__top--item_search filter-search-p">
                        <p class="filter-title">
                            Поиск по названию
                        </p>
                        <v-select v-model="partner" :options="basesSelect" @input="sendYaAndGoToBase(partner)" @change="location = partner.link" placeholder="Название площадки" label="name"></v-select>
                    </div>
                    <!-- <div class="filter_line"></div> -->
                    <div class="sort-items" :class="sort.class">
                        <p class="filter-title">Сортировать</p>
                        <v-select v-model="sort" :options="sortItems" @input="changeSort" placeholder="по возрастанию цены" label="name"></v-select>
                    </div>
                    <div class="filter-check-block">
                        <p class="filter-title">
                            Быстрое бронирование
                        </p>
                        <div class="check-filter-item" v-for="n in 3">
                            <div class="checkbox_custom">
                                <input type="checkbox" :value="staticFilters[n - 1]" :id="staticFilters[n - 1].type" v-model="filter.static" :checked="staticFilters[n - 1].checked">
                                <label :for="staticFilters[n - 1].type" class="check-filter-label">
                                    {{staticFilters[n - 1].name}}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="filter-check-block">
                        <p class="filter-title">
                            Акции и скидки
                        </p>
                        <div class="check-filter-item">
                            <div class="checkbox_custom">
                                <input type="checkbox" :value="staticFilters[3]" :id="staticFilters[3].type" v-model="filter.static" :checked="staticFilters[3].checked">
                                <label :for="staticFilters[3].type" class="check-filter-label">
                                    {{staticFilters[3].name}}
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="filter-input-block">
                        <p class="filter-title">Стоимость, ₽/ч</p>
                        <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                            <input class="filter_scroll__input" type="number" v-model="minPrice.value[0]" @keyup="onChangeMinPrice" @change="onChangeMinPrice" />
                            <input class="filter_scroll__input" type="number" v-model="minPrice.value[1]" @keyup="onChangeMinPrice" @change="onChangeMinPrice" />
                        </div>
                        <div class="input-drag-ball">
                            <vue-slider v-model="minPrice.value" :min="minPrice.min" :max="minPrice.max" :interval="50" @drag-end="onChangeMinPrice"></vue-slider>
                        </div>
                    </div>
                    <div class="filter-input-block">
                        <p class="filter-title">Площадь, м²</p>
                        <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                            <input class="filter_scroll__input" type="number" v-model="square.value[0]" @keyup="onChangeSquare" @change="onChangeSquare" />
                            <input class="filter_scroll__input" type="number" v-model="square.value[1]" @keyup="onChangeSquare" @change="onChangeSquare" />
                        </div>
                        <div class="input-drag-ball">
                            <vue-slider v-model="square.value" :min="square.min" :max="square.max" @drag-end="onChangeSquare"></vue-slider>
                        </div>
                    </div>
                    <div class="filter-drop-block">
                        <div class="filter-drop-item" v-for="feature in features">
                            <div class="filter-drop-btn filter-title">
                                {{ feature.category }}
                            </div>
                            <div class="filter-drop-items">
                                <div class="checkbox_custom__item check-filter-item" v-for="value in feature.values">
                                    <div class='checkbox_custom'>
                                        <input :id='value.feature_id' type="checkbox" :value="value" v-model="filter.features" :checked="value.checked" />
                                        <label :for='value.feature_id' class="check-filter-label">{{ value.name
                                            }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="catalog_filters__btns filter-submt-btn">
                        <button @click="clearFilters">Сбросить</button>
                        <button @click="applyFilters" v-if="filter.static.length > 0 || filter.features.length > 0" class="text-gold">Применить</button>
                        <button @click="applyFilters" v-else>Применить</button>
                    </div>
                    <!--                    <div class='selected_filters'>-->
                    <!--                        <div class="selected_filters__item" v-for="feature in filter.features">-->
                    <!--                            <div class="selected_filters__item--inner">-->
                    <!--                                <span>{{ feature.name }}</span>-->
                    <!--                                <a @click="removeFilter(feature, 'features')">x</a>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        <div class="selected_filters__item" v-for="filter in filter.static">-->
                    <!--                            <div class="selected_filters__item--inner">-->
                    <!--                                <span>{{ filter.name }}</span>-->
                    <!--                                <a @click="removeFilter(filter, 'static')">x</a>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                </div>
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
