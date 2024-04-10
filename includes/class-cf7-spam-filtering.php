<?php
/**
 * A class for implementing spam filtering features for Contact Form 7.
 *
 * Author: Vishal Padhariya
 * Version: 1.0.0
 * License: GPL2
 *
 * @package cf7-spam-filtering
 */

/**
 * Class CF7_Spam_Filtering
 *
 * A class for implementing spam filtering features for Contact Form 7.
 *
 * This class adds spam filtering options to each contact form, saves filtering options meta data,
 * and validates form submissions against spam filter options.
 *
 * @since 1.0.0
 */
class CF7_Spam_Filtering {



	/**
	 * Constructor for initializing spam filtering features for Contact Form 7.
	 *
	 * Adds spam filtering options tab for each contact form,
	 * saves filtering options meta data, and validates form submissions
	 * against spam filter options.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Add spam filtering options tab for each contact form.
		add_action( 'wpcf7_editor_panels', array( $this, 'add_spam_filtering_tab' ) );

		// Save filtering options meta data.
		add_action( 'wpcf7_after_save', array( $this, 'save_cf7_spam_filtering_form_meta' ) );

		// Validate form submissions against spam filter options.
		add_filter( 'wpcf7_validate_email*', array( $this, 'validate_email' ), 20, 2 );
	}

	/**
	 * Callback function to add spam filtering tab content to CF7 settings.
	 *
	 * @param array $panels An array of editor panels.
	 * @return array Modified array of editor panels with added spam filtering tab.
	 */
	public function add_spam_filtering_tab( $panels ) {
		$panels['spam-filtering'] = array(
			'title'    => __( 'Spam Filtering', 'contact-form-7' ),
			'callback' => array( $this, 'spam_filtering_tab_content' ),
		);
		return $panels;
	}

	/**
	 * Saves spam filtering form meta data.
	 *
	 * @param WPCF7_ContactForm $contact_form The contact form object.
	 * @return void
	 */
	public function save_cf7_spam_filtering_form_meta( $contact_form ) {
		// Get the form ID.
		$form_id = $contact_form->id();

		// Get the meta data you want to save.
		$spam_domains = isset( $_POST['cf7_spam_domains'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cf7_spam_domains'] ) ) : '';

		// Save the meta data for the form.
		update_post_meta( $form_id, '_cf7_spam_domains', $spam_domains );
	}

	/**
	 * Render the spam filtering tab content in Contact Form 7 settings.
	 *
	 * @return void|HTML content
	 */
	public function spam_filtering_tab_content() {
		$contact_form    = WPCF7_ContactForm::get_current();
		$contact_form_id = $contact_form->id;
		$html            = '<div class="cf7-spam-filtering-options">';
		$html           .= '<h3>' . esc_html_e( 'Spam Filtering Options', 'cf7-spam-filtering' ) . '</h3>';
		$html           .= '<p>' . esc_html_e( 'Add domains to prevent in form submissions:', 'cf7-spam-filtering' ) . '</p>';
		$html           .= '<textarea name="cf7_spam_domains" rows="5" cols="50">' . esc_textarea( get_post_meta( $contact_form_id, '_cf7_spam_domains', true ) ) . '</textarea>';
		$html           .= '</div>';

		echo wp_kses_post( $html );
	}

	/**
	 * Validate email field submissions against spam filter options.
	 *
	 * @param WPCF7_Validation $result The validation result object.
	 * @param WPCF7_FormTag    $tag    The email form tag object.
	 * @return WPCF7_Validation The modified validation result object.
	 */
	public function validate_email( $result, $tag ) {
		$form_id          = isset( $_POST['_wpcf7'] ) ? absint( wp_unslash( $_POST['_wpcf7'] ) ) : '';
		$email_field_name = sanitize_text_field( wp_unslash( $this->get_cf7_email_field_name( $form_id ) ) );

		if ( '' !== $email_field_name ) {
			$email = isset( $_POST[ $email_field_name ] ) ? sanitize_email( wp_unslash( trim( $_POST[ $email_field_name ] ) ) ) : '';
		} else {
			$result->invalidate( $tag, 'Email field name not found!' );
		}

		// Retrieve spam domains from the form settings.
		$spam_domains = get_post_meta( $form_id, '_cf7_spam_domains', true );
		if ( '' !== $spam_domains ) {
			$spam_domains = explode( "\n", $spam_domains );
			$spam_domains = array_map( 'trim', $spam_domains );

			// Block submissions from spam domains.
			foreach ( $spam_domains as $domain ) {
				if ( false !== strpos( $email, $domain ) ) {
					$result->invalidate( $tag, "Submission from $domain is not allowed." );
					break; // Stop checking once a spam domain is found.
				}
			}
		}

		return $result;
	}

	/**
	 * Get Field name by form id.
	 *
	 * @param int $form_id Form Id for get fields object.
	 *
	 * @return string Field name
	 */
	public function get_cf7_email_field_name( $form_id ) {
		$cf7_form         = WPCF7_ContactForm::get_instance( $form_id );
		$form_fields      = $cf7_form->scan_form_tags();
		$email_field_name = '';

		$email_object = array_filter(
			$form_fields,
			function ( $obj ) {
				return 'email*' === $obj['type'];
			}
		);

		$email_field_name = reset( $email_object )['name'];

		return sanitize_text_field( wp_unslash( $email_field_name ) );
	}
}

// Initialize the self-class.
new CF7_Spam_Filtering();
