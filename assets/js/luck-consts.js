Vue.component('multiselect', window.VueMultiselect.default)
Vue.component('v-select', window.VueSelect.default)
Vue.component('star-rating', window.VueStarRating.default)
Vue.component('vue-slider', window['vue-slider-component'])


const YM_COUNTER = 41615739
const AJAX_URL = '/wp-admin/admin-ajax.php'
const SOCKET_URL = jQuery('#js_info').data('socket_url')
const WIDGET_URL = jQuery('#js_info').data('widget_url')
const SLUG = jQuery('#js_info').data('slug')
const SPHERE_ID = jQuery('#js_info').data('sphere_id')
const BASE_URL = jQuery('#js_info').data('base_url')
const BASE_ID = jQuery('#js_info').data('base_id')
const NO_PAY_FEATURE = '4aed4962-ff08-4732-99f3-c49ba81beb67'
const NO_REQUEST_FEATURE = 'c75e6023-6c12-459c-90be-85d79b9b5698'
const SPHERE_TITLES = {
  '0676eaa9-4ddb-495c-8980-06250d7d5f4a': 'Репетиционные базы Каталог - MUSbooking',
  '38f7aeac-d462-41a2-b4e7-f2c642cb9225': 'Музыкальные классы Каталог - MUSbooking',
  '36e5c7bc-f45c-4251-9552-456664c55c22': 'Танцевальные залы Каталог - MUSbooking',
  '41ce174b-2964-416b-9830-3468bf15dba3': 'Студии звукозаписи Каталог - MUSbooking',
  'b2c8f9ff-a0f0-42f8-be3e-c061d5c1144c': 'Фотостудии Каталог - MUSbooking',
  '0be020e3-34dd-432b-ba93-4a7cc45a2777': 'Площадки для мероприятий Каталог - MUSbooking',
}
const SESSION_DATA = {
  city: jQuery('#js_info').data('city_id'),
  square: jQuery('#js_info').data('square'),
  minPrice: jQuery('#js_info').data('min_price'),
  features: jQuery('#js_info').data('features'),
  filters: jQuery('#js_info').data('filters'),
}
