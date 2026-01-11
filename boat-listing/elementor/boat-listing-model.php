<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class boat_listing_models extends \ElementorPro\Modules\Forms\Fields\Field_Base {

    public $models;

    public function __construct() {
        parent::__construct();
        add_action( 'elementor/preview/init', [ $this, 'editor_preview_footer' ] );

        $helper = new Boat_Listing_Helper();
       // $this->models = $helper->fetch_all_models(); // store for later
    }

    public function get_type() {
        return 'boat-model';
    }

    public function get_name() {
        return esc_html__( 'Boat Models', BL_TEXT_DOMAIN );
    }

    public function render( $item, $item_index, $form ): void {
        
        // Debug: Let's see what's in the $item array
        // error_log('Model Field item data: ' . print_r($item, true));
        
        // Get the field name/id - try different possible keys
        $field_id = '';
        if ( ! empty( $item['custom_id'] ) ) {
            $field_id = $item['custom_id'];
        } elseif ( ! empty( $item['_id'] ) ) {
            $field_id = $item['_id'];
        } elseif ( ! empty( $item['field_id'] ) ) {
            $field_id = $item['field_id'];
        } else {
            $field_id = 'field_' . $item_index;
        }
        
        $field_name = 'form_fields[' . $field_id . ']';
        $html_field_id = 'form-field-' . $field_id;

        $form->add_render_attribute(
            'select_model' . $item_index,
            [
                'class' => 'elementor-field elementor-select elementor-field-type-' . $this->get_type(),
                'id' => $html_field_id,
                'name' => $field_name, // This is crucial for form submission
                'data-field-type' => $this->get_type(),
            ]
        );

        // Add required attribute if field is required
        if ( ! empty( $item['required'] ) ) {
            $form->add_render_attribute( 'select_model' . $item_index, 'required', 'required' );
            $form->add_render_attribute( 'select_model' . $item_index, 'aria-required', 'true' );
        }

        echo '<select ' . $form->get_render_attribute_string( 'select_model' . $item_index ) . '>';

        // Default option
        $placeholder = ! empty( $item['placeholder'] ) ? $item['placeholder'] : esc_html__( 'Select Model', BL_TEXT_DOMAIN );
        echo '<option value="">' . esc_html( $placeholder ) . '</option>';

        if ( ! empty( $this->models ) && is_array( $this->models ) ) {
            foreach ( $this->models as $model ) {
                $data = $model['model_data'];
                $value = $data['name'];
                $selected = '';
                
                // Check for default/selected value - try different possible keys
                $default_value = '';
                if ( ! empty( $item['field_value'] ) ) {
                    $default_value = $item['field_value'];
                } elseif ( ! empty( $item['default_value'] ) ) {
                    $default_value = $item['default_value'];
                } elseif ( ! empty( $item['value'] ) ) {
                    $default_value = $item['value'];
                }
                
                if ( !empty( $default_value ) && $default_value === $value ) {
                    $selected = ' selected="selected"';
                }
                
                echo '<option value="' . esc_attr( $value ) . '"' . $selected . '>' . esc_html( $value ) . '</option>';
            }
        }

        echo '</select>';
    }

    public function editor_preview_footer(): void {
        add_action( 'wp_footer', [ $this, 'content_template_script' ] );
    }

    public function content_template_script(): void {
        ?>
        <script>
        jQuery( document ).ready( () => {
            elementor.hooks.addFilter(
                'elementor_pro/forms/content_template/field/<?php echo $this->get_type(); ?>',
                function ( inputField, item, i ) {
                    // Get field ID with fallbacks
                    const fieldIdValue = item.custom_id || item._id || ('field_' + i);
                    const fieldId = `form-field-${fieldIdValue}`;
                    const fieldClass = `elementor-field elementor-select elementor-field-type-<?php echo $this->get_type(); ?> ${item.css_classes || ''}`;
                    const fieldName = `form_fields[${fieldIdValue}]`;
                    const required = item.required ? 'required' : '';
                    const placeholder = item.placeholder || '<?php echo esc_js( __( 'Select Model', BL_TEXT_DOMAIN ) ); ?>';

                    // Default option
                    let options = `<option value="">${placeholder}</option>`;

                    <?php if ( ! empty( $this->models ) && is_array( $this->models ) ) : ?>
                        <?php foreach ( $this->models as $model ) :
                            $data = $model['model_data'];
                            $value = esc_js( $data['name'] );
                            $label = esc_js( $data['name'] );
                        ?>
                            options += `<option value="<?php echo $value; ?>"><?php echo $label; ?></option>`;
                        <?php endforeach; ?>
                    <?php endif; ?>

                    return `<select id="${fieldId}" class="${fieldClass}" name="${fieldName}" ${required} data-field-type="<?php echo $this->get_type(); ?>">${options}</select>`;
                }, 10, 3
            );
        });
        </script>
        <?php
    }

    // This method is essential for saving the field value
    public function sanitize_field( $value, $field ) {
        if ( is_array( $value ) ) {
            $value = implode( ', ', $value );
        }
        return sanitize_text_field( $value );
    }

    // This method helps process the field value before saving
    public function process_field( $field, $record, $ajax_handler ) {
        // You can add custom processing logic here if needed
        // This method is called during form submission
    }

    // Make sure the field value is properly retrieved
    public function get_field_content( $field, $field_value, $record ) {
        return sanitize_text_field( $field_value );
    }

    // Add validation if needed
    public function validation( $field, $record, $ajax_handler ) {
        if ( ! empty( $field['required'] ) && empty( $field['value'] ) ) {
            $ajax_handler->add_error( 
                $field['id'], 
                esc_html__( 'This field is required.', BL_TEXT_DOMAIN ) 
            );
        }
    }
}

