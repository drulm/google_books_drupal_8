<?php

/**
 * @file
 * Contains \Drupal\google_books\Plugin\Filter\Googlebooks.
 */

namespace Drupal\google_books\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * Provides a filter to limit allowed HTML tags.
 *
 * @Filter(
 *   id = "google_books",
 *   title = @Translation("Google Books"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_HTML_RESTRICTOR,
 *   settings = {
 *   },
 *   weight = -11
 * )
 */
class Googlebooks extends FilterBase {
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return text;
  }

  /**
   * {@inheritdoc}
   */
  public function getHTMLRestrictions() {
    return array('allowed' => array());
  }

  /**
   * {@inheritdoc}
   */
  
  public function tips($long = FALSE) {
    return "";
  }
}