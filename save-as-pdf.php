<?php

/**
 * Plugin Name: Save as PDF
 * Plugin URI: https://restpack.io/html2pdf
 * Description: Allows visitors to save current page as PDF file
 * Version: 1.0.0
 * Text Domain: save-as-pdf
 * Author: Restpack Inc
 * Author URI: https://restpack.io/html2pdf
 * Tested up to: 5.2.1
 * License: GNUGPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

function gen_key($name)
{
  return "restpack_key_" . $name;
}

register_activation_hook(__FILE__, 'register_plugin');
add_action('admin_init', 'init');

add_filter('the_content', 'render_button');
add_action('admin_menu', 'admin_menus');

function register_plugin()
{
  $default = array(
    "pdf_page" => "Full",
    "pdf_orientation" => "portrait",
    "delay" => 500,
    "button_text" => "Save as PDF",
    "button_icon" => "https://cl.ly/021e985fb708/icon-1.svg",
    "button_width" => "25px",
    "button_position" => "Above",
    "button_align" => "Left"
  );

  update_option(gen_key("options"), $default);
}

function admin_menus()
{
  add_options_page('Restpack PDF', 'Restpack PDF', 'administrator', __FILE__, 'render_settings_page');
}

function init()
{
  register_setting(gen_key("options"), gen_key("options"), 'options_validate');

  $first_section = gen_key("first");

  add_settings_section($first_section, 'Render', 'styling', __FILE__);

  function styling()
  {
    echo "
      <style>
        textarea {width: 320px; height: 70px;}
        input {width: 250px;}
        label  { margin-right: 15px; }
      </style>";
  }

  add_settings_field(gen_key("pdf_page"), 'PDF page format', 'render_settings_field', __FILE__, $first_section, array(
    "key" => "pdf_page"
  ));

  add_settings_field(
    gen_key("pdf_orientation"),
    'PDF page orientation',
    'render_settings_field',
    __FILE__,
    $first_section,
    array("key" => "pdf_orientation")
  );

  add_settings_field(gen_key("delay"), 'Delay', 'render_settings_field', __FILE__, $first_section, array("key" => "delay"));

  $second_section = gen_key("second");

  add_settings_section($second_section, 'Display', null, __FILE__);
  add_settings_field(gen_key("display_pages"), 'Display button on', 'render_settings_field', __FILE__, $second_section, array(
    "key" => "display_pages"
  ));
  add_settings_field(gen_key("button_text"), 'Button Text', 'render_settings_field', __FILE__, $second_section, array(
    "key" => "button_text"
  ));
  add_settings_field(gen_key("button_icon"), 'Button Icon', 'render_settings_field', __FILE__, $second_section, array(
    "key" => "button_icon"
  ));
  add_settings_field(gen_key("button_width"), 'Button Witdh', 'render_settings_field', __FILE__, $second_section, array(
    "key" => "button_width"
  ));
  add_settings_field(gen_key("button_position"), 'Position', 'render_settings_field', __FILE__, $second_section, array(
    "key" => "button_position"
  ));
  add_settings_field(gen_key("button_align"), 'Align', 'render_settings_field', __FILE__, $second_section, array(
    "key" => "button_align"
  ));

  $third_section = gen_key("third");
  add_settings_section($third_section, 'Premium Features', 'premium_features_text', __FILE__);

  function premium_features_text()
  {
    echo "
      <p>
        In order to use these features, you need to subscribe and get access_token from <a href=\"https://restpack.io/html2pdf?utm_source=wp\" target=\"_blank\">Restpack HTML to PDF API</a>. <br />
        <a href=\"https://restpack.io/html2pdf?utm_source=wp\" target=\"_blank\">Start 7-Day Trial</a>
      </p>";
  }

  add_settings_field(gen_key("api_key"), 'Access Token', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "api_key"
  ));

  add_settings_field(gen_key("pdf_margins"), 'PDF margins', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "pdf_margins",
    "placeholder" => "10px 20px 10px 20px"
  ));

  add_settings_field(gen_key("pdf_header"), 'PDF header', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "pdf_header",
    "textarea" => true,
    "placeholder" => "<div>Your company name and logo</div>"
  ));

  add_settings_field(gen_key("pdf_footer"), 'PDF footer', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "pdf_footer",
    "textarea" => true,
    "placeholder" => "<div> <span class='pageNumber'></span> of <span class='totalPages'></span>  </div>"
  ));

  add_settings_field(gen_key("emulate_media"), 'Emulate media', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "emulate_media"
  ));

  add_settings_field(gen_key("js"), 'JS inject', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "js",
    "placeholder" => "document.body.style.backgrond='black';",
    "textarea" => true
  ));

  add_settings_field(gen_key("css"), 'CSS inject', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "css",
    "placeholder" => "h1 {font-size : 25px}",
    "textarea" => true
  ));

  add_settings_field(
    gen_key("block_cookie_warnings"),
    'Block EU cookie warning',
    'render_settings_field',
    __FILE__,
    $third_section,
    array(
      "key" => "block_cookie_warnings"
    )
  );

  add_settings_field(gen_key("block_ads"), 'Block Ads', 'render_settings_field', __FILE__, $third_section, array(
    "key" => "block_ads"
  ));
}

function render_option_tag($opts, $value)
{
  $optsHtml = "";

  foreach ($opts as $label) {
    $optsHtml .= "<option " . ($label == $value ? "selected" : "") . ">" . $label . "</option>\n";
  }

  return $optsHtml;
}

function render_settings_field($arg)
{
  $options = get_option(gen_key("options"));
  $html = "";

  $default_attrs = "id='" . $arg["key"] . "' name='" . gen_key("options") . "[" . $arg["key"] . "]'";
  $value = $options[$arg["key"]];

  switch ($arg["key"]) {
    case 'pdf_page':
      $opts = array("A0", "A1", "A2", "A3", "A4", "A5", "A6", "Legal", "Letter", "Tabloid", "Ledger", "Full");
      $html = "<select " . $default_attrs . ">" . render_option_tag($opts, $value) . "</select>";
      break;

    case 'pdf_orientation':
      $opts = array("portrait", "landscape");
      $html = "<select " . $default_attrs . ">" . render_option_tag($opts, $value) . "</select>";
      break;

    case 'emulate_media':
      $opts = array("screen", "print");
      $html = "<select " . $default_attrs . ">" . render_option_tag($opts, $value) . "</select>";
      break;

    case 'block_ads':
    case 'block_cookie_warnings':
      $html =
        "<input id='" .
        $arg["key"] .
        "' name='" .
        gen_key("options") .
        "[" .
        $arg["key"] .
        "]' type='checkbox' value='true' " .
        ($options[$arg["key"]] ? " checked=checked" : "") .
        " />";

      break;

    case 'display_pages':
      $html =
        "<label>
					<input id='" .
        $arg["key"] .
        "' name='" .
        gen_key("options") .
        "[" .
        $arg["key"] .
        "][posts]' type='checkbox' " .
        ($options[$arg["key"]]['posts'] ? " checked=checked" : "") .
        " />
					Posts
				</label>

				<label>
				<input id='" .
        $arg["key"] .
        "' name='" .
        gen_key("options") .
        "[" .
        $arg["key"] .
        "][pages]' type='checkbox' " .
        ($options[$arg["key"]]['pages'] ? " checked=checked" : "") .
        " />
					Pages
				</label>";
      break;

    case 'button_icon':
      $html = "";
      $icons = array(
        "https://cl.ly/021e985fb708/icon-1.svg",
        "https://cl.ly/a55b4d7c1ba4/icon-2.svg",
        "https://cl.ly/71f4521d32b5/icon-3.svg",
        "https://cl.ly/c431e33aa2d7/icon-5.svg",
        "https://cl.ly/3ed7e86259e5/icon-6.svg",
        "https://cl.ly/57103d650198/icon-7.svg",
        "https://cl.ly/321d63a90af3/icon-8.svg",
        "https://cl.ly/c029ba529e8e/icon-9.svg",
        "https://cl.ly/994eee667c02/icon-10.svg",
        "https://cl.ly/8eb5d9434ecf/icon-11.svg",
        "https://cl.ly/b61b72da7ba5/icon-12.svg",
        "https://cl.ly/745576647389/icon-13.svg"
      );
      foreach ($icons as $icon) {
        $html .=
          "<label>
          <input value='" .
          $icon .
          "' id='" .
          $arg["key"] .
          "' name='" .
          gen_key("options") .
          "[" .
          $arg["key"] .
          "]' type='radio' " .
          ($value == $icon ? " checked=checked" : "") .
          " />
        <img src='" .
          $icon .
          "' style='width: 18px;' alt='Save as pdf' title='Save as pdf'/>
      </label>";
      }
      break;

    case "button_position":
      $opts = array("Above Content", "Below Content");
      $html = "<select " . $default_attrs . ">" . render_option_tag($opts, $value) . "</select>";
      break;

    case "button_align":
      $opts = array("Left", "Center", "Right");
      $html = "<select " . $default_attrs . ">" . render_option_tag($opts, $value) . "</select>";
      break;

    default:
      if ($arg['textarea']) {
        $html = "<textarea" . ($arg["readonly"] ? 'readonly' : '') . " " . $default_attrs . " >" . $value . "</textarea>";
      } else {
        $html = "<input " . ($arg["readonly"] ? 'readonly' : '') . " " . $default_attrs . " value='" . $value . "' />";
      }
      break;
  }

  if ($arg['placeholder']) {
    $html = $html . "<span style='display: block'>Example: " . htmlspecialchars($arg['placeholder']) . "</span> ";
  }

  echo $html;
}

function render_settings_page()
{
  ?>
	<div class="wrap">
		<h2>Restpack PDF Settings</h2>
		<form action="options.php" method="post">

		<?php settings_fields(gen_key('options')); ?>
		<?php do_settings_sections(__FILE__); ?>

		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
	</div>
<?php
}

function options_validate($input)
{
  $input['text_string'] = $input['text_string'];
  return $input;
}

function render_button($content = "")
{
  $options = get_option(gen_key("options"));
  $button = "<div style='text-align:" . $options['button_align'] . " '>" . shortcode(array()) . "</div>";

  if (($options['display_pages']['posts'] && is_single()) || ($options['display_pages']['pages'] && is_page())) {
    if ($options['button_position'] === 'Above Content') {
      $content = $button . $content;
    } else {
      $content = $content . $button;
    }
  }

  return $content;
}

function shortcode($attrs)
{
  $options = get_option(gen_key("options"));
  $props = shortcode_atts(
    array(
      'pdf_page' => $options['pdf_page'],
      'pdf_orientation' => $options['pdf_orientation'],
      'delay' => $options['delay'],
      'private' => true
    ),
    $attrs
  );

  $have_key = $options['api_key'];

  $button_text = $options['button_text'];
  if ($attrs && $attrs['button_text']) {
    $button_text = $attrs['button_text'];
  }

  $query = http_build_query($props);

  return "<a class=\"restpack-button" .
    ($have_key ? ' restpack-api' : '') .
    "\" data-props=" .
    json_encode($props) .
    (!$have_key ? " href=\"https://restpack.io/html2pdf/save-as-pdf?" . $query . "\"" : " href='#'") .
    "target=\"_blank\" rel=\"nofollow\">
    <img style='display: inline; width:" .
    $options['button_width'] .
    "' src='" .
    $options['button_icon'] .
    "' alt='" .
    $button_text .
    "' title='" .
    $button_text .
    "'/>
    " .
    $button_text .
    "</a>";
}
add_shortcode('restpackpdfbutton', 'shortcode');

add_action('wp_ajax_restpack_ajax', 'restpack_ajax');

function restpack_ajax()
{
  $options = get_option(gen_key("options"));

  $url = $_SERVER['HTTP_REFERER'];

  if ($_POST['url']) {
    $url = $_POST['url'];
  }

  $props = array(
    "json" => true,
    "url" => $url,
    "pdf_margins" => $options['pdf_margins'],
    "pdf_header" => $options['pdf_header'],
    "pdf_footer" => $options['pdf_footer'],
    "emulate_media" => $options['emulate_media'],
    "js" => $options['js'],
    "css" => $options['css'],
    "block_cookie_warnings" => $options['block_cookie_warnings'],
    "block_ads" => $options['block_ads'],
    "filename" => $options['filename']
  );

  function removeEmptyValues(array &$array)
  {
    foreach ($array as $key => &$value) {
      if (is_array($value)) {
        $value = removeEmptyValues($value);
      }
      if (empty($value)) {
        unset($array[$key]);
      }
    }
    return $array;
  }

  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "https://restpack.io/api/html2pdf/v6/convert",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query(removeEmptyValues($props)),
    CURLOPT_HTTPHEADER => array("x-access-token: " . $options['api_key'])
  ));

  $response = curl_exec($curl);
  $err = curl_error($curl);

  curl_close($curl);

  if ($err) {
    echo "cURL Error #:" . $err;
  } else {
    echo $response;
  }

  wp_die();
}

function plugins_page_links($links)
{
  $settings_link = '<a href="options-general.php?page=save-as-pdf/save-as-pdf.php">' . __('Settings') . '</a>';

  array_push($links, $settings_link);

  return $links;
}

function plugins_page_links_meta($links)
{
  $settings_link = [
    'demo' => '<a href="https://restpack.io/html2pdf" target="_blank">Demo</a>',
    'documentation' => '<a href="https://restpack.io/html2pdf/docs" target="_blank">Documentation</a>'
  ];

  return array_merge($links, $settings_link);
}

add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'plugins_page_links');
add_filter('plugin_row_meta', 'plugins_page_links_meta', 10, 2);

function restpack_js()
{
  wp_enqueue_script('restpack', plugin_dir_url(__FILE__) . 'restpack.js', array('jquery'), '1.0');
}
add_action('wp_enqueue_scripts', 'restpack_js');
