<?php

namespace Drupal\google_books\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * 
 */
class GooglebooksController extends ControllerBase {

  /**
   * 
   * @return type
   */
  public function content() {
    return [
      '#theme' => 'googlebooks_template',
      '#title_link' => NULL,
      '#page_curl' => NULL,
      '#title_anchor' => NULL,
      '#worldcat_link' => NULL,
      '#librarything_link' => NULL,
      '#openlibrary_link' => NULL,
      '#image' => NULL,
      '#reader' => NULL,
      '#bib_fields' => NULL,
      '#image_height' => NULL,
      '#image_width' => NULL,
      '#reader_height' => NULL,
      '#reader_width' => NULL,
      '#api_key' => NULL,
      '#info_link' => NULL,
      '#isbn' => NULL,
      '#prevent_duplicate_values' => NULL,
    ];
  }

}
