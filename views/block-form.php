<aside class="wp-block-amnesty-petition <?php echo esc_attr( $attributes['className'] ?? '' ); ?> <?php $user_has_signed && print 'is-signed'; ?>">
	<header class="petition-header">
		<h2 class="petition-title"><?php echo esc_html( $user_has_signed ? $attributes['thankYouTitle'] : $attributes['title'] ); ?></h2>
		<p class="petition-subtitle"><?php echo esc_html( $user_has_signed ? $attributes['thankYouSubTitle'] : $attributes['subtitle'] ); ?></p>
	</header>

<?php if ( $attributes['showContent'] && ! empty( $attributes['contentTitle'] ) && ! empty( $attributes['content'] ) ) : ?>
	<div class="petition-contentReveal <?php $attributes['contentIsExpanded'] && print 'is-open'; ?>">
		<dl>
			<dt><span class="petition-contentTitle"><?php echo esc_html( $attributes['contentTitle'] ); ?></span></dt>
			<dd>
				<div class="petition-content"><?php echo wp_kses_post( nl2br( $attributes['content'] ) ); ?></div>
			</dd>
		</dl>
	</div>
<?php endif; ?>

<?php if ( $user_has_signed ) : ?>
	<div class="petition-thank-you-content"><?php echo wp_kses_post( apply_filters( 'the_content', $attributes['thankYouContent'] ) ); ?></div>
<?php else : ?>
	<?php include_once __DIR__ . '/petition-form.php'; ?>
<?php endif; ?>

<?php if ( ( $signatures !== $attributes['targetCount'] ) || ! $attributes['replaceProgressWithMessage'] ) : ?>
	<div class="petition-signatures">
		<p>
			<strong><?php echo esc_html( $signatures ? sprintf( /* translators: [front/admin] %s: the current number of signatures */ _n( '%s has signed.', '%s have signed.', $signatures, 'aip' ), number_format_i18n( $signatures ) ) : /* translators: [front] shown on petition block when it has yet to be signed by anyone */ __( 'Be the first to sign!', 'aip' ) ); ?></strong>
		<?php if ( $signatures && $signatures < absint( $attributes['targetCount'] ) ) : ?>
			<span><?php echo esc_html( sprintf( /* translators: [admin/front] %s: the target number of signatures */ _x( "Let's get to %s", 'Petition target signatory count', 'aip' ), number_format_i18n( $attributes['targetCount'] ) ) ); ?></span>
		<?php endif; ?>
		</p>

	<?php if ( $signatures ) : ?>
		<div class="petition-progress">
			<style>.petition-progressBar{--p:<?php echo esc_attr( 200 - $percentage ); ?>%;--q:<?php echo esc_attr( $percentage ); ?>%;}</style>
			<div class="petition-progressBar" role="progressbar" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( $attributes['targetCount'] ); ?>" aria-valuenow="<?php echo esc_attr( $signatures ); ?>">
				<span class="u-hiddenVisually"><?php echo esc_html( $percentage ); ?>%</span>
			</div>
		</div>
	<?php endif; ?>
	</div>
<?php endif; ?>
<?php if ( $signatures >= $attributes['targetCount'] && $attributes['replaceProgressWithMessage'] && $attributes['showTargetReachedText'] && $attributes['targetReachedText'] ) : ?>
	<div><?php echo esc_html( $attributes['targetReachedText'] ); ?></div>
<?php endif; ?>
<?php if ( isset( $settings['terms_text'] ) ) : ?>
	<hr>
	<div class="petition-terms"><?php echo wp_kses_post( wpautop( $settings['terms_text'] ) ); ?></div>
<?php endif; ?>
</aside>
