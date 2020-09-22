<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<?php if ( ! function_exists( 'has_site_icon' ) || ! has_site_icon() ) : ?>
  <?php if ( get_theme_mod('site_favicon') ) : ?>
    <link rel="shortcut icon" href="<?php echo esc_url(get_theme_mod('site_favicon')); ?>" />
  <?php endif; ?>
<?php endif; ?>

<?php wp_head(); ?>

</head>

<body <?php body_class('page-maintenance-mode'); ?>>
<?php

  $container_class = 'emp-container';

  ob_start();
  if ( have_posts() ) :

  do_action('emp_content_before');

  ?>
    <div <?php echo apply_filters('amp_container_selector', $container_class); ?>>
    <?php
    while ( have_posts() ) : the_post();

      do_action('emp_entry_before');

      the_content();

      do_action('emp_entry_after');

    endwhile;
    ?>
    </div>
  <?php

  do_action('emp_content_after');

  endif;

  $content = ob_get_contents();

  ob_end_clean();

  echo apply_filters( 'emp_content', $content );

  wp_footer();
?>
</body>
</html>
