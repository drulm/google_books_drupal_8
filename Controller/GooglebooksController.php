<?php

/**
 * @file
 * Contains \Drupal\google_books\Controller\GooglebooksController.php
 */
 
namespace Drupal\google_books\Controller;

use Drupal\Core\Controller\ControllerBase;

class GooglebooksController extends ControllerBase
{
    public function content()
    {
        return array(
        '#theme' => 'googlebooks_template',
        '#title_anchor' => null,
        '#worldcat_link' => null,
        '#librarything_link' => null,
        '#openlibrary_link' => null,
        '#image' => null,
        '#reader' => null,
        '#bib_fields' => null,
        '#image_height' => null,
        '#image_width' => null,
        '#reader_height' => null,
        '#reader_width' => null,
        '#api_key' => null,
        '#info_link' => null,
    '#isbn' => null,
    );
    }
}
