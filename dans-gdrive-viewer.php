<?php

/**
* Plugin Name: Dans Google Drive Viewer
* Plugin URI: https://www.wptechguides.com
* Description: A custom Google Drive Viewer w/ Folder Transversal and File Export
* Version: 1.1
* Author: Dan D
* Author URI: https://www.convexcode.com
* Text Domain: dgdrive
* Domain Path: /languages/
**/

//loads my plugin translation files
function dans_gdrive_load_translation_files() {
	load_plugin_textdomain('dgdrive', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

//add action to load plugin translation files
add_action('plugins_loaded', 'dans_gdrive_load_translation_files');


//enqueues all js files needed
function gdrive_enqueue_script() {

	wp_enqueue_script( 'dans-gdrive-js', plugin_dir_url( __FILE__ ) . 'js/dandrive.js', array('jquery'), false );
	wp_localize_script( 'dans-gdrive-js', 'objectL10n', array(
	'DL' => esc_html__( 'DL', 'dgdrive' ),
	'Export' => esc_html__( 'Export', 'dgdrive' ),
) );

}
add_action( 'wp_enqueue_scripts', 'gdrive_enqueue_script' );


//creates an entry on the admin menu for dan-gdrive-plugin
add_action('admin_menu', 'dans_gdrive_plugin_menu');

//creates a menu page with the following settings
function dans_gdrive_plugin_menu() {

	add_submenu_page('tools.php', 'Dan\'s gDrive', 'Dan\'s gDrive', 'manage_options', 'dans-gdrive-settings', 'dans_gdrive_display_settings');

}

//on-load, sets up the following settings for the plugin
add_action( 'admin_init', 'dans_gdrive_settings' );

function dans_gdrive_settings() {
	register_setting( 'dans-gdrive-settings-group', 'gdrive_api_key' ); //api key
	register_setting( 'dans-gdrive-settings-group', 'gdrive_driveids' ); //array of drive ids
}



//displays the settings page
function dans_gdrive_display_settings() {

	//form to save api key and drive settings
	echo "<form method=\"post\" action=\"options.php\">";

	settings_fields( 'dans-gdrive-settings-group' );
	do_settings_sections( 'dans-gdrive-settings-group' );

echo "<script>function addRow(nextnum,nextdisp){


	var toremove = 'addrowbutton';
	var elem = document.getElementById(toremove);
    elem.parentNode.removeChild(elem);

	var table = document.getElementById(\"gdrive-settings\");
	var row = table.insertRow(-1);
	var cell1 = row.insertCell(0);
	var cell2 = row.insertCell(1);
	c1var = '<b>Google Drive ID ('+nextdisp+')</b>';
	cell1.innerHTML = c1var;
	var newnextdisp= nextdisp+1;
	c2var = '<input type=\"text\" name=\"gdrive_driveids['+nextnum+']\" size=\"80\"><button type=\"button\" id=\"addrowbutton\" onClick=\"addRow('+nextdisp+','+newnextdisp+')\">Add Row</button>';
	cell2.innerHTML = c2var;
}</script>

";

	//paragraph giving plugin explanation, api setup instructions, and shortcode information
	echo '
	<div><h1>' . esc_html__( 'Dan\'s Google Drive Viewer Settings', 'dgdrive' ) . '</h1>

	<p>' . esc_html__( 'Welcome! This is a basic Google Drive Viewer integration plugin, with the following features.', 'dgdrive' ) . '
	<ul style=\"list-style-type:square\">
		<li>' . esc_html__( 'Displays public drive document listings in a mobile friendly format', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'Offers both View and Download Options, where appropriate', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'All options are configured via shortcode', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'Offers Export Option for Google Docs / Sheets / Presentations', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'Allows Drill Down / Up without Leaving the Page', 'dgdrive' ) . '</li>
	</ul>
	<br>
	<strong>' . esc_html__( 'Shortcodes:', 'dgdrive' ) . '</strong>
	<ul style=\"list-style-type:square\">
		<li>' . esc_html__( 'Default Display [dandrive] (defaults to 1st drive)', 'dgdrive' ) . '</li>
	</ul>

	' . esc_html__( 'Optional Attributes Ex:[dandrive drive=1 divid=mydrive height=400 width=300 rbutton=no]', 'dgdrive' ) . '
	<ul style=\"list-style-type:square\">
		<li>' . esc_html__( 'drive= (number of the drive you want, defaults to 1 if not entered)', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'divid= (id of the div your calendar is stored in, for custom theming. Defaults to random string to allow multiple per page)', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'height= (maximum height in pixels, defaults to auto. If this is set, a scroll bar will appear if your div overflows. Enter a number only).', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'width = (maximum width in pixels, defaults to 400. Enter a number only)', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'rbutton = (no, to not display return to initial folder button, if you have files only in the folder directly linked with no subfolders. Otherwise, leave this attribute off).', 'dgdrive' ) . '</li>
	</ul>
	<br>
	' . esc_html__( 'To create API key, visit <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a> Then, follow bellow;', 'dgdrive' ) . '

	<ul style=\"list-style-type:square\">
		<li>' . esc_html__( 'Create new project (or use project you created before).', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'Check "APIs & auth" -> "Credentials" on side menu.', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'Hit "Create new Key" button on "Public API access" section.', 'dgdrive' ) . '</li>
		<li>' . esc_html__( 'Choose "Browser key" and keep blank on referer limitation.', 'dgdrive' ) . '</li>
	</ul>
	</p>';

//Settings to be saved
echo "
<table id=\"gdrive-settings\" class=\"form-table\" aria-live=\"assertive\">
	<tr><td colspan=\"2\"><h2>" . esc_html__( 'API KEY - Google Drive Viewer (All REQUIRED)', 'dgdrive' ) . "</h2></td></tr>
       <tr valign=\"top\">
        <th scope=\"row\">" . esc_html__( 'Google Drive API Key', 'dgdrive' ) . "</th>
        <td><input type=\"text\" name=\"gdrive_api_key\" size=\"80\" value=\"".esc_attr( get_option('gdrive_api_key') )."\" /></td></tr>

<tr><td colspan=\"2\"><h2>Google Drive Folder IDs</h2></td></tr>";

$gdrive_driveids = get_option('gdrive_driveids');
$num_drives = 0;
$num_drives = count($gdrive_driveids);

if ($num_drives > 1) $showrows=$num_drives;
else $showrows = 1;

for ($i=0;$i < $showrows; $i++) {
	$nextid = $i+1;
	$nextdisp = $i+2;
	$drivenum = $i+1;
	echo "
       <tr valign=\"top\">
        <th scope=\"row\">Google Drive ID ($drivenum)</th>
        <td><input type=\"text\" name=\"gdrive_driveids[$i]\" size=\"80\" value=\"$gdrive_driveids[$i]\"/>
";

if (($showrows -1) == $i) {

echo '<button type=\"button\" id=\"addrowbutton\" onClick=\"addRow($nextid,$nextdisp)\">' . esc_html__( 'Add Row', 'dgdrive' ) . '</button>';

}
echo '</td></tr>';

}

   echo" </table>";

    submit_button();

	echo "</form>";




}


//function displays folder on shortcode base: [dandrive]
function dandrive_display($atts) {




	$gdrive_api_key = esc_attr( get_option('gdrive_api_key') );

	if ($gdrive_api_key == '') {

		$error = 'You must first enter a valid Google Drive API key.';
		return $error;
	}


	//generates a random div id to allow multiple on one page, if one isn't specified in shortcode
	$randdiv = 'a'.substr(md5(microtime()),rand(0,26),10);

	//Handles attribures. If none are specified, defaults to no scroll, 1st drive
	$atts = shortcode_atts(
        array(
					'drive' => 1,
					'divid' => $randdiv,
					'height' => 'auto',
					'width' => '400',
					'rbutton' => 'yes',
				), $atts, 'dandrive' );

	$drive = $atts['drive'];
	$divid = $atts['divid'];
	$maxheight = $atts['height'];
	$maxwidth = $atts['width'];
	$rbutton = $atts['rbutton'];

	//adds pixels to height
	if ($maxheight!='auto') {
		$maxheight .= 'px';
	}


	//adds pixels to width
	if ($maxwidth!='auto') {
		$maxwidth .= 'px';
	}

	//Makes sure that scroll is a valid true / false option
	if ($scroll != 'true') $scroll = 'false';

	$gdrive_driveids = get_option('gdrive_driveids');

	$gdrive_num = $drive-1;

	$drive = $gdrive_driveids[$gdrive_num];

	if ($drive == '' || $drive == 'broken') {

		$error = 'You must first enter a valid Google Drive id.';
		return $error;
	}


	//default-styling
	$disp = "<style>
#glist-$divid tr td {

	background:white;

}
#$divid {

	background:black;
	display:inline-block;
	max-height:$maxheight;
	width:100%;
	max-width:$maxwidth;
	overflow-x:hidden;";


if ($maxheight != 'auto') {

	$disp.= "
	overflow-y:scroll;

";

}

$disp .= "
}
#glist-$divid tr td button { padding: 0.4em 0.4em!important}
#glist-$divid tr th {

	color:white;

}
</style>
</head>
<body>
<script>
var folderId = '$drive';
var url = 'https://www.googleapis.com/drive/v3/files?q=\'' + folderId + '\'+in+parents&orderBy=folder,name&key=$gdrive_api_key';

