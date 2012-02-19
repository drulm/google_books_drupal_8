<?php
/**
 * @file
 * Googlebook Template Drupal 7.0
 *
 * @author Darrell Ulm.
 *
 * This is the default template file for the googlebook filter.
 *
 */
?>

<!-- Main CSS of a Google Book entry, multiple per page. -->
<div class="googlebook">

  <!-- Print the title of the book. -->
  <div class="googlebook_title">
      <?php print $title_anchor; ?>
  </div>

  <!-- Build image theme function call for book cover. -->
  <div class="googlebook_image">
    <?php
      if ($img_link != ""):
      print theme('image', array(
        'path' => $img_link,
        'alt' => $title,
        'title' => $title,
        'width' => $image_width,
        'height' => $image_height,
        'attributes' => array(
          'rel' => 'nofollow',
          'target' => '_blank',
        ),
      ));
      endif;
    ?>
  </div>

  <!-- Display links and leave empty divs for theming. -->
  <div class="googlebook_links">
    <div class="googlebook_worldcat">
      <?php print $worldcat; ?>
    </div>
    <div class="googlebook_librarything">
      <?php print $librarything; ?>
    </div>
    <div class="googlebook_openlibrary">
      <?php print $openlibrary; ?>
    </div>
  </div>

  <!-- Show the Google book viewer if needed. -->
  <!-- Embed direct because of the filter JavaScript issue in Drupal -->
  <?php
  if (!empty($googlebook_js_string)):
    print "<script type='text/javascript'>" . $googlebook_js_string . "</script>";
    print '<div class="googleviewer" id="viewerCanvas' . $isbn . '" style = "width:' . $reader_width . 'px; height:' . $reader_height . 'px"></div><p></p><br />';
  endif;
  ?>

  <!-- List the selected and available Google Book data fields. -->
  <?php
  print theme('googlebookbiblio', $selected_bibs);
  ?>
</div>
