<?php

/**
 * @file
 * What We Do view. Wrap every 3rd result in Bootstrap .row class.
 *
 * @ingroup views_templates
 */
?>

<?php
  $last=count($rows) - 1;
  foreach ($rows as $id => $row): ?>
  <?php if ($id % 3 == 0): // if this an even row id, open the <div> ?>
    <div class="row">
  <?php endif; ?>
  <div<?php if ($classes_array[$id]) { print ' class="' . $classes_array[$id] .'"';  } ?>>
    <?php print $row; ?>
  </div>
  <?php if ($id==$last || $id % 3 == 2): // if this the last or an odd row id, close the <div> ?>
    </div>
  <?php endif; ?>
<?php endforeach; ?>

