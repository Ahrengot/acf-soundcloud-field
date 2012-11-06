<?php

/*
 *	Advanced Custom Fields - New field template
 *	
 *	Create your field's functionality below and use the function:
 *	register_field($class_name, $file_path) to include the field
 *	in the acf plugin.
 *
 *	Documentation: 
 *
 */
 
 
class Soundcloud_field extends acf_Field
{

	/*--------------------------------------------------------------------------------------
	*
	*	Constructor
	*	- This function is called when the field class is initalized on each page.
	*	- Here you can add filters / actions and setup any other functionality for your field
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function __construct($parent)
	{
		// do not delete!
    	parent::__construct($parent);
    	
    	// set name / title
    	$this->name = 'soundcloud'; // variable name (no spaces / special characters / etc)
		$this->title = __("SoundCloud",'acf'); // field label (Displayed in edit screens)
		
   	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	create_options
	*	- this function is called from core/field_meta_box.php to create extra options
	*	for your field
	*
	*	@params
	*	- $key (int) - the $_POST obejct key required to save the options to the field
	*	- $field (array) - the field object
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_options($key, $field)
	{
		// defaults
		$field['symbol'] = isset($field['symbol']) ? $field['symbol'] : '';
		
		?>
		<tr class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Symbol",'acf'); ?></label>
			</td>
			<td>
				<?php 
				$this->parent->create_field(array(
					'type'	=>	'text',
					'name'	=>	'fields['.$key.'][symbol]',
					'value'	=>	$field['symbol'],
				));
				?>
			</td>
		</tr>
		<?php
	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	create_field
	*	- this function is called on edit screens to produce the html for this field
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function create_field($field)
	{
		if (gettype($field['value']) == 'object') {
			$obj = $field['value'];
			$value = $obj->permalink_url;
		}

		echo '<input type="url" value="' . $value . '" id="' . $field['name'] . '" class="' . $field['class'] . '" name="' . $field['name'] . '">';
		
		if ($obj) {
			if ($obj->kind != 'track') {
				echo "<p class='error'><strong>Error:</strong> This is a link to a {$obj->kind}, not a single track!</p>";
			} else {
				$total_seconds = $obj->duration / 1000;
				$min = round($total_seconds / 60);
				$sec = $total_seconds % 60;

				$min = ($min < 10) ? '0'.$min : $min;
				$sec = ($sec < 10) ? '0'.$sec : $sec;

				?>
				<div class='track-info'>
					<img src='<?= $obj->artwork_url; ?>' width='40' />
					<div class='desc'>
						<p><strong><?= $obj->title; ?></strong></p>
						<p class='duration'>Duration: <?= $min . ':' . $sec; ?></p>
					</div>
				</div>
				<?php
			}
		}
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	admin_head
	*	- this function is called in the admin_head of the edit screen where your field
	*	is created. Use this function to create css and javascript to assist your 
	*	create_field() function.
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function admin_head()
	{
		?>
		<style type="text/css">
			.acf_postbox .field input[type="url"].soundcloud {
				width: 100%;
			}

			.acf_postbox .track-info {
				overflow: hidden;
				margin-top: 5px;
			}

			.acf_postbox .track-info img {
				float: left;
				margin: 0 5px 0 2px;
			}

			.acf_postbox p.error {
				color: red;
			}

			.acf_postbox .track-info p {
				margin: 0; 
			}

			.acf_postbox .track-info p.duration {
				color: #999;
			}

			.acf_postbox .track-info p:first-child {
				margin-top: 2px;
			}

		</style>
		<?php	
	}

	
	/*--------------------------------------------------------------------------------------
	*
	*	update_value
	*	- this function is called when saving a post object that your field is assigned to.
	*	the function will pass through the 3 parameters for you to use.
	*
	*	@params
	*	- $post_id (int) - usefull if you need to save extra data or manipulate the current
	*	post object
	*	- $field (array) - usefull if you need to manipulate the $value based on a field option
	*	- $value (mixed) - the new value of your field.
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function update_value($post_id, $field, $value)
	{
		// Do stuff with the value
		$resolve_link = 'http://api.soundcloud.com/resolve.json?url=' . $value . '&client_id=4dff1d7d1d48502da29b39a453d57756';
		
		// Make sure the link doesn't return a 404
		$headers = get_headers($resolve_link);
		$server_status = substr($headers[0], 9, 3);

		if ($server_status != '404') {
			$resolve_json = file_get_contents($resolve_link);
		}

		if (isset($resolve_json)) $track_info = json_decode($resolve_json);
		
		$value =  $track_info ? $track_info : '';
		
		// save value
		parent::update_value($post_id, $field, $value);
	}
	
	
	
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value
	*	- called from the edit page to get the value of your field. This function is useful
	*	if your field needs to collect extra data for your create_field() function.
	*
	*	@params
	*	- $post_id (int) - the post ID which your value is attached to
	*	- $field (array) - the field object.
	*
	*	@author Elliot Condon
	*	@since 2.2.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value($post_id, $field)
	{
		// get value
		$value = parent::get_value($post_id, $field);
		
		// return value
		return $value;		
	}
	
	
	/*--------------------------------------------------------------------------------------
	*
	*	get_value_for_api
	*	- called from your template file when using the API functions (get_field, etc). 
	*	This function is useful if your field needs to format the returned value
	*
	*	@params
	*	- $post_id (int) - the post ID which your value is attached to
	*	- $field (array) - the field object.
	*
	*	@author Elliot Condon
	*	@since 3.0.0
	* 
	*-------------------------------------------------------------------------------------*/
	
	function get_value_for_api($post_id, $field)
	{
		// get value
		$value = $this->get_value($post_id, $field);
		
		// format value
		
		// return value
		return $value;

	}
	
}

?>