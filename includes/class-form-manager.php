<?php

/**
 * The Form Manager Class
 *
 * @since 2.8.7
 */
class WPUF_Form_Manager {

    /**
     * Get all the forms
     *
     * @return array
     */
    public function all() {
        return $this->get_forms();
    }

    /**
     * Get forms
     *
     * @param  array $args
     *
     * @return array
     */
    public function get_forms( $args = array() ) {
        $forms_array = array(
            'forms' => array(),
            'meta'  => array(
                'total' => 0,
                'pages' => 0
            )
        );
        $defaults  = array(
            'post_type'   => 'wpuf_forms',
            'post_status' => array( 'publish', 'draft', 'pending' ),
            'posts_per_page' => -1
        );

        $args  = wp_parse_args( $args, $defaults );

        $query = new WP_Query( $args );
        $forms = $query->get_posts();

        if ( $forms ) {
            foreach ( $forms as $form ) {
                $forms_array['forms'][] = new WPUF_Form( $form );
            }
        }

        $forms_array['meta']['total'] = (int) $query->found_posts;
        $forms_array['meta']['pages'] = (int) $query->max_num_pages;

        return $forms_array;
    }

    /**
     * Get a single form
     *
     * @param  integer|WP_Post $form
     *
     * @return \WPUF_Form
     */
    public function get( $form ) {
        return new WPUF_Form( $form );
    }

    /**
     * Create a form
     *
     * @param  string $form_name
     * @param  array  $fields
     *
     * @return integer|WP_Error
     */
    public function create( $form_name, $fields = array() ) {
        $form_id = wp_insert_post( array(
            'post_title'  => $form_name,
            'post_type'   => 'wpuf_forms',
            'post_status' => 'publish'
        ) );

        if ( is_wp_error( $form_id ) ) {
            return $form_id;
        }

        if ( $fields ) {
            foreach ( $fields as $order => $field) {
                $args = array(
                    'post_type'    => 'wpuf_input',
                    'post_parent'  => $form_id,
                    'post_status'  => 'publish',
                    'post_content' => maybe_serialize( wp_unslash( $field ) ),
                    'menu_order'   => $order
                );

                wp_insert_post( $args );
            }
        }

        return $form_id;
    }

    /**
     * Delete a form with it's input fields
     *
     * @param  integer  $form_id
     * @param  boolean $force
     *
     * @return void
     */
    public function delete( $form_id, $force = true ) {
        global $wpdb;

        wp_delete_post( $form_id, $force );

        // delete form inputs as WP doesn't know the relationship
        $wpdb->delete( $wpdb->posts,
            array(
                'post_parent' => $form_id,
                'post_type'   => 'wpuf_input'
            )
        );
    }

    /**
     * API to duplicate a form
     *
     * @param int $_form_id
     *
     * @return int New duplicated form id
     */
    function duplicate( $_form_id ) {

       return wpuf_duplicate_form( $_form_id ) ;
    }
}
