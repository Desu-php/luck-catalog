if (jQuery('#rep-vue').length > 0) {
  new Vue({
    el: '#rep-vue',
    data: () => {
      return {
        cities: [],
        city: [],

        options: [],
        option: [],

        bases: [],
        base: [],
        base_disable: true,

        features: [],
        selectedFeatures: [],
        feature_disable: true,
        feature_loading: false,
        feature_error: '',

        canChangeModal: true
      }
    },
    mounted: function() {
      axios.post(AJAX_URL, jQuery.param({ action: 'get_options', sphere_id: SPHERE_ID })).then(response_option => {
        axios.post(AJAX_URL, jQuery.param({ action: 'get_cities', sphere_id: SPHERE_ID })).then(response_city => {
          if(response_option.data['options']){
            eval(response_option.data['options']).map(option => {
              this.options.push(option)
            })
          }

          this.cities = response_city.data['cities']
          if (response_city.data['city'] && response_option.data['selected']) {
            this.city = response_city.data['city']
            this.option = this.options.find(option => option.id == response_option.data['selected'])
            this.setOption(this.option)
          }
          else if(response_city.data['city']){
            this.setCity(response_city.data['city'])
          }
        }, error => { throw error.response_city.data })
      }, error => { throw error.response_option.data })
    },
    methods: {
      setCity(city) {
        this.city = city
        this.bases = []
        this.base = []
        this.features = []
        this.selectedFeatures = []
        this.base_disable = true
        this.feature_disable = true

        if (typeof this.option.length == 'undefined') {
          jQuery('#preloader_sl').show();

          axios.post(AJAX_URL, jQuery.param({
            action: 'get_bases',
            sphere_id: SPHERE_ID,
            city_id: this.city.city_id,
            need_room: true,
            is_rep: true,
            date: `${moment().format('Y-MM-DD')} 00:00`,
            hours: 1,
            options: this.option.id,
            remove_features: true
          })).then(response => {
            this.bases = response.data
            jQuery('#preloader_sl').fadeOut('slow');
            this.base_disable = false
            this.feature_disable = false
          }, error => {
            jQuery('#preloader_sl').fadeOut('slow');
            throw error.response.data
          })
        }
      },
      setOption(option) {
        this.option = option
        this.bases = []
        this.base = []
        this.features = []
        this.selectedFeatures = []
        this.base_disable = true
        this.feature_disable = true

        if (this.city !== null) {
          jQuery('#preloader_sl').show();

          axios.post(AJAX_URL, jQuery.param({
            action: 'get_bases',
            sphere_id: SPHERE_ID,
            city_id: this.city.city_id,
            need_room: true,
            is_rep: true,
            date: `${moment().format('Y-MM-DD')} 00:00`,
            hours: 1,
            options: option.id
          })).then(response => {
            this.bases = response.data
            jQuery('#preloader_sl').fadeOut('slow');
            this.base_disable = false
            this.feature_disable = false
          }, error => {
            jQuery('#preloader_sl').fadeOut('slow');
            throw new error.response.data
          })
        }
      },
      setBase(base) {
        location = base.link
      },
      loadFeature() {
        this.feature_disable = true
        this.feature_loading = true
        jQuery('#preloader_sl').show();
        if (this.features.length > 0) {
          if (this.selectedFeatures.length > 0) {
            this.features.map(feature => {
              if (this.selectedFeatures.indexOf(feature.feature_id) == 1) {
                feature.checked = true
              }
              else {
                feature.checked = false
              }
            })
          }
          this.feature_disable = false
          this.feature_loading = false

          if(this.features.length == 0){
            this.canChangeModal = false
            this.feature_error = 'По вашим параметрам нет подходящих вариантов'
            this.selectedFeatures = []
          }else{
            this.canChangeModal = true
          }

          jQuery('#preloader_sl').fadeOut('slow');
          jQuery('[data-remodal-id=rep-params]').remodal().open();
        }
        else {
          axios.get(AJAX_URL, { params: { action: 'get_features_katalog', sphere_id: SPHERE_ID, city_id: this.city.city_id } }).then(response => {
            this.features = response.data.features
            this.feature_disable = false
            this.feature_loading = false

            if(this.features.length == 0){
              this.canChangeModal = false
              this.feature_error = 'По вашим параметрам нет подходящих вариантов'
              this.selectedFeatures = []
            }else{
              this.canChangeModal = true
              this.feature_error = ''
            }

            jQuery('#preloader_sl').fadeOut('slow');
            jQuery('[data-remodal-id=rep-params]').remodal().open();
          })
        }
      },
      setFeature() {
        jQuery('[data-remodal-id=rep-params]').remodal().close();
        this.base_disable = true
        jQuery('#preloader_sl').show();
        this.feature_disable = true
        this.feature_error = ''
        axios.post(AJAX_URL, jQuery.param({
          action: 'get_bases',
          sphere_id: SPHERE_ID,
          city_id: this.city.city_id,
          need_room: true,
          is_rep: true,
          date: `${moment().format('Y-MM-DD')} 00:00`,
          hours: 1,
          options: this.option.id,
          features: this.selectedFeatures.length > 0 ? this.selectedFeatures : false
        })).then(response => {
          this.bases = []
          this.bases = response.data
          this.base_disable = false
          this.feature_disable = false
          if(this.bases.length > 0){
            jQuery('[data-remodal-id=rep-params]').remodal().close();
          }else{
            this.feature_error = 'По вашим параметрам нет подходящих вариантов'
          }
          if(this.selectedFeatures.length == 0){
            this.features = []
            axios.get(AJAX_URL, { params: { action: 'get_features_katalog', sphere_id: SPHERE_ID, city_id: this.city.city_id } }).then(response => {
	            this.features = response.data.features
              this.feature_disable = false
              this.feature_loading = false
            })
          }
          jQuery('#preloader_sl').fadeOut('slow');
        }, error => {
          jQuery('#preloader_sl').fadeOut('slow');
          throw new error.response.data
        })
      },
      unsetFeature() {
        this.selectedFeatures = [];
      },
    }
  })
}
