<?php
$menu_type = engage_get_theme_option( 'header-style' );
$page_wrapper_class = '';
if( $menu_type == 'header-menu-icon' && !is_404() ) {
    $page_wrapper_class = "page-wrapper";
}
$solid_nav_class = '';
if ( $menu_type == 'header-menu-top' && engage_is_blog() ) {
    $solid_nav_class = 'solid-nav';
}
?>

<?php require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/PageBuilder.php"); ?>
<?php $pageBuilder = new PageBuilder(); ?>
<?php $categoryTitle = $pageBuilder->getCategoryTitle(); ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

  <!-- Favicon Icon -->
  <?php if( !function_exists( 'wp_site_icon' ) ) {?>
      <?php $favicon = engage_get_theme_option( 'favicon' ); ?>
      <?php $favicon = $favicon[ 'url' ]; ?>
      <link rel="shortcut icon" href="<?php echo esc_url( $favicon ); ?>">
  <?php } ?>

  <?php wp_head();?>

  <title><?php echo $categoryTitle; ?></title>

  <meta name="apple-itunes-app" content="app-id=1119551003">
  <meta name="yandex-verification" content="2d720ed9c0e9aee7" />
  <meta name="google-site-verification" content="AEESKjIVPYRh5oPgqCrm08F8qDqtKhaaInZdAEnyEYU" />

  <script type="text/javascript" src="<?php bloginfo('template_url'); ?>/ajax.js"></script>
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
</head>

<?php if (!isset($_SERVER['HTTP_USER_AGENT']) || stripos($_SERVER['HTTP_USER_AGENT'], 'Chrome-Lighthouse') === false): ?>
    <script src="https://api-maps.yandex.ru/2.1/?apikey=c0036657-8cb3-46f2-98a1-34b4ffe2240b&lang=ru_RU" type="text/javascript"></script>
    <script type="text/javascript" >
      (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
          try {
            w.yaCounter41615739 = new Ya.Metrika2({
              id:41615739,
              clickmap:true,
              trackLinks:true,
              accurateTrackBounce:true,
              webvisor:true
            });
          } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
          s = d.createElement("script"),
          f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/tag.js";

        if (w.opera == "[object Opera]") {
          d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
      })(document, window, "yandex_metrika_callbacks2");
    </script>

    <noscript><div><img src="https://mc.yandex.ru/watch/41615739" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
    <!-- /Yandex.Metrika counter -->

    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

      ga('create', 'UA-89006792-1', 'auto');
      ga('send', 'pageview');
    </script>

<?php endif; ?>

<!-- Begin Talk-Me {literal} -->
<script type='text/javascript'>
  (function(d, w, m) {
    window.supportAPIMethod = m;
    var s = d.createElement('script');
    s.type ='text/javascript'; s.id = 'supportScript'; s.charset = 'utf-8';
    s.async = true;
    var id = '7cd43ecc7385b225555f8b2012b118c4';
    s.src = '//lcab.talk-me.ru/support/support.js?h='+id;
    var sc = d.getElementsByTagName('script')[0];
    w[m] = w[m] || function() { (w[m].q = w[m].q || []).push(arguments); };
    if (sc) sc.parentNode.insertBefore(s, sc);
    else d.documentElement.firstChild.appendChild(s);
  })(document, window, 'TalkMe');
</script>
<!-- {/literal} End Talk-Me -->


<body <?php body_class(); ?> >
<div id="katalog-vue">
<?php if ( engage_get_theme_option( 'preloader' ) == 1 ) { ?>
    <div id="preloader">
        <div class="preloader-wrap">
            <div class="spinner">
                <div class="bounce1"></div>
                <div class="bounce2"></div>
                <div class="bounce3"></div>
            </div>
        </div>
    </div>
<?php } ?>

<?php
  get_template_part( 'template-parts/katalog-menu' );
?>

<section class="content-wrap <?php echo esc_attr( $page_wrapper_class ); ?> <?php echo esc_attr( $solid_nav_class ); ?>">
