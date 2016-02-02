<?php
/**
 * @file
 * The primary PHP file for this theme.
 */

/**
 * Implements hook_css_alter().
 */
function techoregon_js_alter(&$js) {
  // Stop Bootstrap JS file from being loaded by Bootstrap theme so we can load Bootstrap JS in our subtheme instead.
  $bootstrap_theme_path = drupal_get_path('theme', 'bootstrap');
  unset($js[$bootstrap_theme_path . '/js/bootstrap.js']);
}

/**
 * Implements hook_preprocess_page().
 */
function techoregon_preprocess_page(&$variables) {

  // If front page, change logo
  if ($variables['is_front']) {
    $variables['logo'] = path_to_theme() . '/images/logos/Logo_white_text.png';
  }

  // Remove .navbar-default class from header navbar to avoid unwanted styling
  if (($key = array_search('navbar-default', $variables['navbar_classes_array'])) !== false) {
    unset($variables['navbar_classes_array'][$key]);
  }

  // If there is a value for hero text or hero image field, then we can assume
  // there is also a hero text/image view and default page title should not be rendered
  if (!empty($variables['node']->field_hero_text) || !empty($variables['node']->field_hero_image)) {
    $variables['title'] = '';
  }

  // Add classes to body based on site section or content type
  if (!empty($variables['node']->field_site_section)) {
    switch ($variables['node']->field_site_section['und'][0]['tid']) {
      // Get Involved
      case "8":
        ctools_class_add('section-involved');
        break;
      // What We Do
      case "7":
        ctools_class_add('section-do');
        break;
      // Who We Are
      case "6":
        ctools_class_add('section-are');
        break;
      // Events
      case "34":
        ctools_class_add('section-events');
        break;
      // Blog
      case "31":
        ctools_class_add('section-blog');
        break;
    }
  }
  if (!empty($variables['node']->type)) {
    switch ($variables['node']->type) {
      case "blog":
        ctools_class_add('section-blog');
        break;
      case "event":
        ctools_class_add('section-events');
        break;
    }
  }

  // If page is landing page content type, add class to body
  if (!empty($variables['node']->type) && $variables['node']->type == 'landing_page') {
    ctools_class_add('landing-page');
  }
}

/**
 * Implements theme_menu_tree()
 *
 * Remove Bootstrap .nav class from Footer menu.
 */
function techoregon_menu_tree(&$variables) {
  if ($variables['theme_hook_original'] == 'menu_tree__menu_footer_menu') {
    return '<ul class="menu">' . $variables['tree'] . '</ul>';
  }
}

/**
 * Returns HTML for a menu link and submenu.
 * Copied from Bootstrap theme @see menu-link.func.php in order to:
 * Alter output for Footer menu to add forward slashes.
 * Prevent Main Menu from rendering dropdowns.
 *
 * @param array $variables
 *   An associative array containing:
 *   - element: Structured array data for a menu link.
 *
 * @return string
 *   The constructed HTML.
 *
 * @see theme_menu_link()
 *
 * @ingroup theme_functions
 */
function techoregon_menu_link(array $variables) {
  $element = $variables['element'];
  $sub_menu = '';

  if ($element['#below']) {
    // Prevent dropdown functions from being added to management menu so it
    // does not affect the navbar module.
    if (($element['#original_link']['menu_name'] == 'management') && (module_exists('navbar'))) {
      $sub_menu = drupal_render($element['#below']);
    }
    // Prevent dropdown from being added to main menu. Add to other menus.
    if ($element['#original_link']['menu_name'] !== 'main-menu') {
      if ((!empty($element['#original_link']['depth'])) && ($element['#original_link']['depth'] == 1)) {
        // Add our own wrapper.
        unset($element['#below']['#theme_wrappers']);
        $sub_menu = '<ul class="dropdown-menu">' . drupal_render($element['#below']) . '</ul>';
        // Generate as standard dropdown.
        $element['#title'] .= ' <span class="caret"></span>';
        $element['#attributes']['class'][] = 'dropdown';
        $element['#localized_options']['html'] = TRUE;

        // Set dropdown trigger element to # to prevent inadvertant page loading
        // when a submenu link is clicked.
        $element['#localized_options']['attributes']['data-target'] = '#';
        $element['#localized_options']['attributes']['class'][] = 'dropdown-toggle';
        $element['#localized_options']['attributes']['data-toggle'] = 'dropdown';
      }
    }
  }
  // On primary navigation menu, class 'active' is not set on active menu item.
  // @see https://drupal.org/node/1896674
  if (($element['#href'] == $_GET['q'] || ($element['#href'] == '<front>' && drupal_is_front_page())) && (empty($element['#localized_options']['language']))) {
    $element['#attributes']['class'][] = 'active';
  }
  $output = l($element['#title'], $element['#href'], $element['#localized_options']);

  // If Footer menu, add forward slashes between menu items
  if ($variables['theme_hook_original'] == 'menu_link__menu_footer_menu' && $variables['element']['#attributes']['class'][0] !== 'last') {
    return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "/</li>\n";
  }
  else {
    return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
  }
}

