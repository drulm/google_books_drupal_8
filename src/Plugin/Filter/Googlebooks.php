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

/*
 * Holds the address of the books.google.com JSON call
 */
define('GOOGLE_BOOKS_EXTERN_JS', 'https://www.google.com/jsapi');

/*
 * Has the Google Books reader height default.
 */
define('GOOGLE_BOOKS_DEFAULT_READER_HEIGHT', '500');

/*
 * Has the Google Books reader width default.
 */
define('GOOGLE_BOOKS_DEFAULT_READER_WIDTH', '400');

/*
 * GOOGLE_BOOKS_API_ROOT is the path for the cURL request
 */
define("GOOGLE_BOOKS_API_ROOT", 'https://www.googleapis.com/books/v1/volumes?q=');

/*
 * GOOGLE_BOOKS_CACHE_PERIOD is the time to keep data in the book cache in secs.
 */
define("GOOGLE_BOOKS_CACHE_PERIOD", 24 * 60 * 60);


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
    $text .= " More Text";
    return new FilterProcessResult(_filter_url($text, $this));
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
  
  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return t('Put a Google Books search term between square brackets like this:
        [google_books:The Hobbit] or [google_books:9780618154012] or [google_books:Rucker+Software]
        and this will filter the input to replace with Google Books data
        and images from http://books.google.com');
    }
  }
  