var promise = jQuery.getJSON( url, function( data, status){
    // on success
});
promise.done(function( data ){

var inner='<table id=\"glist-$divid\" cellpadding=\"5\" aria-live=\"assertive\"><tr><th>" . esc_html__( 'View', 'dgdrive' ) . "</th>';

/*<th>" . esc_html__( 'Type', 'dgdrive' ) . "</th><th>" . esc_html__( 'View', 'dgdrive' ) . "</th>*/

inner += '<th>" . esc_html__( 'Download', 'dgdrive' ) . "</th></tr>';

for (i = 0; i < data.files.length; i++) {

	tempinner = print_file_row(data,i,'$gdrive_api_key');
	inner += tempinner;

}

inner += '</table>';

	document.getElementById('$divid').innerHTML = inner;";

if ($rbutton == 'yes') {

	$disp .= "
document.getElementById('return-to-root-$divid').innerHTML = '<table><tr><td><button onClick=loadSub(\''+folderId+'\',\''+folderId+'\',\'$gdrive_api_key\')>" . esc_html__( 'Return To Initial Folder', 'dgdrive' ) . "</button></td></tr></table>';";

}

$disp .= "
}).fail(function(){

})



//function loads after first page load, when folder is clicked.

function loadSub(folderId,hId) {

	var url = 'https://www.googleapis.com/drive/v3/files?q=\'' + folderId + '\'+in+parents&orderBy=folder,name&key=$gdrive_api_key';

	var sub = jQuery.getJSON( url, function( data, status){
    // on success
	});

	sub.done(function( data ){


		var inner='<table id=\"glist-$divid\" cellpadding=\"5\"  aria-live=\"assertive\" style=\"max-width:100%\"><tr><th>View</th>';

/*<th>Type</th><th>" . esc_html__( 'View', 'dgdrive' ) . "</th>*/

		inner += '<th>" . esc_html__( 'Download', 'dgdrive' ) . "</th></tr>';

		for (i = 0; i < data.files.length; i++) {

			tempinner = print_file_row(data,i,'$gdrive_api_key');
			inner += tempinner;
		}

		inner += '</table>';

		document.getElementById('$divid').innerHTML = inner;

	}).fail(function(){

	});

}
</script>";

if ($rbutton == 'yes') {

	$disp .= "<div id=\"return-to-root-$divid\"  aria-live=\"assertive\"></div>";
}
$disp .= "<div id='$divid'  aria-live=\"assertive\"><h2 style=\"color:white;padding:20px;max-width:100%\">" . esc_html__( 'Loading Drive Folder...', 'dgdrive' ) . "</h2></div>

</body>";





	return $disp;

}

add_shortcode('dandrive', 'dandrive_display');
