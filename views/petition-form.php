<form class="petition-form" method="POST" action="<?php echo esc_url( sprintf( '%s/sign', untrailingslashit( get_permalink() ) ) ); ?>">
	<fieldset <?php $user_has_signed && print 'disabled'; ?>>
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'amnesty_petition_signature' ) ); ?>">
		<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( amnesty_get_referrer() ); ?>">
		<div class="petition-formFill">
			<label for="petition-first-name" class="u-hiddenVisually"><?php /* translators: [front/admin] input placeholder text */ esc_html_e( 'First Name', 'aip' ); ?>. <?php /* translators: [front] input informational text */ esc_attr_e( 'Required Field', 'aip' ); ?></label>
			<input id="petition-first-name" type="text" name="first_name" required placeholder="<?php /* translators: [front/admin] input placeholder text */ esc_attr_e( 'First Name', 'aip' ); ?>*">
			<label for="petition-last-name" class="u-hiddenVisually"><?php /* translators: [front/admin] input placeholder text */ esc_html_e( 'Last Name', 'aip' ); ?>. <?php /* translators: [front] input informational text */ esc_attr_e( 'Required Field', 'aip' ); ?></label>
			<input id="petition-last-name" type="text" name="last_name" required placeholder="<?php /* translators: [front/admin] input placeholder text */ esc_attr_e( 'Last Name', 'aip' ); ?>*">
			<label for="petition-phone" class="u-hiddenVisually"><?php /* translators: [front/admin] input placeholder text */ esc_html_e( 'Telephone', 'aip' ); ?></label>
			<input id="petition-phone" type="tel" name="phone" placeholder="<?php /* translators: [front/admin] input placeholder text */ esc_attr_e( 'Telephone', 'aip' ); ?>">
			<label for="petition-email" class="u-hiddenVisually"><?php /* translators: [front/admin] input placeholder text */ esc_html_e( 'Email Address', 'aip' ); ?>. <?php /* translators: [front] input informational text */ esc_attr_e( 'Required Field', 'aip' ); ?></label>
			<input id="petition-email" type="email" name="email" required placeholder="<?php /* translators: [front/admin] input placeholder text */ esc_attr_e( 'Email', 'aip' ); ?>*">
		</div>
	<?php if ( $attributes['showNewsletter'] && isset( $settings['mailing_list_text'] ) ) : ?>
		<div class="petition-formExtra"><?php echo wp_kses_post( nl2br( $settings['mailing_list_text'] ) ); ?></div>
		<div class="petition-formExtra">
			<span class="petition-radio">
				<span><input id="newsletter-yes" type="radio" name="newsletter" value="yes"></span>
				<label for="newsletter-yes"><?php echo esc_html( $attributes['newsletterAcceptText'] ); ?></label>
			</span>
			<span class="petition-radio">
				<span><input id="newsletter-no" type="radio" name="newsletter" value="no"></span>
				<label for="newsletter-no"><?php echo esc_html( $attributes['newsletterRejectText'] ); ?></label>
			</span>
		</div>
	<?php endif; ?>
		<button class="btn btn--fill" type="submit"><?php echo esc_html( $attributes['buttonText'] ); ?></button>
	</fieldset>
</form>
