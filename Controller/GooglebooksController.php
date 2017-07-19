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
		'#test_var' => $this->t('Test Value'),
		'#title_anchor' => $this->t('Test Value'),
		'#worldcat' => $this->t('Test Value'),
		'#librarything' => $this->t('Test Value'),
		'#openlibrary' => $this->t('Test Value'),
		'#image' => $this->t('Test Value'),
		'#reader' => $this->t('Test Value'),
		'#bib_fields' => $this->t('Test Value'),
		'#image_height' => $this->t('Test Value'),
		'#image_width' => $this->t('Test Value'),
		'#reader_height' => $this->t('Test Value'),
		'#reader_width' => $this->t('Test Value'),
		'#api_key' => $this->t('Test Value'),
    );

  }
}