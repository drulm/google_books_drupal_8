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
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE,
 *   weight = -11,
 * )
 */
class Googlebooks extends FilterBase {
  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    /*$input = &$form_state->getUserInput(); 
    $input = $input['filters']['google_books']['settings'];
    dpm($input);
    */
    $form['api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Google Books API Key'),
      '#size' => 60,
      '#maxlength' => 80,
      '#description' => t('Register your key at: (fill in link later)'),
      '#default_value' => $this->settings['api_key'],
    );
    $form['worldcat'] = array(
      '#type' => 'checkbox',
      '#title' => t('Link to WorldCat'),
      '#default_value' => $this->settings['worldcat'],
    );
    $form['librarything'] = array(
      '#type' => 'checkbox',
      '#title' => t('Link to LibraryThing'),
      '#default_value' => $this->settings['librarything'],
      //'#default_value' => isset($input['librarything']) ? $input['librarything'] : FALSE,
    );
    $form['openlibrary'] = array(
      '#type' => 'checkbox',
      '#title' => t('Link to Open Library'),
      '#default_value' => $this->settings['openlibrary'],
      //'#default_value' => isset($input['openlibrary']) ? $input['openlibrary'] : FALSE,
    );
    $form['image'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include Google Books cover image'),
      '#default_value' => $this->settings['image'],
      //'#default_value' => isset($input['image']) ? $input['image'] : FALSE,
    );
    $form['image_height'] = array(
      '#type' => 'textfield',
      '#title' => t('Image height'),
      '#size' => 4,
      '#maxlength' => 4,
      // @TODO Fix validator
      // '#element_validate' => array('_google_books_image_or_reader_valid_int_size'),
      '#description' => t('Height of Google cover image'),
      '#default_value' => $this->settings['image_height'],
      //'#default_value' => isset($input['image_height']) ? $input['image_height'] : FALSE,
    );
    $form['image_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Image width'),
      '#size' => 4,
      '#maxlength' => 4,
      // @TODO Fix validator
      // '#element_validate' => array('_google_books_image_or_reader_valid_int_size'),
      '#description' => t('Width of Google cover image'),
      '#default_value' => $this->settings['image_width'],
      //'#default_value' => isset($input['image_width']) ? $input['image_width'] : FALSE,
    );
    $form['reader'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include the Google Books reader'),
      '#default_value' => $this->settings['reader'],
      //'#default_value' => isset($input['reader']) ? $input['reader'] : FALSE,
    );
    $form['reader_height'] = array(
      '#type' => 'textfield',
      '#title' => t('Reader height'),
      '#size' => 4,
      '#maxlength' => 4,
      // @TODO Fix validator
      //'#element_validate' => array('_google_books_image_or_reader_valid_int_size'),
      '#description' => t('Height of Google reader'),
      '#default_value' => $this->settings['reader_height'],
      //'#default_value' => isset($input['reader_height']) ? $input['reader_height'] : FALSE,
    );
    $form['reader_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Reader width'),
      '#size' => 6,
      '#maxlength' => 6,
      // @TODO Fix validator
      //'#element_validate' => array('_google_books_image_or_reader_valid_int_size'),
      '#description' => t('Width of Google reader'),
      '#default_value' => $this->settings['reader_width'],
      //'#default_value' => isset($input['reader_width']) ? $input['reader_width'] : FALSE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array(
      'google_books' => t('Google Books'),
    );
  }
  
  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return $text;
  }

  /**
   * {@inheritdoc}
   */
  /*public function getHTMLRestrictions() {
    return array('allowed' => array());
  }*/

  /**
   * {@inheritdoc}
   */
  
  /*public function tips($long = FALSE) {
    return "";
  }*/
}