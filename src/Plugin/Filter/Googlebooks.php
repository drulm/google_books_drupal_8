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
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Cookie\CookieJar;
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
    $form['api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Google Books API Key'),
      '#size' => 60,
      '#maxlength' => 80,
      '#description' => t('Register your key at: https://console.developers.google.com/apis'),
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
  /* 
   * Previous D7 filter process. 
  function google_books_filter_process($text, $filter, $format, $langcode, $cache, $cache_id) {
  preg_match_all('/\[google_books:(.*)\]/', $text, $match);
  $tag = $match[0];
  $book = array();
   */
/*
    foreach ($this->settings['tags'] as $tag) {
      $tag_elements = $document->getElementsByTagName($tag);
      foreach ($tag_elements as $tag_element) {
        $tag_element->setAttribute('test_attribute', 'test attribute value');
      }
    }
    return new FilterProcessResult(Html::serialize($document));
 */
  public function process($text, $langcode) {
    $document = Html::load($text);
    
    //dpm($this->settings);
    
    
    //return new FilterProcessResult(Html::serialize($document));
    
    preg_match_all('/\[google_books:(.*)\]/', $text, $match);
    $tag = $match[0];
    $book = array();
    
    
    /*
     * Old params:
    function google_books_retrieve_bookdata(
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
     */
    
    //dpm($document);
    
    //dpm($this->settings);
    
    foreach ($match[1] as $i => $val) {
      $book[$i] = google_books_retrieve_bookdata(
        $match[1][$i],
        $this->settings['worldcat'],
        $this->settings['librarything'],
        $this->settings['openlibrary'],
        $this->settings['image'],
        $this->settings['reader'],
        //$this->settings['bib_fields'],
        $this->settings['image_height'],
        $this->settings['image_width'],
        $this->settings['reader_height'],
        $this->settings['reader_width'],
        $this->settings['api_key']
      );

	  dpm($book[$i]);

	  $output = [
			'#theme' => 'googlebooks_template',
			'#test_var' => "Hello World",
			'#title_anchor' => $book[$i]['title'],
			'#worldcat' => $book[$i]['worldcat_link'],
			'#librarything' => $book[$i]['librarything_link'],
			'#openlibrary' => $book[$i]['openlibrary_link'],
			'#image' => $book[$i]['title'],
			'#reader' => $book[$i]['title'],
			'#bib_fields' => 'bib_fields',
			'#image_height' => 'image_height',
			'#image_width' => 'image_width',
			'#reader_height' => 'reader_height',
			'#reader_width' => 'reader_width',
			'#api_key' => 'api_key',
		];
	  $markup = render($output);
	  
	  $text = str_replace($i, $markup, $text);
    }
    //$text = str_replace($tag, $book, $text);
    //dpm($text);


    return new FilterProcessResult($text);
    //return new FilterProcessResult(Html::serialize($document));
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
  function google_books_filter_process($text) {
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
  function google_books_retrieve_bookdata(
    $id,
    $worldcat_link,
    $librarything_link,
    $openlibrary_link,
    $image_option,
    $reader_option,
    //$bib_field_select,
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
    // array_map('filter_xss', $params);
    $search_string = $params[0];

    // Get the Google Books data.
    // Ignore if google_books_api_get_google_books_data returns NULL.
    $bib = google_books_api_get_google_books_data($search_string, 0, $api_key);
    if ($bib != NULL) {
      // Clean the data from Google.
      // array_map('filter_xss', $bib);
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

      // Send the variables to the theme.
      //$output = theme('google_books_aggregate', $vars);

      //$output = print_r($vars, TRUE);  
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
   * Takes book field data and makes it a link if it address present.
   *
   * @param string $address
   *   The biblio field string, which might be an address.
   *
   * @return string
   *   Returns an HTML <a></a> link if there is a valid address in $address.
   */
  function google_books_make_html_link($address) {
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
 function google_books_api_get_google_books_data($id, $version_num, $api_key = NULL) {

   // Clean search string of spaces, turn into '+'.
   $id = google_books_api_clean_search_id($id);

   // Get all the arrays from the query.
   $bookkeys = google_books_api_cached_request($id, $api_key);
   //dpm($bookkeys);

   // Decode into array to be able to scan.
   $json_array_google_books_data = json_decode($bookkeys, TRUE);
   //dpm($json_array_google_books_data);

   $versions = $json_array_google_books_data['totalItems'];
   //dpm($versions);

   // Check the number of versions returned by Google Books.
   if ($versions > 0 && $version_num < $versions) {

     // Grab the first result.
     $bookkeyresult = $json_array_google_books_data['items'][$version_num];

     // Extract the results into one big string with delimiters.
     $book_str = google_books_api_demark_and_flatten($bookkeyresult);
     //dpm($book_str);

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
     //dpm($bib);
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
     
     //$url_data = guzzle_http_request($url_bookkeys);
    try {
      $url_data = (string) \Drupal::httpClient()
        ->get($url_bookkeys)
        ->getBody();
    }  
    catch (RequestException $exception) {
      drupal_set_message(t('Googlebooks: Could not retrieve data from google.com. @err', array('%error' => $exception->getMessage())), 'error');
      return FALSE;
    }

    //dpm($url_data);
    
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

     /*if (isset($url_data->error) || !isset($url_data->data)) {
       drupal_set_message(t('Googlebooks: Could not retrieve data from google.com. @err', array('@err' => $url_data->error)), $type = 'error');
       return NULL;
     }*/
     //$bookkeys = $url_data->data;
    $bookkeys = $url_data;

     // Set the cache if return from request is not NULL.
     if ($bookkeys != NULL) {
       //cache_set($bookkeys_hash, $bookkeys, 'cache_google_books_api', REQUEST_TIME + GOOGLE_BOOKS_CACHE_PERIOD);
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
 function google_books_api_get_googlebooks_version_count($id, $api_key = NULL) {

   // Cleanup the search ID.
   $id = google_books_api_clean_search_id($id);

   // Get all the arrays from the query.
   $bookkeys = google_books_api_cached_request($id, $api_key);
   //dpm($bookkeys);
   if ($bookkeys != NULL) {
     // Decode into array to be able to scan.
     $json_array_google_books_data = json_decode($bookkeys, TRUE);
     //dpm($json_array_google_books_data);
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
   function google_books_api_clean_search_id($id) {

     // Clean search string of spaces, turn into '+'.
     $id = trim($id);
     $dirt_id = array(" ");
     return str_replace($dirt_id, "+", $id);
   }

/**
 * Implements hook_theme().
 */
function google_books_theme() {
  // Return the array describing the template name and vars.
  return array(
    // The main theme template google_books.tpl.php
    'google_books_aggregate' => array(
      'template' => 'google_books',
      'arguments' => array('parameter' => NULL),
    ),
    // Theme function to print biblio fields.
    'google_books_biblio' => array(
      'variables' => array(),
    ),
  );
}

/**
 * Implements hook_preprocess_HOOK().
 *
 * @param array $vars
 *   An associative array containing:
 *   - title_anchor: Theme variable with title anchor.
 *   - title_link: Should title be on or off.
 *   - info_link: The cleaned link to the google information page.
 *   - title: The text title.
 *   - isbn: The ISBN if it exists for this Google Book.
 *   - worldcat: Output link to WorldCat data on this book.
 *   - worldcat_link: True or false to display WorldCat link.
 *   - librarything: Output link to LibraryThing data on this book.
 *   - librarything_link: True or false to display LibraryThing link.
 *   - openlibrary: Output link to OpenLibrary data on this book.
 *   - openlibrary_link: True or false to display OpenLibrary link.
 *   - book_image: Theme output <img tag to theme output.
 *   - image_option: Global option for image on or off.
 *   - image_on: Per book option for image on or off.
 *   - thumbnail: The cleaned image thumbnail URL.
 *   - page_curl: Per book option to turn on/off page curl for image.
 *   - reader_height: Option for reader height, per book.
 *   - reader_width: Option for reader width, per book.
 *   - image_height: Option for image height, per book.
 *   - image_width: Option for image width, per book.
 *   - google_books_js_string: Per book Google Books javascript.
 *   - reader_option: Global option for Google Books reader on/off.
 *   - reader_on: Per book option for Google Books reader on/off.
 *   - embeddable: From Google, check if technology and licensing allows embed.
 *   - book_data_array: From theme function, build the list of biblio data.
 *   - selected_bibs: Array of biblio elements selected to include.
 *   - bib_data: Final biblio data to display generated by theme function.
 *
 * @see theme_google_books_biblio()
 * @see google_books_make_html_link()
 */
function google_books_preprocess_google_books_aggregate(&$vars) {
  // Build the main title with a link.
  $vars["title_anchor"] = "";
  if ($vars["title_link"] !== 0 && isset($vars["info_link"]) && isset($vars["title"])) {
    $vars["title_anchor"] = l($vars["title"], $vars["info_link"], array('attributes' => array('rel' => 'nofollow', 'target' => '_blank')));
  }

  // Show the book links if any are found. Not checked for ISBN validity.
  $isbn = $vars["isbn"];
  if (!empty($isbn) || TRUE) {
    $vars["worldcat"] = "";
    if ($vars["worldcat_link"]) {
      $vars["worldcat"] = l(t('WorldCat'), check_url("http://worldcat.org/isbn/" . $isbn), array('attributes' => array('rel' => 'nofollow', 'target' => '_blank')));
    }
    $vars["librarything"] = "";
    if ($vars["librarything_link"]) {
      $vars["librarything"] = l(t('LibraryThing'), check_url("http://librarything.com/isbn/" . $isbn), array('attributes' => array('rel' => 'nofollow', 'target' => '_blank')));
    }
    $vars["openlibrary"] = "";
    if ($vars["openlibrary_link"]) {
      $vars["openlibrary"] = l(t('OpenLibrary'), check_url("http://openlibrary.org/isbn/" . $isbn), array('attributes' => array('rel' => 'nofollow', 'target' => '_blank')));
    }
  }

  // Check if we need to process the image thumbnail, and setup.
  $vars["book_image"] = "";
  $image_option = $vars["image_option"];
  $image_on = $vars["image_on"];
  $thumbnail = $vars["thumbnail"];
  if (isset($thumbnail) && ($image_option == 1 || $image_on == 1) && $image_on !== 0) {
    $img_link = $thumbnail;
    if ($vars["page_curl"] == 1) {
      $img_link = str_replace("&edge=nocurl", "", $img_link);
      $img_link .= "&edge=curl";
    }
    if ($vars["page_curl"] == 0) {
      $img_link = str_replace("&edge=curl", "", $img_link);
    }
    // Setup the image array and call theme('image'.
    $vars["book_image"] = theme('image', array(
      'path' => $img_link,
      'alt' => $vars["title"],
      'title' => $vars["title"],
      'width' => $vars["image_width"],
      'height' => $vars["image_height"],
    ));
  }

  // Setup the book reader.
  if (trim($vars["reader_height"]) == '') {
    $vars["reader_height"] = GOOGLE_BOOKS_DEFAULT_READER_HEIGHT;
  }
  if (trim($vars["reader_width"]) == '') {
    $vars["reader_width"] = GOOGLE_BOOKS_DEFAULT_READER_WIDTH;
  }
  $vars["google_books_js_string"] = "";
  $reader_option = $vars["reader_option"];
  $reader_on = $vars["reader_on"];
  if (isset($vars["embeddable"]) && ($reader_option == 1 || $reader_on == 1) && $reader_on !== 0) {
    // Build the string for the google_books viewer.
    $vars["google_books_js_string"] = '
      google.load("books", "0");
      function initialize' . $isbn . '() {
        var viewer' . $isbn . ' = new google.books.DefaultViewer(document.getElementById("viewerCanvas' . $isbn . '"));
        viewer' . $isbn . '.load("ISBN:' . $isbn . '");
      }
      google.setOnLoadCallback(initialize' . $isbn . ');
      ';
  }

  // Setup the data array of biblio items.
  $vars["book_data_array"] = theme('google_books_biblio', $vars["selected_bibs"]);

  // Setup the list of biblio data.
  $processed_bibs = array();
  foreach ($vars["selected_bibs"] as $bib_index => $bib_value) {
    $bib_field_name = drupal_ucfirst(preg_replace('/[A-Z]/', ' $0', str_replace('_', ' ', $bib_index)));
    $bib_field_data = google_books_make_html_link($bib_value);
    $processed_bibs[$bib_field_name] = $bib_field_data;
  }
  $vars["bib_data"] = theme('google_books_biblio', $processed_bibs);
}

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
