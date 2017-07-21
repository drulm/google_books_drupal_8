<?php

namespace Drupal\google_books\Plugin\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use GuzzleHttp\Exception\RequestException;

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
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => t('Google Books API Key'),
      '#size' => 60,
      '#maxlength' => 80,
      '#description' => t('Register your key at: https://console.developers.google.com/apis'),
      '#default_value' => $this->settings['api_key'],
    ];
    $form['title_link'] = [
      '#type' => 'checkbox',
      '#title' => t('Title linked to GoogleBooks entry'),
      '#default_value' => $this->settings['title'],
    ];
    $form['worldcat'] = [
      '#type' => 'checkbox',
      '#title' => t('Link to WorldCat'),
      '#default_value' => $this->settings['worldcat'],
    ];
    $form['librarything'] = [
      '#type' => 'checkbox',
      '#title' => t('Link to LibraryThing'),
      '#default_value' => $this->settings['librarything'],
    ];
    $form['openlibrary'] = [
      '#type' => 'checkbox',
      '#title' => t('Link to Open Library'),
      '#default_value' => $this->settings['openlibrary'],
    ];
    $form['image'] = [
      '#type' => 'checkbox',
      '#title' => t('Include Google Books cover image'),
      '#default_value' => $this->settings['image'],
    ];
    $form['page_curl'] = [
      '#type' => 'checkbox',
      '#title' => t('Image page curl'),
      '#default_value' => $this->settings['page_curl'],
    ];
    $form['image_height'] = [
      '#type' => 'textfield',
      '#title' => t('Image height'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => t('Height of Google cover image'),
      '#default_value' => $this->settings['image_height'],
    ];
    $form['image_width'] = [
      '#type' => 'textfield',
      '#title' => t('Image width'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => t('Width of Google cover image'),
      '#default_value' => $this->settings['image_width'],
    ];
    $form['reader'] = [
      '#type' => 'checkbox',
      '#title' => t('Include the Google Books reader'),
      '#default_value' => $this->settings['reader'],
    ];
    $form['reader_height'] = [
      '#type' => 'textfield',
      '#title' => t('Reader height'),
      '#size' => 4,
      '#maxlength' => 4,
      '#description' => t('Height of Google reader'),
      '#default_value' => $this->settings['reader_height'],
    ];
    $form['reader_width'] = [
      '#type' => 'textfield',
      '#title' => t('Reader width'),
      '#size' => 6,
      '#maxlength' => 6,
      '#description' => t('Width of Google reader'),
      '#default_value' => $this->settings['reader_width'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [
      'google_books' => t('Google Books'),
    ];
  }


  /**
   * 
   * @param type $text
   * @param type $langcode
   * @return FilterProcessResult
   */
  public function process($text, $langcode) {
    $document = Html::load($text);

    // dpm($this->settings);
    // return new FilterProcessResult(Html::serialize($document));
    preg_match_all('/\[google_books:(.*)\]/', $text, $match);
    $tag = $match[0];
    $book = [];

    foreach ($match[1] as $i => $val) {
      $book[$i] = google_books_retrieve_bookdata(
      $match[1][$i],
      $this->settings['worldcat'],
      $this->settings['librarything'],
      $this->settings['openlibrary'],
      $this->settings['image'],
      $this->settings['reader'],
      // $this->settings['bib_fields'],.
      $this->settings['image_height'],
      $this->settings['image_width'],
      $this->settings['reader_height'],
      $this->settings['reader_width'],
      $this->settings['api_key']
      );

      $output = [
        '#theme' => 'googlebooks_template',
        '#title_anchor' => $book[$i]['title'],
        '#worldcat_link' => $book[$i]['worldcat_link'],
        '#librarything_link' => $book[$i]['librarything_link'],
        '#openlibrary_link' => $book[$i]['openlibrary_link'],
        '#image' => $book[$i]['thumbnail'],
        '#reader' => $book[$i]['title'],
        '#bib_fields' => 'bib_fields',
        '#image_height' => $book[$i]['image_height'],
        '#image_width' => $book[$i]['image_width'],
        '#reader_height' => $book[$i]['reader_height'],
        '#reader_width' => $book[$i]['reader_width'],
        '#info_link' => $book[$i]['info_link'],
        '#isbn' => $book[$i]['isbn'],
      ];

      $markup = render($output);

      $text = str_replace($tag[$i], $markup, $text);
    }

    return new FilterProcessResult($text);
  }

  /**
   * 
   * @param type $long
   * @return type
   */
  public function tips($long = FALSE) {
    if ($long) {
      return t('Put a Google Books search term between square brackets like this:
        [google_books:The Hobbit] or [google_books:9780618154012] or [google_books:Rucker+Software]
        and this will filter the input to replace with Google Books data
        and images from http://books.google.com');
    }
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

/**
 * Function google_books_filter_process($text, $filter, $format, $langcode, $cache, $cache_id) {.
 */
function google_books_filter_process($text) {
  preg_match_all('/\[google_books:(.*)\]/', $text, $match);
  $tag = $match[0];
  $book = [];
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
function google_books_retrieve_bookdata(
    $id,
    $worldcat_link,
    $librarything_link,
    $openlibrary_link,
    $image_option,
    $reader_option,
    // $bib_field_select,.
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
  // array_map('filter_xss', $params);.
  $search_string = $params[0];

  // Get the Google Books data.
  // Ignore if google_books_api_get_google_books_data returns NULL.
  $bib = google_books_api_get_google_books_data($search_string, 0, $api_key);
  if ($bib != NULL) {
    // Clean the data from Google.
    // array_map('filter_xss', $bib);.
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
    $bib_field_select_explicit = [];
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
      $selected_bibs = [];
      /*foreach ($bib_field_select as $i => $k) {
      $field = $bib_fields[$i];
      if (isset($bib[$field])) {
      $selected_bibs[$field] = $bib[$field];
      }
      }*/
      $selected_bibs = $bib;
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

    return $vars;
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
function google_books_get_isbn($identifiers) {
  // Pull an ISBN if we have it, prefer the last
  // which should be an ISBN 13 although may be something else.
  $identifier_list = explode("|", $identifiers);
  $num_of_identifiers = count($identifier_list);
  $isbn = trim($identifier_list[$num_of_identifiers - 1]);
  return is_numeric($isbn) ? $isbn : "";
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
function google_books_set_param($params, &$flag_var, $value, $set) {
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
function google_books_api_bib_field_array() {
  return [
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
  ];
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
 *   The version of the book returned from the search to use.
 *
 * @return array
 *   Field names used to index the book data for each field.
 */
function google_books_api_get_google_books_data($id, $version_num, $api_key = NULL) {

  // Clean search string of spaces, turn into '+'.
  $id = google_books_api_clean_search_id($id);

  // Get all the arrays from the query.
  $bookkeys = google_books_api_cached_request($id, $api_key);

  // Decode into array to be able to scan.
  $json_array_google_books_data = json_decode($bookkeys, TRUE);

  $versions = $json_array_google_books_data['totalItems'];

  // Check the number of versions returned by Google Books.
  if ($versions > 0 && $version_num < $versions) {

    // Grab the first result.
    $bookkeyresult = $json_array_google_books_data['items'][$version_num];

    // Extract the results into one big string with delimiters.
    $book_str = google_books_api_demark_and_flatten($bookkeyresult);
    // dpm($book_str);
    // Build array for this.
    $bib = [];
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
    // dpm($bib);
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
function google_books_api_demark_and_flatten(&$barr) {

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
function google_books_api_assign_bib_array(&$bib_array, $index, $value) {
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
function google_books_api_cached_request($path, $api_key = NULL) {

  // Build the full path (and the cache key).
  $url_bookkeys = GOOGLE_BOOKS_API_ROOT . $path;
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

    try {
      $url_data = (string) \Drupal::httpClient()
        ->get($url_bookkeys)
        ->getBody();
    }
    catch (RequestException $exception) {
      drupal_set_message(t('Googlebooks: Could not retrieve data from google.com. @err', ['%error' => $exception->getMessage()]), 'error');
      return FALSE;
    }

    $bookkeys = $url_data;

    // Set the cache if return from request is not NULL.
    // @TODO Cache!
    if ($bookkeys != NULL) {
      // cache_set($bookkeys_hash, $bookkeys, 'cache_google_books_api', REQUEST_TIME + GOOGLE_BOOKS_CACHE_PERIOD);.
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
/*function google_books_api_get_googlebooks_version_count($id, $api_key = NULL) {

  // Cleanup the search ID.
  $id = _google_books_api_clean_search_id($id);

  // Get all the arrays from the query.
  $bookkeys = google_books_api_cached_request($id, $api_key);
  // dpm($bookkeys);
  if ($bookkeys != NULL) {
    // Decode into array to be able to scan.
    $json_array_google_books_data = json_decode($bookkeys, TRUE);
    // dpm($json_array_google_books_data);
    return $json_array_google_books_data['totalItems'];
  }
  else {
    return NULL;
  }
}
 * 
 */

/**
 * Cleans the search ID to be used for Google API.
 *
 * @param string $id
 *   The raw search string.
 *
 * @return string
 *   The search string cleaned.
 */
function google_books_api_clean_search_id($id) {
  // Clean search string of spaces, turn into '+'.
  $id = trim($id);
  $dirt_id = [" "];
  return str_replace($dirt_id, "+", $id);
}

/**
 * Implements hook_theme().
 */
/**
function google_books_theme() {
  // Return the array describing the template name and vars.
  return [
    // The main theme template google_books.tpl.php.
    'google_books_aggregate' => [
      'template' => 'google_books',
      'arguments' => ['parameter' => NULL],
    ],
      // Theme function to print biblio fields.
    'google_books_biblio' => [
      'variables' => [],
    ],
  ];
}
 * 
 */

/**
 * Returns HTML for google_books.
 *
 * @param array $selected_bibs
 *   An associative array containing:
 *   - selected_bibs: Array of biblio: $index => $value defined in
 *     google_books_api_bib_field_array()
 *
 * @ingroup themeable
 */
/**
function theme_google_books_biblio($selected_bibs) {
  $html_string = '<ul class="google_books_datafields">';
  foreach ($selected_bibs as $bib_field_name => $bib_field_data) {
    $html_string .= '<li><span class="google_books_field_name">'
      . $bib_field_name
      . '</span>: <span class="google_books_field_data">'
      . $bib_field_data
      . "</span></li>";
  }
  $html_string .= "</ul>";
  return $html_string;
}
 * 
 */
