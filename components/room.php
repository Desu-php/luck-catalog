<?php require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/PageBuilder.php"); ?>
<?php $pageBuilder = new PageBuilder(); ?>
<?php $roomData = $pageBuilder->getRoomData(); ?>

<div id="room-vue">
  <?php if($roomData['isRepetitorsPage']){ ?>
    <?php require(WP_PLUGIN_DIR . LUCK_DIR_COMPONENTS . "/repetitor-room.php"); ?>
  <?php }else{ ?>
    <?php require(WP_PLUGIN_DIR . LUCK_DIR_COMPONENTS . "/katalog-room.php"); ?>
  <?php }?>
<!-- 
  <div class="slider-wrapper">
    <div class="container">
      <div class="owl-carousel slider">
        <?php foreach($roomData['images'] as $image){ ?>
          <div class="slide">
            <a href="<?php echo $image; ?>" data-fancybox="gallery">
              <img src="<?php echo $image; ?>" alt="<?php echo get_the_title(); ?>"/>
            </a>
          </div>
        <?php }?>
      </div>
    </div>
  </div> -->
</div>
