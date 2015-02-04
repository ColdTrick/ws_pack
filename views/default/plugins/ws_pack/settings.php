<?php

$plugin = elgg_extract("entity", $vars);

$noyes_options = array(
	"no" => elgg_echo("option:no"),
	"yes" => elgg_echo("option:yes")
);

$colors_1 = array(
	"#fff" => "Light",
	"#f8f8f8" => "Stable",
	"#4a87ee" => "Positive",
	"#43cee6" => "Calm",
	"#66cc33" => "Balanced",
	"#f0b840" => "Energized",
	"#ef4e3a" => "Assertive",
	"#8a6de9" => "Royal",
	"#444" => "Dark"
);

$colors_2 = array(
	"#FFFFFF" => "#FFFFFF",
	"#FCFCFC" => "#FCFCFC",
	"#ffefdb" => "#ffefdb",
	"#f0ffff" => "#f0ffff",
	"#f5f5dc" => "#f5f5dc",
	"#ffd39b" => "#ffd39b",
	"#fff8dc" => "#fff8dc",
	"#97ffff" => "#97ffff",
	"#f8f8ff" => "#f8f8ff",
	"#fff68f" => "#fff68f",
	"#e6e6fa" => "#e6e6fa",
	"#e0ffff" => "#e0ffff"
);

echo "<div>";
echo elgg_echo("ws_pack:settings:allow_application_registration");
echo elgg_view("input/dropdown", array("name" => "params[allow_registration]", "options_values" => $noyes_options, "value" => $plugin->allow_registration, "class" => "mls"));
echo "</div>";

echo "<div>";
echo elgg_echo("Color 1 (menu and headers)");
echo elgg_view("input/dropdown", array(
	"name" => "params[color_1]",
	"options_values" => $colors_1,
	"value" => $plugin->color_1,
	"class" => "mls",
	"onchange" => "$('#color1test').css('background', this.value)"
));
echo "</div>";

/*
echo "<div>";
echo elgg_echo("Color 2 (background)");
echo elgg_view("input/dropdown", array(
	"name" => "params[color_2]",
	"options_values" => $colors_2,
	"value" => $plugin->color_2,
	"class" => "mls",
	"onchange" => "$('#color2test').css('background', this.value)",
	"onload" => "$('#color1test').css('background', this.value)"	
));
echo "</div>";
*/

$image_url = elgg_get_site_url() . "mod/ws_pack/views/default/plugins/ws_pack/img/device.png";
$image_url = elgg_format_url($image_url);

echo "<center>";
echo "<div style=\"background-image:url('" . $image_url . "');background-size:contain; width:200px; height:435px;\">";
echo "<span id='color1test' style='margin-top: 148px;padding: 5px 55px;position: relative;top: 50px; background:" . $plugin->color_1 . "'>Color 1</span>";
		
echo "<br>";
echo "<span id='color2test' style='margin-top: 148px; color:#FFF; padding: 5px 55px 285px;position: relative;top: 57px; height:300px; background:" . $plugin->color_2 . ";'>Color 2</span>";
		
echo "</div>";
echo "</center>";
	