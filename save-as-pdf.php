<?php

/**
 * Plugin Name: Save as PDF
 * Plugin URI: https://restpack.io/html2pdf
 * Description: Allows visitors to save current page as PDF file
 * Version: 1.0.0
 * Text Domain: save-as-pdf
 * Author: Restpack Inc
 * Author URI: https://restpack.io
 * Tested up to: 5.2.1
 * License: GNUGPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

class Restpack
{
  public $icon_list = array(
    "icon-1.svg",
    "icon-2.svg",
    "icon-3.svg",
    "icon-5.svg",
    "icon-6.svg",
    "icon-7.svg",
    "icon-8.svg",
    "icon-9.svg",
    "icon-10.svg",
    "icon-11.svg",
    "icon-12.svg",
    "icon-13.svg"
  );
  private $prefix = "rpkey";

  public $default = array(
    "pdf_page" => "Full",
    "pdf_orientation" => "portrait",
    "delay" => 500,
    "button_text" => "Save as PDF",
    "button_icon" => "icon-1.svg",
    "button_width" => "25px",
    "button_position" => "Above Content",
    "button_align" => "Left",
    "display_pages_posts" => true,
    "display_pages_pages" => true
  );

  function __construct()
  {
    register_activation_hook(__FILE__, array(&$this, 'plugin_init'));
    register_setting($this->key("options"), $this->key("options"), array(&$this, 'options_validate'));

    add_filter('the_content', array(&$this, 'render_button'));
    add_filter("plugin_action_links_" . plugin_basename(__FILE__), array(&$this, 'plugins_page_links'));
    add_filter('plugin_row_meta', array(&$this, 'plugins_page_links_meta'), 10, 2);

    add_action('admin_menu', array(&$this, 'insert_menu'));
    add_action('admin_init', array(&$this, 'init'));
    add_action('wp_footer', array(&$this, 'insert_admin_url_in_footer'));

    add_action('wp_enqueue_scripts', array(&$this, 'restpack_js'));
    add_action('wp_ajax_restpack_ajax', array(&$this, 'restpack_ajax'));

    add_shortcode('restpackpdfbutton', array(&$this, 'shortcode'));
  }

  function insert_admin_url_in_footer()
  {
    $url = admin_url("admin-ajax.php");
    echo "<script>window.ajaxcallurl = \"$url\";</script>";
  }

  function plugin_init()
  {
    update_option($this->key("options"), $this->default);
  }

  function insert_menu()
  {
    add_options_page('Restpack PDF', 'Restpack PDF', 'administrator', __FILE__, array(&$this, 'render_settings_page'));
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

  function restpack_js()
  {
    wp_enqueue_script('restpack', plugin_dir_url(__FILE__) . 'restpack.js', '1.0');
  }

  function key($name)
  {
    return $this->prefix . $name;
  }

  function options_validate($input)
  {
    $input['text_string'] = $input['text_string'];
    return $input;
  }

  function render_button($content = "")
  {
    $options = get_option($this->key("options"));
    $button = "<div style='text-align:" . $options['button_align'] . " '>" . $this->shortcode(array()) . "</div>";

    if (($options['display_pages_posts'] && is_single()) || ($options['display_pages_pages'] && is_page())) {
      if ($options['button_position'] === 'Above Content') {
        $content = $button . $content;
      } else {
        $content = $content . $button;
      }
    }

    return $content;
  }

  function init()
  {
    $first_section = $this->key("first");

    add_settings_section($first_section, 'Render', 'styling', __FILE__);

    function styling()
    {
      echo "
      <style>
        textarea { width: 320px; height: 70px; }
        input { width: 250px; }
        label  { margin-right: 15px; }
      </style>";
    }

    add_settings_field(
      $this->key("pdf_page"),
      'PDF page format',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $first_section,
      array(
        "key" => "pdf_page"
      )
    );

    add_settings_field(
      $this->key("pdf_orientation"),
      'PDF page orientation',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $first_section,
      array("key" => "pdf_orientation")
    );

    add_settings_field($this->key("delay"), 'Delay', array(&$this, 'render_settings_field'), __FILE__, $first_section, array(
      "key" => "delay"
    ));

    $second_section = $this->key("second");

    add_settings_section($second_section, 'Display', null, __FILE__);
    add_settings_field(
      $this->key("display_pages"),
      'Display button on',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $second_section,
      array(
        "key" => "display_pages"
      )
    );
    add_settings_field(
      $this->key("button_text"),
      'Button Text',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $second_section,
      array(
        "key" => "button_text"
      )
    );
    add_settings_field(
      $this->key("button_icon"),
      'Button Icon',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $second_section,
      array(
        "key" => "button_icon"
      )
    );
    add_settings_field(
      $this->key("button_width"),
      'Button Witdh',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $second_section,
      array(
        "key" => "button_width"
      )
    );
    add_settings_field(
      $this->key("button_position"),
      'Position',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $second_section,
      array(
        "key" => "button_position"
      )
    );
    add_settings_field(
      $this->key("button_align"),
      'Align',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $second_section,
      array(
        "key" => "button_align"
      )
    );

    $third_section = $this->key("third");
    add_settings_section($third_section, 'Premium Features', 'premium_features_text', __FILE__);

    function premium_features_text()
    {
      echo "
      <p>
        In order to use these features, you need to subscribe and get access_token from <a href=\"https://restpack.io/html2pdf?utm_source=wp\" target=\"_blank\">Restpack HTML to PDF API</a>. <br />
        <a href=\"https://restpack.io/html2pdf?utm_source=wp\" target=\"_blank\">Start 7-Day Trial</a>
      </p>";
    }

    add_settings_field(
      $this->key("api_key"),
      'Access Token',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $third_section,
      array(
        "key" => "api_key"
      )
    );

    add_settings_field(
      $this->key("pdf_margins"),
      'PDF margins',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $third_section,
      array(
        "key" => "pdf_margins",
        "placeholder" => "10px 20px 10px 20px"
      )
    );

    add_settings_field(
      $this->key("pdf_header"),
      'PDF header',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $third_section,
      array(
        "key" => "pdf_header",
        "textarea" => true,
        "placeholder" => "<div>Your company name and logo</div>"
      )
    );

    add_settings_field(
      $this->key("pdf_footer"),
      'PDF footer',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $third_section,
      array(
        "key" => "pdf_footer",
        "textarea" => true,
        "placeholder" => "<div> <span class='pageNumber'></span> of <span class='totalPages'></span>  </div>"
      )
    );

    add_settings_field(
      $this->key("emulate_media"),
      'Emulate media',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $third_section,
      array(
        "key" => "emulate_media"
      )
    );

    add_settings_field($this->key("js"), 'JS inject', array(&$this, 'render_settings_field'), __FILE__, $third_section, array(
      "key" => "js",
      "placeholder" => "document.body.style.backgrond='black';",
      "textarea" => true
    ));

    add_settings_field($this->key("css"), 'CSS inject', array(&$this, 'render_settings_field'), __FILE__, $third_section, array(
      "key" => "css",
      "placeholder" => "h1 {font-size : 25px}",
      "textarea" => true
    ));

    add_settings_field(
      $this->key("block_cookie_warnings"),
      'Block EU cookie warning',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $third_section,
      array(
        "key" => "block_cookie_warnings"
      )
    );

    add_settings_field(
      $this->key("block_ads"),
      'Block Ads',
      array(&$this, 'render_settings_field'),
      __FILE__,
      $third_section,
      array(
        "key" => "block_ads"
      )
    );
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
    $options = get_option($this->key("options"));
    $html = "";

    $default_attrs = "id='" . $arg["key"] . "' name='" . $this->key("options") . "[" . $arg["key"] . "]'";
    $value = $options[$arg["key"]];

    switch ($arg["key"]) {
      case 'pdf_page':
        $opts = array("A0", "A1", "A2", "A3", "A4", "A5", "A6", "Legal", "Letter", "Tabloid", "Ledger", "Full");
        $html = "<select " . $default_attrs . ">" . $this->render_option_tag($opts, $value) . "</select>";
        break;

      case 'pdf_orientation':
        $opts = array("portrait", "landscape");
        $html = "<select " . $default_attrs . ">" . $this->render_option_tag($opts, $value) . "</select>";
        break;

      case 'emulate_media':
        $opts = array("screen", "print");
        $html = "<select " . $default_attrs . ">" . $this->render_option_tag($opts, $value) . "</select>";
        break;

      case 'block_ads':
      case 'block_cookie_warnings':
        $html =
          "<input id='" .
          $arg["key"] .
          "' name='" .
          $this->key("options") .
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
          $this->key("options") .
          "[" .
          $arg["key"] .
          "_posts]' type='checkbox' " .
          ($options[$arg["key"] . '_posts'] ? " checked=checked" : "") .
          " />
					Posts
				</label>

				<label>
				<input id='" .
          $arg["key"] .
          "' name='" .
          $this->key("options") .
          "[" .
          $arg["key"] .
          "_pages]' type='checkbox' " .
          ($options[$arg["key"] . '_pages'] ? " checked=checked" : "") .
          " />
					Pages
				</label>";
        break;

      case 'button_icon':
        $html = "";

        foreach ($this->icon_list as $icon) {
          $html .=
            "<label><input value='" .
            $icon .
            "' id='" .
            $arg["key"] .
            "' name='" .
            $this->key("options") .
            "[" .
            $arg["key"] .
            "]' type='radio' " .
            ($value == $icon ? " checked=checked" : "") .
            " />
              <img src='" .
            plugin_dir_url(__FILE__) .
            "assets/" .
            $icon .
            "' style='width: 18px;' alt='Save as pdf' title='Save as pdf'/></label>";
        }
        break;

      case "button_position":
        $opts = array("Above Content", "Below Content");
        $html = "<select " . $default_attrs . ">" . $this->render_option_tag($opts, $value) . "</select>";
        break;

      case "button_align":
        $opts = array("Left", "Center", "Right");
        $html = "<select " . $default_attrs . ">" . $this->render_option_tag($opts, $value) . "</select>";
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

  function shortcode($attrs)
  {
    $options = get_option($this->key("options"));
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
      plugin_dir_url(__FILE__) .
      'assets/' .
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

  function restpack_ajax()
  {
    $options = get_option($this->key("options"));

    $url = $_SERVER['HTTP_REFERER'];

    if ($_POST['url']) {
      $url = esc_url($_POST['url']);
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

    $args = array(
      'body' => removeEmptyValues($props),
      'timeout' => '10',
      'headers' => array("x-access-token" => $options['api_key'])
    );

    $response = wp_remote_post('https://restpack.io/api/html2pdf/v6/convert', $args);

    echo $response['body'];
    wp_die();
  }
  function render_settings_page()
  {
    ?><div class="wrap">
		<h2>Restpack PDF Settings</h2>
		<form action="options.php" method="post">

		<?php settings_fields($this->key('options')); ?>
		<?php do_settings_sections(__FILE__); ?>

		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
		</form>
	</div><?php
  }
}

$restpack = new Restpack();
