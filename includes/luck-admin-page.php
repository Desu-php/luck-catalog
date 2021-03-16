<?php require_once(WP_PLUGIN_DIR . LUCK_DIR_INCLUDES . "/Parser.php"); ?>

<?php if(isset($_GET['upload'])) { ?>
    <?php new Parser(); ?>
    <div>
        <span>Каталог обновлен</span>
    </div>
<?php } else { ?>
    <form action="<?php echo site_url(); ?>/wp-admin/admin.php?page=luck-catalog/includes/luck-admin-page.php&upload" method="POST">
        <button class='button button-primary'>Обновить каталог</button>
    </form>
<?php }?>
