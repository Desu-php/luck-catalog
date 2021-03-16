if (jQuery('#room-vue').length > 0) {
    Vue.component("modal", {
        props: ['width'],
        template: "#modal-template",
    });
    function textLine (){
      const div = jQuery('.text-line-7'),
          lh = parseInt(div.css('line-height')),
          dh = div.height();
      const count_line = dh/lh
        if (count_line <= 6){
            jQuery('#description_base').find('a').hide()
        }

    }

    jQuery(window).load(function (){
        textLine()
    })
    // Vue.use(window.Maska)
    new Vue({
        el: '#room-vue',
        data: () => {
            return {
                rooms: [{
                    room_id: ''
                }],
                reviews: [],
                isReviewsVisible: false,
                modalShow: false,
                name: '',
                phone: '',
                phoneError: false,
                token: '',
                equipments: [],
                room_id: ''
            }
        },
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

            ShowModal(e) {
                this.room_id = e.currentTarget.getAttribute('data-room_id')
                console.log(this.room_id)
            },
            setRooms() {
                let params = {
                    action: 'get_rooms',
                    base_id: BASE_ID
                }
                return axios.get(AJAX_URL + '?' + jQuery.param(params)).then(response => {
                    this.rooms = response.data.rooms.map((room, index) => {
                        room.active = index == 0 ? true : false
                        return room
                    })
                })
            },
            validatePhone(phone) {
                console.log(phone)
                var regex = /^(\+7|7|8)?[\s\-]?\(?[489][0-9]{2}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/;
                return regex.test(phone);
            },
            setEquipments() {
                axios.get(`https://hendrix.musbooking.com/api/equipments/balance?base=${BASE_ID}`)
                    .then(response => {
                        const groupName = []
                        let data = {}
                        response.data.forEach((item) => {
                            if (groupName.indexOf(item.groupName) === -1) {
                                groupName.push(item.groupName)
                                const newData = {
                                    [item.groupName]: [item]
                                }
                                data = {...data, ...newData}
                            } else {
                                data[item.groupName].push(item)
                            }

                        })

                        this.equipments = data
                        console.log(this.equipments)

                    })
            },
            async sendOrder() {
                const phone = "+7" + this.phone
                if (!this.validatePhone(phone)) {
                    this.phoneError = true
                    return false
                }
                this.phoneError = false
                const formData = new FormData()
                formData.append('login', 'test')
                formData.append('password', 'CksEme')
                const token = await this.getToken(formData)
                const user = await this.getUser(token)

                if (user.length) {
                    console.log('room_id',this.room_id)
                } else {
                    const form = new FormData();
                    form.append('firstName', this.name)
                    form.append('phone', this.phone)
                    const user = await this.createUser(form, token)
                    console.log(user)
                }

            },
            async getUser(token) {
                const config = {
                    headers: {Authorization: `Bearer ${token}`}
                };
                return axios.get(`https://dev.musbooking.com/api/clients/search2?filter=${this.phone}`, config)
                    .then(response => {
                        return response.data
                    })
            },
            async createUser(formData, token) {
                return axios({
                    method: 'POST',
                    url: 'https://dev.musbooking.com/api/clients/save',
                    headers: {
                        "Content-Type": "multipart/form-data",
                        Authorization: `Bearer ${token}`
                    },
                    data: formData
                }).then(response => {
                    return response.data
                })
            },
            async getToken(formData) {
                return axios({
                    method: 'POST',
                    url: 'https://dev.musbooking.com/api/auth/login',
                    headers: {"Content-Type": "multipart/form-data"},
                    data: formData
                }).then(response => {
                    return response.data.token
                })
            },
            setReviews() {
                let params = {
                    action: 'get_reviews',
                    room_id: this.rooms.find(room => room.active).room_id
                }

                axios.get(AJAX_URL + '?' + jQuery.param(params)).then(response => {
                    this.reviews = response.data.reviews
                })
            },
            changeRoom(room) {
                this.rooms.map(item => item.active = false)

                let roomIndex = this.rooms.findIndex(item => item.room_id == room.room_id)
                this.rooms[roomIndex].active = true

                jQuery('#widget').attr('src', `${WIDGET_URL}/?room=${room.room_id}&source=1&optionChange=1`)
                this.setReviews()

                yaCounter41615739.reachGoal('selected_room', {sphere_id: SPHERE_ID})
            },
            toggleReviews() {
                this.isReviewsVisible = !this.isReviewsVisible
            }
        },
        mounted() {
            // this.setRooms().then(() => {
            //     this.setReviews()
            // })
            this.setEquipments()
            jQuery(".fancy-box-gallery").fancybox();
        }
    })
}

