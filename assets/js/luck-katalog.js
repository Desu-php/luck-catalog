if (jQuery('#katalog-vue').length > 0) {
    jQuery('body').on('click', '.filter-title', function () {
        jQuery(this).toggleClass('active')
    })
    new Vue({
        el: '#katalog-vue',
        data: () => ({
            city: {city_id: null, name: 'Город'},
            sphere: {id: null, value: 'Сфера'},
            cities: [],
            spheres: [],
            features: [],
            roomsImages: [],
            geoObjects: [],
            isOnlinePayment: false,
            sort: {name: 'Без сортировки', class: ''},
            loading: false,
            load: true,
            sortItems: [
                {name: 'По умолчанию', class: ''},
                {name: 'По возрастанию цены', order: 'minPrice', direction: 'ASC', class: 'gold-icon'},
                {name: 'По убыванию цены', order: 'minPrice', direction: 'DESC', class: 'gold-icon'},
                {name: 'По возрастанию рейтинга', order: 'rating', direction: 'ASC', class: 'gold-icon'},
                {name: 'По убыванию рейтинга', order: 'rating', direction: 'DESC', class: 'gold-icon'},
            ],
            square: {
                min: 0,
                max: 10000,
                value: [0, 0],
                timer: null
            },
            minPrice: {
                interval: 50,
                min: 0,
                max: 10000,
                value: [0, 0],
                timer: null,
            },
            staticFilters: [
                {type: 'hasPrepay', name: 'Без предоплаты'},
                {type: 'isRequest', name: 'Без заявки'},
                {type: 'hasOnlinePay', name: 'Оплата онлайн'},
                {type: 'hasPointsPay', name: 'Принимает баллы'}
            ],
            showFilters: false,
            filter: {
                static: [],
                features: [],
            },
            basesTotal: 0,
            bases: [],
            basesMap: [],
            basesSelect: [],
            partner: {name: 'Площадка'},
            mapView: false,
            listView: jQuery(window).width() <= 1024 ? true : false,
            limit: 10
        }),
        methods: {
            getNoun(number, one, two, five) {
                let n = Math.abs(number);
                n %= 100;
                if (n >= 5 && n <= 20) {
                    return five;
                }
                n %= 10;
                if (n === 1) {
                    return one;
                }
                if (n >= 2 && n <= 4) {
                    return two;
                }
                return five;
            },
            sortingByPc(items) {
                const firstItems = []
                const secondItems = []
                if (this.sort.name === 'Без сортировки') {
                    for (const item of items) {
                        if (item.bases_list[0].bookingPointsPc > 2) {
                            firstItems.push(item)
                        } else {
                            secondItems.push(item)
                        }
                    }
                }
                return [...firstItems, ...secondItems]
            },
            canLoadMore() {
                return this.bases.length < this.basesTotal
            },
            changeSphere(sphere) {
                const params = {
                    action: 'get_url_sphere',
                    sphere_id: sphere.id
                }
                axios.get(AJAX_URL, {params: params}).then(response => {
                    window.location.href = response.data
                })
            },
            sendYaOnSelectBase(base) {
                yaCounter41615739.reachGoal('selected_base_from_list', {sphere_id: base.sphere_id})
            },
            dd(item) {
                console.log(item)
            },
            sendYaAndGoToBase(base) {
                yaCounter41615739.reachGoal('selected_base')
                location = base.link
            },
            changeCity(city) {
                this.city = city

                this.changeTitle()
                this.showPreloader()

                history.pushState({}, '', SLUG + '?city=' + this.city.slug)

                let params = {
                    city_id: city.city_id,
                    slug: SLUG,
                    action: 'get_city_session'
                }

                axios.get(AJAX_URL, {params: params}).then(response => {
                    SESSION_DATA.features = response.data.features
                    SESSION_DATA.square = response.data.square
                    SESSION_DATA.minPrice = response.data.minPrice

                    let squareInterval = this.loadSquareInterval()
                    let minPriceInterval = this.loadMinPriceInterval()
                    let filters = this.setFilters()

                    Promise.all([squareInterval, minPriceInterval, filters]).then(() => {
                        this.setTotalBases()
                        let bases = this.setBases()
                        let map = this.setMap()
                        yaCounter41615739.reachGoal('selected_city')

                        Promise.all([bases, map]).then(() => {
                            setTimeout(() => this.hidePreloader(), 2000)
                        })
                    })
                })
            },
            changeTitle() {
                if (this.city.city_id == '898f482f-5fdd-4192-b4ed-d157e6952bc6' && SPHERE_ID == '36e5c7bc-f45c-4251-9552-456664c55c22') {
                    jQuery('title').text('Танцевальные залы Каталог Санкт-Петербург')
                } else {
                    jQuery('title').text(SPHERE_TITLES[SPHERE_ID])
                }
            },
            countTotalBasesByCity() {
                let totalBasesParams = {
                    city_id: city.city_id,
                    action: 'get_city_session'
                }

                axios.get(AJAX_URL, {params: totalBasesParams}).then(response => {

                })
            },
            changeSort() {
                this.showPreloader()
                let bases = this.setBases()
                let map = this.setMap()

                Promise.all([bases, map]).then(() => {
                    this.hidePreloader()
                })
            },
            setTotalBases() {
                let totalParams = {
                    action: 'get_total_bases',
                    square: this.square.value,
                    minPrice: this.minPrice.value,
                    filters: this.filter.static.map(item => item.type),
                    features: this.filter.features.map(item => item.feature_id),
                    sphere: SPHERE_ID,
                    city: this.city.city_id,
                }

                axios.get(AJAX_URL, {params: totalParams}).then(response => {
                    this.basesTotal = response.data.total
                })
            },
            setCities() {
                let params = {action: 'get_cities_katalog'}
                return axios.get(AJAX_URL, {params: params}).then(response => {
                    this.cities = response.data.cities
                    this.city = SESSION_DATA.city ? response.data.cities.find(city => city.city_id == SESSION_DATA.city) : response.data.city
                    history.pushState({}, '', SLUG + '?city=' + this.city.slug)
                })
            },
            setSpheres() {
                return axios.get('https://hendrix.musbooking.com/api/spheres/search')
                    .then(response => {
                        this.spheres = response.data
                        this.sphere = response.data.find(item => item.id === SPHERE_ID)
                    })
            },
            setRoomsImages(base_id) {
                const params = {
                    base: base_id
                }
                return axios.get('https://hendrix.musbooking.com/api/rooms/search2', {params: params})
                    .then(response => {
                        return response.data.rooms
                    })
            },
            setBases() {
                return axios.get(AJAX_URL, {params: this.getBasesParams(null, false, true, 'get_bases_katalog_with_rooms')}).then(async response => {
                    const bases = response.data
                    if (bases) {
                        for (const key in bases) {
                            bases[key].rating = Number(bases[key].rating)
                            for (const index in bases[key]['rooms']) {
                                bases[key]['rooms'][index]['images'] = JSON.parse(bases[key]['rooms'][index]['images'])
                            }
                        }
                        this.bases = bases
                    }
                    return response.data
                })
            },
            setBasesMap() {
                return axios.get(AJAX_URL, {params: this.getBasesParams(-1, false, true)}).then(response => {
                    this.basesMap = response.data.bases ? response.data.bases.map(base => {
                        base.rating = Number(base.rating)
                        return base
                    }) : []

                    this.basesSelect = this.basesMap.sort((a, b) => (a.name > b.name) ? 1 : -1)
                    return response.data
                })
            },
            setFilters() {
                let params = {
                    action: 'get_features_katalog',
                    sphere_id: SPHERE_ID,
                    city_id: this.city.city_id
                }

                return axios.get(AJAX_URL, {params: params}).then(response => {
                    this.features = response.data.features

                    if (SESSION_DATA.features && SESSION_DATA.features.length > 0) {
                        this.features.map(feature => {
                            let selectedFilterItem = feature.values.find(value => SESSION_DATA.features.some(filter => value.feature_id == filter))
                            if (selectedFilterItem) {
                                this.filter.features.push(selectedFilterItem)
                            }
                        })
                    }

                    if (SESSION_DATA.filters && SESSION_DATA.filters.length > 0) {
                        this.filter.static = this.staticFilters.filter(filter => SESSION_DATA.filters.some(sessionFilter => sessionFilter == filter.type))
                    }
                })
            },
            setMap() {
                return this.setBasesMap().then(() => {
                    this.initMap()
                })
            },
            initMap() {
                ymaps.ready(() => {
                    jQuery('#katalog-map').replaceWith('<div id="katalog-map" style="position: relative; height: 430px;"></div>')

                    let myMap = new ymaps.Map('katalog-map', {
                        center: [this.city.gpsLat, this.city.gpsLong],
                        zoom: 10
                    })

                    let clusterer = new ymaps.Clusterer({
                        preset: 'islands#invertedOrangeClusterIcons',
                        groupByCoordinates: false,
                        clusterDisableClickZoom: true,
                        clusterHideIconOnBalloonOpen: false,
                        geoObjectHideIconOnBalloonOpen: false
                    })

                    let geoObjects = []
                    let objectsEvent = []

                    this.basesMap.map((base, key) => {

                        geoObjects[key] = new ymaps.Placemark([base.gpsLat, base.gpsLong], this.getPointData(base), this.getPointOptions(base))
                        objectsEvent[base.base_id] = geoObjects[key]
                    })
                    clusterer.add(geoObjects)
                    myMap.geoObjects.add(clusterer)
                    this.geoObjects = objectsEvent
                    jQuery('body').on('mouseover', '.kalbosa', function () {
                        const key = jQuery(this).data('key')
                        const objectState = clusterer.getObjectState(objectsEvent[jQuery(this).data('key')]);

                        if (objectsEvent[key].state._data.active) {
                            return false
                        }

                        if (objectState.isClustered) {
                            // Если метка находится в кластере, выставим ее в качестве активного объекта.
                            // Тогда она будет "выбрана" в открытом балуне кластера.
                            objectState.cluster.state.set('activeObject', objectsEvent[jQuery(this).data('key')]);
                            clusterer.balloon.open(objectState.cluster);
                        } else if (objectState.isShown) {
                            // Если метка не попала в кластер и видна на карте, откроем ее балун.
                            objectsEvent[key].balloon.open();
                        }

                    })
                })
            },
            getPointData(base) {
                return {
                    balloonContentHeader: `<a href="${base.link}" style="max-width: 170px; font-size: 12px">${base.name}</a>`,
                    balloonContentBody: `
          <div style="display: flex; justify-content: center; align-items: center;">
            <a href="${base.link}" style="display:flex; width: 200px; height: 100px; position: relative;" onClick="yaCounter41615739.reachGoal('selected_base_from_cart', {sphere_id: '${base.sphere_id}'})">
              <img style="object-fit: cover; object-position: center" src="${base.image}">
              <span class="map-sort-item">${base.minPrice} - ${base.maxPrice} ₽/ч</span>
            </a>
           </div>`,
                    balloonContentFooter: `
          <ul>
						<li class='base_preview_list__item' style="display: flex;">
							<span class="base_preview_title" style="max-width: 16px;min-width: 16px; margin-right: 5px"><img style="max-width: 16px; max-height: 16px;" src="${BASE_URL}/2019/04/baseline-location_on-24px.svg"/></span>
							<span class="base_preview_value">${base.address}</span>
						</li>
						<li class='base_preview_list__item' style="display: flex;">
							<span class="base_preview_title" style="max-width: 16px;min-width: 16px; margin-right: 5px"><img src="${BASE_URL}/2019/04/metro-logo-vector-basic.svg"/></span>
							<span class="base_preview_value">${base.metro}</span>
						</li>
						<li class='base_preview_list__item' style="display: flex;">
							<span class="base_preview_title" style="max-width: 16px;min-width: 16px; margin-right: 5px"><img src="${BASE_URL}/2019/04/baseline-access_time-24px.svg"/></span>
							<span class="base_preview_value">
								<span><span>Будни:</span> ${base.work_time}</span>
								<span><span>Выходные:</span> ${base.weekend_time}</span>
							</span>
						</li>
					</ul>`,
                    clusterCaption: `<strong>${base.name}</strong>`
                }
            },
            getPointOptions(base) {
                return {
                    iconLayout: 'default#image',
                    iconImageHref: base.icon,
                    iconImageSize: [30, 40]
                }
            },
            toggleFilters() {
                let scrollWidth = Math.max(
                    document.body.scrollWidth, document.documentElement.scrollWidth,
                    document.body.offsetWidth, document.documentElement.offsetWidth,
                    document.body.clientWidth, document.documentElement.clientWidth
                )

                if (scrollWidth <= 1024) {
                    let header = document.querySelector('#header')
                    header.classList.add('hide_header')
                }

                this.showFilters = !this.showFilters
            },
            clearFilters() {
                this.filter = {
                    static: [],
                    features: [],
                }

                this.closeFilters()

                this.showPreloader()
                this.setTotalBases()
                let bases = this.setBases()
                let map = this.setMap()

                Promise.all([bases, map]).then(() => {
                    setTimeout(() => this.hidePreloader(), 2000)
                })
            },
            closeFilters() {
                let header = document.querySelector('#header')
                header.classList.remove('hide_header')

                this.showFilters = false
            },
            applyFilters() {
                this.closeFilters()
                if (this.filter.static.length === 0 && this.filter.features.length === 0) {
                    return false
                }
                this.showPreloader()
                this.setTotalBases()
                let bases = this.setBases()
                let map = this.setMap()

                yaCounter41615739.reachGoal('selected_filter', {
                    filters: this.filter.features.map(item => {
                        return {
                            feature_id: item.feature_id,
                            category_id: item.category_id
                        }
                    })
                })

                Promise.all([bases, map]).then(() => {
                    this.hidePreloader()
                })
            },
            removeFilter(item, entity) {
                let index = this.filter[entity].indexOf(item)
                this.filter[entity].splice(index, 1)
                this.applyFilters()
            },
            showPreloader() {
                jQuery('#preloader_sl').show()
                this.loading = true
            },
            hidePreloader() {
                jQuery('#preloader_sl').fadeOut('slow')
                this.loading = false
            },
            onlyListView() {
                if (this.listView) {
                    this.listView = false
                    this.initMap()
                } else {
                    yaCounter41615739.reachGoal('selected_list')
                    this.listView = true
                    this.mapView = false
                }
            },
            onlyMapView() {
                if (this.mapView) {
                    this.mapView = false
                } else {
                    yaCounter41615739.reachGoal('selected_on_cart')
                    this.mapView = true
                    this.listView = false
                }
                this.initMap()
            },
            getBaseList(base_id) {
                const params = {
                    'base': base_id
                }
                return axios.get('https://hendrix.musbooking.com/api/bases/list-city', {params: params})
                    .then(response => {
                        return response.data
                    })
            },
            getBasesParams(limit = null, need_without = false, with_city = true, action = 'get_bases_katalog') {
                let params = {
                    slug: SLUG,
                    action: action,
                    square: this.square.value,
                    minPrice: this.minPrice.value,
                    filters: this.filter.static.map(item => item.type),
                    features: this.filter.features.map(item => item.feature_id),
                    sphere: SPHERE_ID,
                    limit: limit ? limit : this.limit,
                    sort_order: this.sort.order ? this.sort.order : null,
                    sort_direction: this.sort.direction ? this.sort.direction : null,
                }

                if (with_city) {
                    params.city = this.city.city_id
                }

                if (need_without) {
                    params.without_ids = this.bases.map(base => base.base_id)
                }

                return params
            },
            showMore() {
                this.showPreloader()
                axios.get(AJAX_URL, {params: this.getBasesParams(10, true, true, 'get_bases_katalog_with_rooms')}).then(async response => {
                    if (response.data) {
                        const bases = response.data
                        if (bases) {
                            for (const key in bases) {
                                bases[key].rating = Number(bases[key].rating)
                                for (const index in bases[key]['rooms']) {
                                    bases[key]['rooms'][index]['images'] = JSON.parse(bases[key]['rooms'][index]['images'])
                                }
                            }
                            this.bases = [...this.bases, ...bases]
                        }

                    }
                    this.hidePreloader()
                })
            },
            scroll() {
                let tempScrollTop, currentScrollTop = 0;
                const container = jQuery('#bases')
                jQuery(window).scroll(function () {
                    let distance = container.height() - document.documentElement.scrollTop
                    currentScrollTop = jQuery(window).scrollTop();
                    if (tempScrollTop < currentScrollTop) {
                        if (distance <= 200 && !this.loading && this.canLoadMore() && !this.mapView && jQuery('.bases_preview__item').length >= 10) {
                            this.showMore()
                        }
                    }
                    tempScrollTop = currentScrollTop;
                }.bind(this))
            },
            loadSquareInterval() {
                let params = {
                    action: 'get_square_interval',
                    sphere_id: SPHERE_ID,
                    city_id: this.city.city_id,
                }

                return axios.get(AJAX_URL, {params: params}).then(response => {
                    this.square.min = response.data.min ? parseInt(response.data.min) : 0
                    this.square.max = response.data.max ? parseInt(response.data.max) : 0

                    if (this.square.min == this.square.max) {
                        return false
                    }

                    let minValue = this.square.min
                    let maxValue = this.square.max

                    if (SESSION_DATA.square && SESSION_DATA.square.length > 0) {
                        minValue = parseInt(SESSION_DATA.square[0])
                        maxValue = parseInt(SESSION_DATA.square[1])
                    }

                    this.square.value = [minValue, maxValue]
                })
            },
            loadMinPriceInterval() {
                let params = {
                    action: 'get_min_price_interval',
                    sphere_id: SPHERE_ID,
                    city_id: this.city.city_id,
                }

                return axios.get(AJAX_URL, {params: params}).then(response => {
                    this.minPrice.min = this.roundFloor(response.data.min, this.minPrice.interval)
                    this.minPrice.max = this.roundCeil(response.data.max, this.minPrice.interval)

                    if (this.minPrice.min == this.minPrice.max) {
                        return false
                    }

                    let minValue = this.minPrice.min
                    let maxValue = this.minPrice.max

                    if (SESSION_DATA.minPrice && SESSION_DATA.minPrice.length > 0) {
                        minValue = this.roundFloor(SESSION_DATA.minPrice[0], this.minPrice.interval)
                        maxValue = this.roundCeil(SESSION_DATA.minPrice[1], this.minPrice.interval)
                    } else {
                        minValue = this.roundFloor(this.minPrice.min, this.minPrice.interval)
                        maxValue = this.roundCeil(this.minPrice.max, this.minPrice.interval)
                    }

                    this.minPrice.value = [minValue, maxValue]
                })
            },
            roundFloor(value, interval) {
                return Math.floor(Number(value) / interval) * interval
            },
            roundCeil(value, interval) {
                return Math.ceil(Number(value) / interval) * interval
            },
            onChangeMinPrice() {
                clearTimeout(this.minPrice.timer)

                if (this.minPrice.value[0] == this.minPrice.value[1]) {
                    if (this.minPrice.value[0] + this.minPrice.interval < this.minPrice.max) {
                        this.minPrice.value = [this.minPrice.value[0], this.minPrice.value[0] + this.minPrice.interval]
                    } else {
                        this.minPrice.value = [this.minPrice.value[1] - this.minPrice.interval, this.minPrice.value[1]]
                    }
                }

                this.minPrice.timer = setTimeout(() => {
                    this.showPreloader()
                    this.setTotalBases()
                    let map = this.setMap()
                    let bases = this.setBases()

                    Promise.all([bases, map]).then(responses => {
                        setTimeout(() => this.hidePreloader(), 2000)
                    })
                }, 500)
            },
            onChangeSquare() {
                clearTimeout(this.square.timer)

                if (this.square.value[0] == this.square.value[1]) {
                    if (this.square.value[0] + 1 < this.square.max) {
                        this.square.value = [this.square.value[0], this.square.value[0] + 1]
                    } else {
                        this.square.value = [this.square.value[1] - 1, this.square.value[1]]
                    }
                }

                this.square.timer = setTimeout(() => {
                    this.showPreloader()
                    let map = this.setMap()
                    let bases = this.setBases()

                    Promise.all([bases, map]).then(responses => {
                        setTimeout(() => this.hidePreloader(), 2000)
                    })
                }, 500)
            },
            hoveredCard(base_id) {
                this.geoObjects[base_id].balloon.open()

            }
        },
        computed: {
            isHideShowMore() {
                return this.bases.length < this.basesTotal
            },
            isHideMinPrice() {
                return this.minPrice.min == this.minPrice.max
            },
            isHideSquare() {
                return this.square.min == this.square.max
            }
        },
        mounted() {
            this.showPreloader()
            jQuery('.catalog_wrap').css('display', 'flex')
            this.setSpheres()
            this.setCities().then(() => {
                let squareInterval = this.loadSquareInterval()
                let minPriceInterval = this.loadMinPriceInterval()
                let filters = this.setFilters()
                Promise.all([squareInterval, minPriceInterval, filters]).then(() => {
                    this.setTotalBases()
                    let map = this.setMap()
                    let bases = this.setBases()
                    Promise.all([bases, map]).then(() => {
                        setTimeout(() => this.hidePreloader(), 2000)
                        jQuery(".base-link-images a").fancybox();
                        // jQuery.fancybox.defaults.buttons = [
                        //     'slideShow',
                        //     'share',
                        //     'zoom',
                        //     'close'
                        // ];

                    })
                })
            })
            this.scroll()
        }
    })
}
