<?php
/*
 * @file googlebook.tpl.php
 *
 * This is the default template file for the googlebook filter.
 *
 */

print '<div class="googlebook">';

// Get the data array
$bib = googlebookapi_bib_field_array();

// Build the main title with a link.
if (($title_link == 1 || $title_link === "") && isset($infolink) && isset($title)) {
  print '<div class="googlebooktitle">' . t('Title') . ': <a href="' . htmlentities($infoLink) . '" rel="nofollow" target="_blank"><i>' . $title . '</i></a></div>';
}

// Show the book links if any are found. None are checked for ISBN validity.
if (!empty($isbn) || TRUE) {
  if ($worldcat_link) {
    $worldcat = '<a href="http://worldcat.org/isbn/' . $isbn . '" rel="nofollow" target="_blank">WorldCat</a><br />';
  }
  if ($librarything_link) {
    $librarything = '<a href="http://librarything.com/isbn/' . $isbn . '" rel="nofollow" target="_blank">LibraryThing</a><br />';
  }
  if ($openlibrary_link) {
    $openlibrary = '<a href="http://openlibrary.org/isbn/' . $isbn . '" rel="nofollow" target="_blank">Open Library</a><br />';
  }
  if (drupal_strlen($worldcat . $librarything . $openlibrary) > 0) {
    print '<div class="googlebooklinks">' . t("Link to") . ":<br />" . $worldcat . $librarything . $openlibrary . '</div>';
  }
}

// Build a coverimage if user selected.
if (isset($thumbnail) && ($image_option == 1 || $image_on == 1) && $image_on !== 0) {
  $img_link = $thumbnail;
  if ($page_curl == 1) {
    $img_link = str_replace("&edge=nocurl", "", $img_link);
    $img_link .= "&edge=curl";
  }
  if ($page_curl == 0) {
    $img_link = str_replace("&edge=curl", "", $img_link);
  }
  $html_coverimage = "<img class='googlebookimage' src='" . htmlentities($img_link) . "' alt='" . $title . "' height='" . $image_height . "' width='" . $image_width . "' />";
  print "<a href='" . htmlentities($infoLink) . "' rel='nofollow' target='_blank'>" . $html_coverimage . "</a>";
}

// Show the google book viewer if selected and isbn exists.
if (!empty($googlebook_js_string)){
  print "<script type='text/javascript' src='".GOOGLE_BOOK_EXTERN_JS."'></script>";
  print "<script type='text/javascript'>".$googlebook_js_string."</script>";
  $google_viewer = '<div class="googleviewer" id="viewerCanvas' . $isbn . '" style = "width:' . $reader_width . 'px; height:' . $reader_height . 'px"></div><p></p><br />';
  print $google_viewer;
}

// Output the biblio fields in the array.
print "<ul>";
foreach ($bib as $bib_index) {
  if (isset (${$bib_index})) {
    print "<li>" . drupal_ucfirst(preg_replace('/[A-Z]/', ' $0', str_replace('_', ' ', $bib_index))) . ": " . googlebook_make_html_link(${$bib_index}) . "</li>";
  }
}
print "</ul>";
print "</div>";

//drupal_add_js(GOOGLE_BOOK_EXTERN_JS,'theme','external','header');
?>
