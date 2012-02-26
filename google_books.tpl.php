<?php
/**
 * @file
 * Google Books Template Drupal 7.0
 *
 * @author Darrell Ulm.
 *
 * This is the default template file for the google_book filter.
 *
 */
?>

<!-- Main CSS of a Google Books entry, multiple per page. -->
<div class="google_books">

  <!-- Print the title of the book. -->
  <div class="google_books_title">
    <?php print $title_anchor; ?>
  </div>

  <!-- Build image theme function call for book cover. -->
  <div class="google_books_image">
    <?php
      if ($book_image != ""):
        print $book_image;
      endif;
    ?>
  </div>

  <!-- Display links and leave empty divs for theming. -->
  <div class="google_books_links">
    <div class="google_books_worldcat">
      <?php print $worldcat; ?>
    </div>
    <div class="google_books_librarything">
      <?php print $librarything; ?>
    </div>
    <div class="google_books_openlibrary">
      <?php print $openlibrary; ?>
    </div>
  </div>

  <!-- Show the Google Books viewer if needed. -->
  <!-- Embed direct because of the filter JavaScript issue in Drupal -->
  <?php
    if (!empty($google_books_js_string)):
      print "<script type='text/javascript'>" . $google_books_js_string . "</script>";
      print '<div class="google_books_reader" id="viewerCanvas' . $isbn . '" style = "width:' . $reader_width . 'px; height:' . $reader_height . 'px"></div>';
    endif;
  ?>

  <div class="google_books_datalist">
    <!-- List the selected and available Google Books data fields. -->
    <?php
    print $bib_data;
    ?>
  </div>
  
  <!-- Prevent overlap of next element. -->
  <div style="clear:both;"></div>
  
</div>
