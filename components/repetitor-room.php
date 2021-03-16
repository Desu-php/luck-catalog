<?php require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/PageBuilder.php"); ?>
<?php $pageBuilder = new PageBuilder(); ?>
<?php $repetotorsData = $pageBuilder->getRepetitorsRoomData(); ?>

<div id="js_info" style="display: none;"
   data-socket_url="<?php echo LUCK_SOCKET_URL; ?>"
   data-widget_url="<?php echo LUCK_WIDGET_URL; ?>"
   data-sphere_id="<?php echo $repetotorsData['base']['sphere_id']; ?>"
   data-base_id="<?php echo $repetotorsData['base']['base_id']; ?>">
</div>
<div class="section full-width">
  <div class="container">
    <div class="special-heading align-center">
      <h1 class="title title__custom-lg title__custom__with_btn"><?php echo get_the_title(); ?></h1>
    </div>
    <div class="vc_row">
      <div class="wpb_column vc_column_container">
        <div class="wpb_wrapper">
          <div class='base-images'>
            <?php foreach($repetotorsData['rooms'] as $room){ ?>
              <div class='base-images__item'>
                <div class="base-images__item-inner">
                  <div class="base-images__image-wrap">
                    <?php foreach(json_decode($room['image']) as $key => $image){ ?>
                      <a href="<?php echo $image; ?>" data-fancybox="gallery-<?php echo $room['room_id']; ?>" <?php if($key != 0){ ?>style="display: none;"<?php }?>>
                        <img class='base-images__image' src="<?php echo $image; ?>" alt="<?php echo 'Фотография ' . mb_strtolower(get_the_title()); ?>" title="<?php echo 'Фотография ' . mb_strtolower(get_the_title()); ?>"/>
                      </a>
                    <?php }?>
                    <span class='base-images__name'><?php echo $room['name']; ?></span>
                  </div>
                  <?php if(!$repetotorsData['base']['isRequest'] && !$repetotorsData['base']['isArchive']){ ?>
                    <a class='base-images__btn' href="<?php echo LUCK_WIDGET_URL; ?>/?room='<?php echo $room['room_id']; ?>'&partner='<?php echo $repetotorsData['base']['domain_id']; ?>'&source=1&optionChange=1&disableLinkLogo=1" target="_blank">Забронировать</a>
                  <?php }?>
                </div>
              </div>
            <?php }?>
          </div>
          <div class="teacher-details">
            <div class='teacher-details__item teacher-details__address'>
              <div class='teacher-details__item-inner'>
                <div class='address-item'>
                  <span class='address-item__title'>Адрес:</span>
                  <span class='address-item__value'><?php echo $repetotorsData['base']['address']; ?></span>
                </div>
                <div class='address-item address-item__time'>
                  <span class='address-item__title'>Часы работы:</span>
                  <span class='address-item__value'><span>Будни:</span> <?php echo $repetotorsData['base']['work_time']; ?></span>
                  <span class='address-item__value'><span>Выходные:</span> <?php echo $repetotorsData['base']['weekend_time']; ?></span>
                </div>
                <?php if($repetotorsData['base']['isRequest']){ ?>
                  <div class='mobile-only'>
                    Связаться с преподавателем через <span id="open-app" style="color: #ffa801; cursor: pointer;">приложение</span>
                  </div>
                <?php }?>
              </div>
            </div>
            <div class='teacher-details__item teacher-details__map'>
              <div class="teacher-details__item-inner">
                <div id="base-map" data-gpsLat="<?php echo $repetotorsData['base']['gpsLat']; ?>" data-gpsLong="<?php echo $repetotorsData['base']['gpsLong']; ?>" data-icon="<?php echo $repetotorsData['base']['icon']; ?>"></div>
              </div>
            </div>
            <div class='teacher-details__item teacher-details__description'>
              <div class='teacher-details__item-inner'>
                <h2>Процесс обучения и информация</h2>
                <div><?php echo $repetotorsData['base']['description']; ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
