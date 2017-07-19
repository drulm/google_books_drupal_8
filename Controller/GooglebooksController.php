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
    );

  }
}