/**
 * This is the main filter callback process for the google book filter module.
 *
 * Stated @ api.drupal.org hook_filter_FILTER_process is not really a hook.
 *
 * @param string $text
 *   Text to match.
 *
 * @param object $filter
 *   The filter as information stdClass object.
 *
 * @param object $format
 *   The text format type stdClass object.
 *
 * @param string $langcode
 *   The language code.
 *
 * @param bool $cache
 *   TRUE if cachable, FALSE if not.
 *
 * @param string $cache_id
 *   The ID of the cache.
 *
 * @return string
 *   Returns string of the filtered HTML for google_books.
 *
 * @see google_books_filter_info()
 */
  // function google_books_filter_process($text, $filter, $format, $langcode, $cache, $cache_id) {
  private function google_books_filter_process($text) {
    preg_match_all('/\[google_books:(.*)\]/', $text, $match);
    $tag = $match[0];
    $book = array();
    foreach ($match[1] as $i => $val) {
      $book[$i] = google_books_retrieve_bookdata(
        $match[1][$i],
        $filter->settings['google_books_link']['worldcat'],
        $filter->settings['google_books_link']['librarything'],
        $filter->settings['google_books_link']['openlibrary'],
        $filter->settings['google_books_image']['image'],
        $filter->settings['google_books_reader']['reader'],
        $filter->settings['google_books']['bib_fields'],
        $filter->settings['google_books_image']['image_height'],
        $filter->settings['google_books_image']['image_width'],
        $filter->settings['google_books_reader']['reader_height'],
        $filter->settings['google_books_reader']['reader_width'],
        $filter->settings['google_books']['api_key']
      );
    }
    $text = str_replace($tag, $book, $text);
    return $text;
  }
  
  /**
   * Gets the book data from Google Books and then displays it.
   *
   * This is the main filter processing function that does the work.
   *
   * @param string $id
   *   The full string pulled from inside the {{$id}} filter.
   *
   * @param string $worldcat_link
   *   True if setting for Worldcat link on.
   *
   * @param string $librarything_link
   *   True if setting for Library Thing link on.
   *
   * @param string $openlibrary_link
   *   True if setting for openlibrary is on.
   *
   * @param string $image_option
   *   Default param, display the book cover.
   *
   * @param string $reader_option
   *   Default param to display book reader Javascript.
   *
   * @param string $bib_field_select
   *   This is the array of True/False for the multi-field.
   *
   * @param string $image_height
   *   Default height of Image.
   *
   * @param string $image_width
   *   Default width of Image.
   *
   * @param string $reader_height
   *   Default Height of Reader.
   *
   * @param string $reader_width
   *   Default Width of Reader.
   *
   * @return string
   *   Returns the HTML data for google_books after filtering.
   *
   * @see google_books_filter_process
   */
  private function google_books_retrieve_bookdata(
    $id,
    $worldcat_link,
    $librarything_link,
    $openlibrary_link,
    $image_option,
    $reader_option,
    $bib_field_select,
    $image_height,
    $image_width,
    $reader_height,
    $reader_width,
    $api_key
  ) {
    // Get all the Google Books permissible book data fields.
    $bib_fields = google_books_api_bib_field_array();

    // Set the API Key to NULL if blank.
    $api_key = trim($api_key);
    $api_key = $api_key == "" ? NULL : $api_key;

    // Separate parameters by '|' delimiter and clean data.
    $params = explode("|", $id);
    array_map('filter_xss', $params);
    $search_string = $params[0];

    // Get the Google Books data.
    // Ignore if google_books_api_get_google_books_data returns NULL.
    $bib = google_books_api_get_google_books_data($search_string, 0, $api_key);
    if ($bib != NULL) {
      // Clean the data from Google.
      array_map('filter_xss', $bib);
      $bib['infoLink'] = check_url($bib['infoLink']);

      // Start the parameter handling.
      unset($params[0]);
      $params = array_map('trim', $params);

      // Set the fixed parameters explicitly (not the data fields).
      google_books_set_param($params, $worldcat_link, "worldcat", 1);
      google_books_set_param($params, $worldcat_link, "no_worldcat", 0);
      google_books_set_param($params, $openlibrary_link, "openlibrary", 1);
      google_books_set_param($params, $openlibrary_link, "no_openlibrary", 0);
      google_books_set_param($params, $librarything_link, "librarything", 1);
      google_books_set_param($params, $librarything_link, "no_librarything", 0);
      google_books_set_param($params, $page_curl, "pagecurl", 1);
      google_books_set_param($params, $page_curl, "no_pagecurl", 0);
      google_books_set_param($params, $title_link, "titlelink", 1);
      google_books_set_param($params, $title_link, "no_titlelink", 0);
      google_books_set_param($params, $image_on, "image", 1);
      google_books_set_param($params, $image_on, "no_image", 0);
      google_books_set_param($params, $reader_on, "reader", 1);
      google_books_set_param($params, $reader_on, "no_reader", 0);

      // Set the data field parameters explicitly.
      $bib_field_select_explicit = array();
      foreach ($bib_fields as $i => $field_name) {
        $bib_field_select_explicit[$i] = "";
        google_books_set_param($params, $bib_field_select_explicit[$i], $field_name, "$i");
        google_books_set_param($params, $bib_field_select_explicit[$i], "no_" . $field_name, FALSE);
      }

      // Merge the selected parameters with the global bib field options.
      // Use the and operation to default to off.
      foreach ($bib_field_select_explicit as $i => $field_name) {
        if ($bib_field_select_explicit[$i]) {
          $bib_field_select[$i] = $bib_field_select_explicit[$i];
        }
        if ($bib_field_select_explicit[$i] === FALSE) {
          unset($bib_field_select[$i]);
        }
      }

      // Start of the data handling.
      // If the data is sound then continue.
      if ($bib != FALSE) {
        // Get the ISBN.
        $isbn = isset($bib['identifier']) ? google_books_get_isbn($bib['identifier']) : "";
        // Build up the the selected bib fields fields.
        $selected_bibs = array();
        foreach ($bib_field_select as $i => $k) {
          $field = $bib_fields[$i];
          if (isset($bib[$field])) {
            $selected_bibs[$field] = $bib [$field];
          }
        }
      }

      // Build up remaining output to send to template.
      // Pass all information fields for themers future use.
      $vars['selected_bibs'] = $selected_bibs;
      $vars['isbn'] = $isbn;
      $vars['title_link'] = $title_link;
      $vars['title'] = isset($bib['title']) ? $bib['title'] : NULL;
      $vars['thumbnail'] = isset($bib['thumbnail']) ? $bib['thumbnail'] : NULL;
      $vars['embeddable'] = isset($bib['embeddable']) ? $bib['embeddable'] : NULL;
      $vars['info_link'] = isset($bib['infoLink']) ? $bib['infoLink'] : NULL;
      $vars['librarything_link'] = $librarything_link;
      $vars['openlibrary_link'] = $openlibrary_link;
      $vars['worldcat_link'] = $worldcat_link;
      $vars['image_height'] = $image_height;
      $vars['image_width'] = $image_width;
      $vars['reader_height'] = $reader_height;
      $vars['reader_width'] = $reader_width;
      $vars['image_option'] = $image_option;
      $vars['image_on'] = $image_on;
      $vars['page_curl'] = $page_curl;
      $vars['reader_option'] = $reader_option;
      $vars['reader_on'] = $reader_on;

      // Send the variables to the theme.
      $output = theme('google_books_aggregate', $vars);
      return $output;
    }
    else {
      // Nothing found so return empty string.
      return "";
    }
  }
  
  /**
   * Take a string of book identifiers, and choose one.
   *
   * @param string $identifiers
   *   Takes a string of IDs delimited by '|' characters.
   *
   * @return string
   *   This is the ISBN selected.
   */
  private function google_books_get_isbn($identifiers) {
    // Pull an ISBN if we have it, prefer the last
    // which should be an ISBN 13 although may be something else.
    $identifier_list = explode("|", $identifiers);
    $num_of_identifiers = count($identifier_list);
    $isbn = trim($identifier_list[$num_of_identifiers - 1]);
    return is_numeric($isbn) ? $isbn : "";
  }
  
  
  /**
   * Takes book field data and makes it a link if it address present.
   *
   * @param string $address
   *   The biblio field string, which might be an address.
   *
   * @return string
   *   Returns an HTML <a></a> link if there is a valid address in $address.
   */
  private function google_books_make_html_link($address) {
    if (valid_url($address, $absolute = TRUE)) {
      return l(t('link'), $address, array('attributes' => array('rel' => 'nofollow', 'target' => '_blank')));
    }
    return check_plain($address);
  }
  
  
  /**
   * Sets a parameter variable by reference based on presence in array.
   *
   * @param array $params
   *   The parameter array.
   *
   * @param int|string $flag_var
   *   The parameter variable to set.
   *
   * @param string $value
   *   The var to find in the array (needle).
   *
   * @param int|bool $set
   *   What $flag_var will be set to if TRUE.
   */
  private function google_books_set_param($params, &$flag_var, $value, $set) {
    if (in_array($value, $params) === TRUE) {
      $flag_var = $set;
    }
  }
  
 /**
  * These are the fields that are displayable in google books.
  *
  * @return array
  *   Returns array of book data field names returned from books.google.com.
  */
 private function google_books_api_bib_field_array() {
   return array(
     'kind',
     'id',
     'etag',
     'selfLink',
     'volumeInfo',
     'title',
     'authors',
     'publisher',
     'publishedDate',
     'description',
     'industryIdentifiers',
     'type',
     'identifier',
     'pageCount',
     'dimensions',
     'height',
     'width',
     'thickness',
     'printType',
     'mainCategory',
     'categories',
     'averageRating',
     'ratingsCount',
     'contentVersion',
     'imageLinks',
     'smallThumbnail',
     'thumbnail',
     'small',
     'medium',
     'large',
     'extraLarge',
     'language',
     'previewLink',
     'infoLink',
     'canonicalVolumeLink',
     'saleInfo',
     'country',
     'saleability',
     'isEbook',
     'listPrice',
     'amount',
     'currencyCode',
     'buyLink',
     'accessInfo',
     'viewability',
     'embeddable',
     'publicDomain',
     'textToSpeechPermission',
     'epub',
     'acsTokenLink',
     'accessViewStatus',
     'webReaderLink',
     'isAvailable',
   );
 }
 
 
 /**
  * Gets the book data from Google Books and puts in flat array.
  *
  * Multiple data fields are delimited by '|' character in string.
  * This is the fuction to call if the caller only wants an array. If the
  * caller needs *everything* google returns (which is fine) then the user
  * should use the google_books_api_cached_request( $path ) function.
  *
  * @param string $id
  *   The {{ $id }} passed from the filter text.
  *
  * @param int $version_num
  *   The version of the book returned from the search to use
  *
  * @return array
  *   Field names used to index the book data for each field.
  */
 private function google_books_api_get_google_books_data($id, $version_num, $api_key = NULL) {

   // Clean search string of spaces, turn into '+'.
   $id = google_books_api_clean_search_id($id);

   // Get all the arrays from the query.
   $bookkeys = google_books_api_cached_request($id, $api_key);

   // Decode into array to be able to scan.
   $json_array_google_books_data = drupal_json_decode($bookkeys, TRUE);

   $versions = $json_array_google_books_data['totalItems'];

   // Check the number of versions returned by Google Books.
   if ($versions > 0 && $version_num < $versions) {

     // Grab the first result.
     $bookkeyresult = $json_array_google_books_data['items'][$version_num];

     // Extract the results into one big string with delimiters.
     $book_str = google_books_api_demark_and_flatten($bookkeyresult);

     // Build array for this.
     $bib = array();
     $fields = explode("|||", $book_str);
     for ($i = 1; $i < count($fields); $i += 2) {
       $fieldname = $fields[$i];
       if (strpos($fields[$i + 1], "[[[") === FALSE) {
         $value = trim(str_replace("(((", "", $fields[$i + 1]));
       }
       else {
         $sub_value = "";
         $sub_fields = explode("[[[", $fields[$i + 1]);
         for ($j = 1; $j < count($sub_fields); $j += 2) {
           if ($j != 1 && !empty($sub_fields[$j + 1])) {
             $sub_value .= " | ";
           }
           $sub_value .= trim(str_replace("(((", "", $sub_fields[$j + 1]));
         }
         $value = $sub_value;
       }
       if (!empty($value)) {
         google_books_api_assign_bib_array($bib, $fieldname, $value);
       }
     }
     return $bib;
   }
   else {
     return NULL;
   }
 }
 
 
 /**
  * Pulls out only the biblio values we need.
  *
  * @param array &$barr
  *   Reference (for speed) to JSON data from a single book search result.
  *
  * @return string
  *   One big string of all book data with delimiters used to expand to arrays.
  */
 private function google_books_api_demark_and_flatten(&$barr) {

   // Get the bib fields and go through the array.
   $bib_fields = google_books_api_bib_field_array();
   $book_html = "";

   // Loop through array struture recursively.
   foreach ($barr as $key => $value) {
     if (!is_array($value) && $value != "") {
       $effective_key = is_int($key) ? '[[[///' . $key . '[[[' : '|||' . $key . '|||';
       $book_html .= "$effective_key ((($value(((";
     }
     else {
       // If there is a sub array, call this same function to traverse it.
       if (is_array($value)) {
         $sub_bib = google_books_api_demark_and_flatten($value, $bib_fields);
       }
       else {
         $sub_bib = $value;
       }
       if ($sub_bib != "") {
         $book_html .= "|||" . $key . "|||" . $sub_bib . "";
       }
     }
   }
   return $book_html;
 }
 
 /**
  * This function assigns the bib_array with index and value.
  *
  * If there is already data in the field, additional data items
  * are appended with the string delimiter '|'. The caller of this
  * function can then get all the data from the field by using explode()
  * or just print out this data field with the delimeters.
  *
  * @param array &$bib_array
  *   Reference to Array to modify.
  *
  * @param string $index
  *   The array index.
  *
  * @param string $value
  *   The value to assign to the array.
  */
 private function google_books_api_assign_bib_array(&$bib_array, $index, $value) {

   if (!array_key_exists($index, $bib_array)) {
     $bib_array[$index] = $value;
   }
   else {
     $bib_array[$index] = $bib_array[$index] . " | " . $value;
   }
 }
 
 /**
  * This returns JSON data from the cache if present.
  *
  * Else goes out and pulls the data in from books.google.com, caches the data
  * then returns it. Callers can use this function to pull all the
  * data Google Books API returns.
  *
  * @param string $path
  *   The search string, without any additional parameters.
  *
  * @return array
  *   Returns the cached JSON data for book located in cache, or fresh data.
  */
 private function google_books_api_cached_request($path, $api_key = NULL) {

   // Build the full path (and the cache key).
   $url_bookkeys = GOOGLE_BOOKS_API_ROOT . $path ;
   $bookkeys_hash = hash('sha256', $url_bookkeys);

   // @TODO Convert to D8 cache
   // See if it is cached.
   // $cached = cache_get($bookkeys_hash, 'cache_google_books_api');
   $cached = FALSE;

   // If is it IS cached, then just return the data from the cache.
   if ($cached !== FALSE) {
     // Check if the time has expired.
     if ($cached->expire < REQUEST_TIME) {
       cache_clear_all($bookkeys_hash, 'cache_google_books_api');
     }
     return $cached->data;
   }
   else {
     // Do it the slow way, go get the new data, and add API key if it exists.
     if ($api_key) {
       $url_bookkeys .= '&key=' . $api_key;
     }
     // $url_data = drupal_http_request($url_bookkeys);

     /* $request = Drupal::httpClient()->get($url_bookkeys);
     $request->addHeader('If-Modified-Since', gmdate(DATE_RFC1123, $last_fetched));
     try {
       $response = $request->send();
       // Expected result.
       $url_data = $response->getBody(TRUE);
     }
     catch (RequestException $e) {
       watchdog_exception('google_books', $e);
     } */

     if (isset($url_data->error) || !isset($url_data->data)) {
       drupal_set_message(t('Googlebooks: Could not retrieve data from google.com. @err', array('@err' => $url_data->error)), $type = 'error');
       return NULL;
     }
     $bookkeys = $url_data->data;

     // Set the cache if return from request is not NULL.
     if ($bookkeys != NULL) {
       cache_set($bookkeys_hash, $bookkeys, 'cache_google_books_api', REQUEST_TIME + GOOGLE_BOOKS_CACHE_PERIOD);
     }
     // Bookkeys is the data, so return it.
     return $bookkeys;
   }
 }
 
 /**
  * Gets the number of versions in the books.google.com JSON data.
  *
  * @param string $id
  *   The raw search string.
  *
  * @return int
  *   The count of the number of book versions returned in the JSON data.
  */
 private function google_books_api_get_googlebooks_version_count($id, $api_key = NULL) {

   // Cleanup the search ID.
   $id = google_books_api_clean_search_id($id);

   // Get all the arrays from the query.
   $bookkeys = google_books_api_cached_request($id, $api_key);
   if ($bookkeys != NULL) {
     // Decode into array to be able to scan.
     $json_array_google_books_data = drupal_json_decode($bookkeys, TRUE);
     return $json_array_google_books_data['totalItems'];
   }
   else {
     return NULL;
   }
 }
 
 
/**
  * Cleans the search ID to be used for Google API.
  *
  * @param string $id
  *   The raw search string.
  *
  * @return string
  *   The search string cleaned.
  */
 private function google_books_api_clean_search_id($id) {

   // Clean search string of spaces, turn into '+'.
   $id = trim($id);
   $dirt_id = array(" ");
   return str_replace($dirt_id, "+", $id);
 }

 



}