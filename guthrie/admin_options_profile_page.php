<?php
/*
Manage our profile
*/
?>
<?php 
global $guthrie;
global $admin_options;

$profile_field_instances = $guthrie->get_profile_field_instances();
$profile_roles = $guthrie->get_profile_roles();
$profile_field_types = $guthrie->get_profile_field_types();
$admin_options_tab = $admin_options->admin_options_tab;
?>
<h2 class="profile-fields">Profile Fields</h2>
<table id="profile-fields">
	<thead>
		<tr>
			<th />
			<th>Name [tag]</th>
			<th>Value <span class="instructions">(click to edit in place)</span></th>
			<th>Roles</th>
		</tr>
	</thead>
	<tbody id="profile-field-instances">
	<?php $i=0; ?>
	<?php foreach ($profile_field_instances as $field): ?>
		<tr class="profile-field-instance" id="field-instance_<?php echo($field->id); ?>" tabindex="<?php echo( $i ) ?>" >
			<td class="draggable-vertical draggable-field-handle">&nbsp;</td>
			<td><label for="<?php echo($field->tag) ?>_<?php echo($field->id) ?>" ><?php echo($field->name) ?> [<?php echo($field->tag) ?>]:</label></td>
			<td><div id="<?php echo($field->tag); ?>_<?php echo($field->id); ?>" name="<?php echo($field->tag); ?>_<?php echo($field->id); ?>" class="profile-field"><?php echo($field->value); ?></div></td>
			<td>
				<select multiple class="field-roles chzn-select roles-select" id="<?php echo($field->tag) ?>_<?php echo($field->id) ?>_role" data-placeholder="Choose a role...">
					<option value=""></option>
					<?php $chosen_roles = explode ( ',' , $field->roles ); ?>

					<?php foreach ($profile_roles as $role): ?>
					<?php 
						$selected = '';
						if(in_array($role->id ,$chosen_roles)){
							$selected = ' selected="selected"';
						}
						?>
					<option<?php echo($selected) ?> value="<?php echo($role->id); ?>"><?php echo($role->name) ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<form action="<?php the_permalink(); ?>" id="profileAddForm" method="post">
	<h2 class="add-a-field">Add a Field</h2>
	<input type="hidden" value="submitted" name="submitted" />
	<table class="form-table">
		<tbody>
			<tr>
				<th class="row">
					<label for="profile-field-add-field-name">Name </label>
				</th>
				<td>
					<input type="text" name="profile-field-add-field-name" id="profile-field-add-field-name" value="<?php echo( $admin_options_tab->name ); ?>" class="regular-text" />
					<span class="description">required</span>
					<?php if ( ! ('' == $admin_options_tab->name_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->name_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for="profile-field-add-field-tag">Tag </label>
				</th>
				<td>
					<input type="text" name="profile-field-add-field-tag" id="profile-field-add-field-tag" value="<?php echo( $admin_options_tab->tag ); ?>" class="regular-text" />
					<span class="description">required</span>
					<?php if ( ! ('' == $admin_options_tab->tag_error) ) : ?>
						<div class="error"><?php echo( $admin_options_tab->tag_error ); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for="profile-field-add-field-type">Type </label>
				</th>
				<td>
					<select name="profile-field-add-field-type" id="profile-field-add-field-type" class="field-type-select">
						<?php $selected = ''; ?>
						<?php foreach ($profile_field_types as $field_type): ?>
							<?php if ( $admin_options_tab->type == $field_type->id ) { $selected = ' selected="selected"'; } ?>
							<option value="<?php echo( $field_type->id ); ?>"<?php echo( $selected ); ?> ><?php echo( $field_type->name ); ?></option>
						<?php endforeach; ?>					
					</select>
					<span class="description">required</span>
					<?php if ( ! ('' == $admin_options_tab->type_error) ) : ?>
						<div class="error"><?php echo( $admin_options_tab->type_error ); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for="profile-field-add-field-description">Description</label>
				</th>
				<td>
					<textarea height="5" name="profile-field-add-field-description" id="profile-field-add-field-description"><?php echo( $admin_options_tab->description ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for="profile-field-add-field-value">Value</label>
				</th>
				<td>
					<textarea height="1" name="profile-field-add-field-value" id="profile-field-add-field-value"><?php echo( $admin_options_tab->value ); ?></textarea>
					<?php if ( ! ('' == $admin_options_tab->value_error) ) : ?>
						<div class="error"><?php echo( $admin_options_tab->value_error ); ?></div>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<button class="button-primary" id="field-instance-add-button" type="submit">Add Field</button>
	</p>
</form>
