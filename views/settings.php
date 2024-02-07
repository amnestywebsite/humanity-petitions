<div class="wrap cmb2-options-page">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<h2 class="nav-tab-wrapper">
	<?php foreach ( $this->pages as $the_tab ) : ?>
		<?php $url = str_replace( '#038;', '&', add_query_arg( [ 'tab' => $the_tab['slug'] ], menu_page_url( $this->slug, false ) ) ); ?>

		<a class="nav-tab <?php $the_tab['slug'] === $tab['slug'] && print 'nav-tab-active'; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo wp_kses_post( $the_tab['title'] ); ?></a>
	<?php endforeach; ?>
	</h2>

	<?php cmb2_print_metabox_form( $tab['id'], $tab['id'] ); ?>
</div>