/**
 * Specifically call bootstrap menu theme functions for menu blocks created by menu_block module.
 * There is apparently a conflict between the Bootstrap theme and Menu Block module.
 * Without these function overrides the menu is not rendered.
 * Used for secondary menu which displays second level of main-menu.
 */
function techoregon_menu_link__menu_block(&$variables) {
  return bootstrap_menu_link($variables);
}

function techoregon_menu_tree__menu_block(&$variables) {
  return '<ul class="menu nav navbar-nav navbar-default">' . $variables['tree'] . '</ul>';
}

/**
 * Implements hook_preprocess_panels_pane()
 */
function techoregon_preprocess_panels_pane(&$variables) {
  // get the subtype
  $subtype = $variables['pane']->subtype;

  // Add the subtype to the panel theme suggestions list
  $variables['theme_hook_suggestions'][] = 'panels_pane__'.$subtype;

  // Add Bootstrap .row class inside .pane-content. Useful if using columns in pane/view.
  if ($subtype == 'get_involved') {
    $variables['theme_hook_suggestions'][] = 'panels_pane__row_content';
  }
}

/**
 * Implements theme_field()
 *
 * Override default Drupal theme_field() function in order to remove colon from field labels.
 */
function techoregon_field($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<label class="field-label"' . $variables['title_attributes'] . '>' . $variables['label'] . '&nbsp;</label>';
  }

  // Render the items.
  $output .= '<div class="field-items"' . $variables['content_attributes'] . '>';
  foreach ($variables['items'] as $delta => $item) {
    $classes = 'field-item ' . ($delta % 2 ? 'odd' : 'even');
    $output .= '<div class="' . $classes . '"' . $variables['item_attributes'][$delta] . '>' . drupal_render($item) . '</div>';
  }
  $output .= '</div>';

  // Render the top-level DIV.
  $output = '<div class="' . $variables['classes'] . '"' . $variables['attributes'] . '>' . $output . '</div>';

  return $output;
}

/**
 * Implements theme_field()
 *
 * Theme field_tags as a comma separated list.
 */
function techoregon_field__field_tags($variables) {
  $output = '';

  // Render the items as a comma separated inline list
  $output .= '<ul class="field-tags field-items"' . $variables['content_attributes'] . '>';
  for ($i = 0; $i < count($variables['items']); $i++) {
    $output .= '<li class="tag-item">' . drupal_render($variables['items'][$i]);
    $output .= ($i == count($variables['items']) - 1) ? '</li>' : ', </li>';
  }
  $output .= '</ul>';

  return $output;
}

/**
 * Implements hook_view_pre_render()
 */
function techoregon_views_pre_render(&$view) {
  // For Event Details view, other sponsors display, swap title with value of field_sponsors_group_ref.
  if ($view->name == 'event_details' && $view->current_display == 'pane_other_sponsors') {
    if (!empty($view->result[0]->field_field_sponsors_group_ref[0]['rendered']['#markup'])) {
      $view->build_info['title'] = $view->result[0]->field_field_sponsors_group_ref[0]['rendered']['#markup'];
    }
  }
}