<?php

//Calling from the Controller
/**
 * @file
 * Contains \Drupal\google_books\Controller\GooglebooksController.php
 */
 
namespace Drupal\google_books\Controller;

use Drupal\Core\Controller\ControllerBase;

class GooglebooksController extends ControllerBase {
  public function content() {
    return array(
		'#theme' => 'googlebooks_template',
		'#title_anchor' => NULL,
		'#worldcat' => NULL,
		'#librarything' => NULL,
		'#openlibrary' => NULL,
		'#image' => NULL,
		'#reader' => NULL,
		'#bib_fields' => NULL,
		'#image_height' => NULL,
		'#image_width' => NULL,
		'#reader_height' => NULL,
		'#reader_width' => NULL,
		'#api_key' => NULL,
    );

  }
}