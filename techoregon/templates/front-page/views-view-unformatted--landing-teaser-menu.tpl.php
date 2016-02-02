<?php

/**
 * @file
 * Landing Page Teaser Menu view that uses .teaser-grid class. Wrap every 2rd result in Bootstrap .row class.
 *
 * @ingroup views_templates
 */
?>

<?php
  $last=count($rows) - 1;
  foreach ($rows as $id => $row): ?>
  <?php if ($id % 2 == 0): // if this an even row id, open the <div> ?>
    <div class="row">
  <?php endif; ?>
  <div<?php if ($classes_array[$id]) { print ' class="' . $classes_array[$id] .'"';  } ?>>
    <?php print $row; ?>
  </div>
  <?php if ($id==$last || $id % 2 == 1): // if this the last or an odd row id, close the <div> ?>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

