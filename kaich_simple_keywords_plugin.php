<?php
    /*
        Plugin Name: Simple seo keywords plugin
        Plugin URI: 
        Description: Add keywords, description to product and product category, product tag, page
        Version: 1.0
        Author: KAICH
        Author URI:
        License: GPL2
        License URI: http://www.gnu.org/licenses/gpl-2.0.html 
    */

    class kaich_simple_keywords {
        public function __construct() {
            // add meta box to product and page
            add_action('add_meta_boxes', array($this, 'add_keywords_meta_boxes'));
            add_action('save_post', array($this, 'save_keywords'));
            add_action('wp_head', array($this, 'keywords_display'));

            // add meta box to product category
            add_action('product_cat_edit_form_fields', array($this, 'edit_keywords_meta_boxes_category'));
            add_action('product_cat_add_form_fields', array($this, 'add_keywords_meta_boxes_category'));
            add_action('edited_product_cat', array($this, 'save_keywords_taxonomy'));

            // add meta box to product tag
            add_action('product_tag_edit_form_fields', array($this, 'edit_keywords_meta_boxes_category'));
            add_action('product_tag_add_form_fields', array($this, 'add_keywords_meta_boxes_category'));
            add_action('edited_product_tag', array($this, 'save_keywords_taxonomy'));
        }

        public function add_keywords_meta_boxes() {
            add_meta_box('wp_keywords_meta_box', 'Seo keywords', array($this, 'seo_meta_box_display'), 'product', 'normal', 'default');
            add_meta_box('wp_keywords_meta_box', 'Seo keywords', array($this, 'seo_meta_box_display'), 'page', 'normal', 'default');
        }

        public function add_keywords_meta_boxes_category() {
            ?>
            <div class="form-field">
                <label>SEO Keywords</label>
                <input type="text" name="wp_seo_keywords" id="wp_seo_keywords" value="" />
            </div>
            <div class="form-field">
                <label>SEO Description</label>
                <textarea name="wp_seo_description" rows="5" cols="40" size="250" id="wp_seo_description"></textarea>
            </div>
            <?php
        }

        public function edit_keywords_meta_boxes_category($tag) {
            $wp_seo_keywords = get_term_meta($tag->term_id, 'wp_seo_keywords', true);
            $wp_seo_description = get_term_meta($tag->term_id, 'wp_seo_description', true);
            ?>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="product_cat_keywords">SEO Keywords</label>
                </th>
                <td>
                    <input type="text" name="wp_seo_keywords" id="wp_seo_keywords" value="<?php echo $wp_seo_keywords ?>" />
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row" valign="top">
                    <label for="product_cat_description">SEO Description</label>
                </th>
                <td>
                    <textarea name="wp_seo_description" rows="5" cols="40" size="250" id="wp_seo_description"><?php echo $wp_seo_description ?></textarea>
                </td>
            </tr>
            <?php
        }

        public function seo_meta_box_display($post) {
            wp_nonce_field('wp_seo_nonce', 'wp_seo_nonce_field');

            $wp_seo_keywords = get_post_meta($post->ID, 'wp_seo_keywords', true);
            $wp_seo_description = get_post_meta($post->ID, 'wp_seo_description', true);
            ?>
            <div class="field-container">
                <div class="field">
                    <label form="wp_seo_keywords">Keywords</label>
                    <input type="text" name="wp_seo_keywords" id="wp_seo_keywords" value="<?php echo $wp_seo_keywords; ?>" />
                </div>
                <div class="field">
                    <label form="wp_seo_description">Description</label>
                    <textarea name="wp_seo_description" rows="5" cols="40" size="250" id="wp_seo_description"><?php echo $wp_seo_description ?></textarea>
                </div>
            </div>
            <?php
        }

        public function save_keywords($post_id) {
            if (!isset($_POST['wp_seo_nonce_field'])) {
                return $post_id;
            }
            if (!wp_verify_nonce($_POST['wp_seo_nonce_field'], 'wp_seo_nonce')) {
                return $post_id;
            }
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }
            $wp_seo_keywords = isset($_POST['wp_seo_keywords']) ? sanitize_text_field($_POST['wp_seo_keywords']) : '';
            $wp_seo_description = isset($_POST['wp_seo_description']) ? sanitize_text_field($_POST['wp_seo_description']) : '';

            update_post_meta($post_id, 'wp_seo_keywords', $wp_seo_keywords);
            update_post_meta($post_id, 'wp_seo_description', $wp_seo_description);
        }

        public function save_keywords_taxonomy($term_id) {
            $wp_seo_keywords = isset($_POST['wp_seo_keywords']) ? sanitize_text_field($_POST['wp_seo_keywords']) : '';
            $wp_seo_description = isset($_POST['wp_seo_description']) ? sanitize_text_field($_POST['wp_seo_description']) : '';

            update_term_meta($term_id, 'wp_seo_keywords', $wp_seo_keywords);
            update_term_meta($term_id, 'wp_seo_description', $wp_seo_description);
        }

        public function keywords_display() {
            $wp_seo_keywords = "";
            $wp_seo_description = "";
            if (is_singular('product') || is_page()) {
                $post = get_queried_object();
                $wp_seo_keywords = get_post_meta($post->ID, 'wp_seo_keywords', true);
                $wp_seo_description = get_post_meta($post->ID, 'wp_seo_description', true);
                if (empty($wp_seo_keywords)) {
                    $wp_seo_keywords = $post->post_title;
                }
            }
            if (is_tax('product_cat') || is_tax('product_tag')) {
                $term = get_queried_object();
                $wp_seo_keywords = get_term_meta($term->term_id, 'wp_seo_keywords', true);
                $wp_seo_description = get_term_meta($term->term_id, 'wp_seo_description', true);
                if (empty($wp_seo_keywords)) {
                    $wp_seo_keywords = $term->name;
                }
            }
            if (!empty($wp_seo_keywords)) {
                echo '<meta name="keywords" content="'.$wp_seo_keywords.'">';
            }
            if (!empty($wp_seo_description)) {
                echo '<meta name="description" content="'.$wp_seo_description.'">';
            }
        }
    }
    $kaich_simple_keywords = new kaich_simple_keywords();
?>
