<?php

class TimberRewriteImageDomain {

  function __construct() {
    add_action('plugins_loaded', array($this,'plugins_loaded'));
  }

  private function plugins_loaded() {
    add_filter('timber_image_src', array($this, 'timber_image_src'));
  }

  private function timber_image_src($src) {
    $rewrite_from_domain = get_field('rewrite_from_domin', 'options');
    $rewrite_to_domain = get_field('rewrite_to_domain', 'options');
    return str_replace($rewrite_from_domain, $rewrite_to_domain, $src);
  }

}

new TimberRewriteImageDomain();
