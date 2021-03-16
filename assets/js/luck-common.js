(function ($) {
  // $('.filter-drop-btn').on('click', function(){
  //   $(this).toggleClass('active')
  //   console.log(2);
  // })

  $(document).on('click', '.minimize-btn', function (e) {
    e.preventDefault()
    const moreBtn = $(this);
    const text_open = moreBtn.data('open-text')
    const close_text = moreBtn.data('close-text')
    const parent = $(this).parents(`${moreBtn.data('parent')}`)
    const textCont = parent.find('.text-container')
    const images = parent.find('.object-area__images');
    let text = moreBtn.text();
    if(textCont.hasClass('show')){
      textCont.removeClass('show')
    }else{
      textCont.addClass('show')
    }
    if(images.hasClass('show')){
      images.removeClass('show')
    }else{
      images.addClass('show')
    }
    moreBtn.text(text === close_text ? text_open : close_text);
  })

  $(document).on('click', '#open-app', function (e) {
    e.preventDefault()

    openApp()
  })

  if ($('#base-map').length > 0) {
    let gpsLat = $('#base-map').data('gpslat')
    let gpsLong = $('#base-map').data('gpslong')
    let icon = $('#base-map').data('icon')

    ymaps.ready(function () {
      var baseMap = new ymaps.Map("base-map", {
        center: [gpsLat, gpsLong],
        zoom: 14
      });

      let basePlacemark = new ymaps.Placemark([gpsLat, gpsLong], {}, {
        iconLayout: 'default#image',
        iconImageHref: icon,
        iconImageSize: [30, 40],
      });
      baseMap.geoObjects.add(basePlacemark);
    })
  }

  $(window).scroll(function () {
    if ($(this).scrollTop() > 1) {
      $('.breadcrumb').addClass("sticky");
    } else {
      $('.breadcrumb').removeClass("sticky");
    }
  });

  //TODO: отрефакторить, чтобы массив images в room.php появлялся раньше чем отрисовка страницы
  setTimeout(function(){
    $('.owl-carousel').owlCarousel({
      dots: false,
      margin: 10,
      responsive: {
        0: {
          items: 1
        },
        768: {
          items: 3
        }
      }
    });
    $('[data-fancybox="gallery"]').fancybox();
  }, 3000)

  function isIos() {
    return !!navigator.platform && /iPad|iPhone|iPod/.test(navigator.platform)
  }

  function openApp() {
    if ($(window).width() <= 767) {
      if (isIos()) {
        window.open('https://redirect.appmetrica.yandex.com/serve/457865545711104653')
      } else {
        window.open('https://redirect.appmetrica.yandex.com/serve/457865545711104653')
      }
    } else {
      $('[data-remodal-id=open-app]').remodal().open()
    }
  }

  if(location.href.indexOf('katalog') != -1){
    setTimeout(function() {
      $('[data-remodal-id=reserve-mob]').remodal().open()
    }, 60000)
  }

  $(document).on('click', '#download-app', function() {
    openApp()
  })
})(jQuery);
