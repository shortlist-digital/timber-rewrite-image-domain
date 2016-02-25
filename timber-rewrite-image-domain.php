<?php
/*
Plugin Name: Timber Rewrite Image Domain
Plugin URI: http://github.com/shortlist-digital/timber-rewrite-image-domain
Description: Rewrite Timber Image URLs from one domain to another.
Author: Jon Sherrard
Version: 0.1
Author URI: http://slimndap.com
*/
class TimberRewriteImageDomain {

  function __construct() {
    add_action('plugins_loaded', array($this, 'plugins_loaded'));
    $this->rewrite_from_domain = get_field('rewrite_from_domain', 'options');
    $this->rewrite_to_domain = get_field('rewrite_to_domain', 'options');
    if(function_exists('register_field_group')) {
      $this->register_acf_options();
    }
  }

  public function plugins_loaded() {
    add_action('timber/twig/filters', array($this, 'twig_apply_filters'), 99);
    add_action('twig_apply_filters', array($this, 'twig_apply_filters'), 99);
    add_filter('timber_image_src', array($this, 'timber_image_src'));
  }

  public function twig_apply_filters($twig) {
    $twig->addFilter('resize', new Twig_Filter_Function(array($this, 'timber_image_src_resize')));
    $twig->addFilter('letterbox', new Twig_Filter_Function(array($this, 'timber_image_src')));
    return $twig;
  }

  private function swap_domain($url) {
    return str_replace($this->rewrite_from_domain, $this->rewrite_to_domain, $url);
  }

  public function timber_image_src($src) {
    return $this->swap_domain($src);
  }

  public function timber_image_src_resize($src, $w, $h = 0, $crop = 'default') {
    if ($src) {
      //if (!array_key_exists(3, func_get_args())) {
      $ext = pathinfo($src, PATHINFO_EXTENSION);
      $src_base = str_replace(".$ext","", $src);
      // Lord forgive me for I hath sinned
      $new_src = "$src_base-".$w."x".$h."-c-$crop.$ext";
      $new_src = $this->swap_domain($new_src);
      try {
        $headers = get_headers($new_src);
      } catch (Exception $e) {
        print_r($new_src);die;
      }
      if (strpos($headers[0], "404")) {
        return $this->swap_domain($src);
      } else {
        return $new_src;
      }
    }
  }

  private function register_acf_options() {
    register_field_group(array (
      'key' => 'group_timber_rewrite_image_domain_plugin',
      'title' => 'Rewrite Image Domain',
      'fields' => array (
        array (
          'key' => 'rewrite_from_domain',
          'label' => 'Rewrite FROM domain',
          'name' => 'rewrite_from_domain',
          'type' => 'url',
          'instructions' => 'e.g: http://local.emeraldstreet.com',
          'wrapper' => array (
            'width' => '50%',
          ),
        ),
        array (
          'key' => 'rewrite_to_domain',
          'label' => 'Rewrite To Domain',
          'name' => 'rewrite_to_domain',
          'type' => 'url',
          'instructions' => 'e.g: http://www.emeraldstreet.com',
          'wrapper' => array (
            'width' => '50%',
          ),
        ),
      ),
      'location' => array (
        array (
          array (
            'param' => 'options_page',
            'operator' => '==',
            'value' => 'acf-options',
          ),
        ),
      ),
    ));
  }
}

new TimberRewriteImageDomain();
