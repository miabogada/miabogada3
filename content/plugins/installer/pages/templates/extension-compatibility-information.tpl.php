<?php
$css = file_get_contents(WPRC_ASSETS_DIR.'/css/wprc.css');
echo '<style type="text/css">'.$css.'</style>';

echo '<h3>'.sprintf(__('Compatibility information of "%s"','installer'),$check_extension_name).'</h3>';

if($no_compatibility_information)
{
    echo __('There is no compatibility information for specified extension', 'installer');
}
else
{

    echo '
        <table class="list">
        <tr>
        <th>'.__('Is compatible with','installer').'</th>
        <th>'.__('Works','installer').'</th>
        <th>'.__('Broken','installer').'</th>
        <th>'.__('Compatibility score','installer').'</th>
        </tr>';

    if (isset($right_extensions) && is_array($right_extensions))
	{
		foreach($right_extensions AS $key => $extension)
		{
			echo '<tr>
				<td>'.$extension['name'].'</td>
				<td align="center">'.$extension['works'].'</td>
				<td align="center">'.$extension['broken'].'</td>
				<td align="center">'.$extension['score'].'</td>
				</tr>';
		}
	}
    echo '</table>';
}

?>