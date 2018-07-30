<?php

/* Quit */
defined( 'ABSPATH' ) || exit;

if ( function_exists( 'fau_initoptions' ) ) {
	$options = fau_initoptions();
} else {
	$options = array();
}

$breadcrumb = '';
if ( isset( $options['breadcrumb_root'] ) ) {
	if ( $options['breadcrumb_withtitle'] ) {
		$breadcrumb .= '<h3 class="breadcrumb_sitetitle" role="presentation">' . get_bloginfo( 'title' ) . '</h3>';
		$breadcrumb .= "\n";
	}
	$breadcrumb .= '<nav aria-labelledby="bc-title" class="breadcrumbs">';
	$breadcrumb .= '<h4 class="screen-reader-text" id="bc-title">' . __( 'Sie befinden sich hier:', 'fau' ) . '</h4>';
	$breadcrumb .= '<a data-wpel-link="internal" href="' . site_url( '/' ) . '">' . $options['breadcrumb_root'] . '</a>';
}

$values = get_option( 'rrze_tos' );

get_header(); ?>

<section id="hero" class="hero-small">
	<div class="container">
		<div class="row">
			<div class="col-xs-12">
				<?php echo $breadcrumb; ?>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<h1><?php echo( isset( $values['rrze_tos_title'] ) ? $values['rrze_tos_title'] : 'Barrierefreiheitserklärung' ); ?></h1>
			</div>
		</div>
	</div>
</section>

<div id="content">
	<div class="container">

		<div class="row">
			<div class="col-xs-12">
				<main>
					<h2><?php esc_html_e( 'sub title', 'rrze-tos' ); ?></h2>
					<?php ( new RRZE\Tos\Tos_Endpoint() )->get_tos_content(); ?>
				</main>
			</div>
		</div>
	</div>
</div>

<?php get_footer(); ?>
