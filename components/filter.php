<div <?= isset($r)? ' v-show="mapView && !listView"':'' ?> style="    margin-top: -147px;">
    <div class="catalog_filters__btns filter-submt-btn only-for-desc">
        <template v-if="filter.static.length === 0 && filter.features.length === 0 || canFilter === 0">
            <button>Назад</button>
            <button>Применить</button>
        </template>
        <template v-else>
            <button @click="clearFilters">Сбросить</button>
            <button @click="applyFilters"  class="text-gold">Применить</button>
        </template>
    </div>
    <div class="catalog_left__top filter-block filter-container-block "  :class="{ 'active': showFilters }">
      <div class="fil-heads">


       <div class="fil-inner">
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
                    <input type="checkbox" :value="staticFilters[n - 1]" @change="checkFilter" :id="staticFilters[n - 1].type" v-model="filter.static" :checked="staticFilters[n - 1].checked">
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
                    <input type="checkbox" :value="staticFilters[3]" @change="checkFilter" :id="staticFilters[3].type" v-model="filter.static" :checked="staticFilters[3].checked">
                    <label :for="staticFilters[3].type" class="check-filter-label">
                        {{staticFilters[3].name}}
                    </label>
                </div>
            </div>
        </div>
        <div class="filter-input-block">
            <p class="filter-title">Стоимость, ₽/ч</p>
            <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                <input class="filter_scroll__input" disabled type="number" v-model="minPrice.value[0]" @keyup="onChangeMinPrice" @change="onChangeMinPrice" />
                <input class="filter_scroll__input" disabled type="number" v-model="minPrice.value[1]" @keyup="onChangeMinPrice" @change="onChangeMinPrice" />
            </div>
            <div class="input-drag-ball">
                <vue-slider v-model="minPrice.value" :min="minPrice.min" :max="minPrice.max" :interval="50" @drag-end="onChangeMinPrice"></vue-slider>
            </div>
        </div>
        <div class="filter-input-block">
            <p class="filter-title">Площадь, м²</p>
            <div class="catalog_left__top--item catalog_left__top--item_view filter-two-btn">
                <input class="filter_scroll__input" disabled type="number" v-model="square.value[0]" @keyup="onChangeSquare" @change="onChangeSquare" />
                <input class="filter_scroll__input" disabled type="number" v-model="square.value[1]" @keyup="onChangeSquare" @change="onChangeSquare" />
            </div>
            <div class="input-drag-ball">
                <vue-slider v-model="square.value" :min="square.min" :max="square.max" @drag-end="onChangeSquare"></vue-slider>
            </div>
        </div>
        <div class="filter-drop-block">
            <div class="filter-drop-item" v-for="feature in features">
                <div class="filter-drop-btn filter-title">
                    {{ feature.category }}
                    <p class="text-container text-line-1"
                       v-if="checkedFilters[feature.values[0].category_id] && checkedFilters[feature.values[0].category_id].length > 0">
                        <template v-for="value in checkedFilters[feature.values[0].category_id]">
                            {{ value}},
                        </template>
                    </p>
                    <p v-else>
                        Не выбрано
                    </p>
                </div>

                <div class="filter-drop-items">
                    <div class="checkbox_custom__item check-filter-item" v-for="value in feature.values">
                        <div class='checkbox_custom'>
                            <input :id='value.feature_id'
                                   :data-category="value.category_id"
                                   type="checkbox"
                                   :data-value="value.name"
                                   :value="value"
                                   v-model="filter.features"
                                   @change="filterHandler"
                                   @checked="checked(value)"
                                   />
                            <label :for='value.feature_id' class="check-filter-label">{{ value.name
                                }}</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       </div>
        <div class="catalog_filters__btns filter-submt-btn only-for-mob">
            <template v-if="filter.static.length === 0 && filter.features.length === 0 || canFilter === 0">
                <button @click="closeFilters" >Назад</button>
                <button>Применить</button>
            </template>
            <template v-else>
                <button @click="clearFilters">Сбросить</button>
                <button @click="applyFilters"  class="text-gold">Применить</button>
            </template>
        </div>  </div>
    </div>

</div>

