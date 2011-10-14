<?php
/*
Template Name: Profiles
*/
?>
<?php 
	global $guthrie;
	require_once( 'admin_options.php' );
	$admin_options = new Guthrie_Admin_Options( $guthrie );	
	$profile_field_instances = $guthrie->get_profile_field_instances();
	$profile_roles = $guthrie->get_profile_field_roles();
	$profile_field_types = $guthrie->get_profile_field_types();

?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div><h2>Guthrie Settings</h2>

	<h3 class="send-an-invite">Send an Invitation</h3>
	<form action="<?php the_permalink(); ?>" id="profileInviteForm" method="post">
		<input type="hidden" name="submitted-invite" id="submitted-invite" value="true" />
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th class="row">
						<label for="profile-invite-email">Email</label>
					</th>
					<td>
						<input type="text" name="profile-invite-email" id="profile-invite-email" value="<?php echo( $admin_options->profile_invite_email ); ?>"  class="regular-text" />
						<span class="description">required</span>
						<?php if ( ! ('' == $admin_options->profile_invite_email_error) ) : ?>
							<div class="error"><?php echo($admin_options->profile_invite_email_error); ?></div>
						<?php endif; ?>
					</td>
				</tr>
				<tr valign="top">
					<th class="row">
						<label for="profile-invite-name">Name </label>
					</th>
					<td>
						<input type="text" name="profile-invite-name" id="profile-invite-name" value="<?php echo( $admin_options->profile_invite_name ); ?>"  class="regular-text" />
						<?php if ( ! ('' == $admin_options->profile_invite_name_error) ) : ?>
							<div class="error"><?php echo($admin_options->profile_invite_name_error); ?></div>
						<?php endif; ?>
					</td>
				</tr>
				<tr valign="top">
					<th class="row">
						<label for="profile-invite-description">Description </label>
					</th>
					<td>
						<input type="text" name="profile-invite-description" id="profile-invite-description" value="<?php echo( $admin_options->profile_invite_description ); ?>"  class="regular-text" />
						<?php if ( ! ('' == $admin_options->profile_invite_description_error) ) : ?>
							<div class="error"><?php echo($admin_options->profile_invite_description_error); ?></div>
						<?php endif; ?>
					</td>
				</tr>
				<tr valign="top">
					<th class="row">
						<label for="profile-invite-field-roles">Relationships </label>
					</th>
					<td>
						<select multiple class="profile-invite-field-roles chzn-select roles-select" id="profile_invite_field_roles" data-placeholder="Choose a role...">
							<option value=""></option>

							<?php foreach ($admin_options->profile_roles as $role): ?>
							<option value="<?php echo($role->id); ?>"><?php echo($role->name) ?></option>
							<?php endforeach; ?>
						</select>
			

						<?php if ( ! ('' == $admin_options->profile_invite_field_roles_error) ) : ?>
							<div class="error"><?php echo($admin_options->profile_invite_field_roles_error); ?></div>
						<?php endif; ?>
					</td>
				</tr>
			<tbody>
		</table>
		<p class="submit">
			<button class="button-primary" id="field-instance-add-button" type="submit">Send Invitation</button>
		</p>
	</form>

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
		<table class="form-table">
			<tbody>
				<tr>
					<th class="row">
						<label for="profile-field-add-field-name">Name </label>
					</th>
					<td>
						<input type="text" name="profile-field-add-field-name" id="profile-field-add-field-name" value="<?php echo( $admin_options->name ); ?>" class="regular-text" />
						<span class="description">required</span>
						<?php if ( ! ('' == $admin_options->name_error) ) : ?>
							<div class="error"><?php echo($admin_options->name_error); ?></div>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th class="row">
						<label for="profile-field-add-field-tag">Tag </label>
					</th>
					<td>
						<input type="text" name="profile-field-add-field-tag" id="profile-field-add-field-tag" value="<?php echo( $admin_options->tag ); ?>" class="regular-text" />
						<span class="description">required</span>
						<?php if ( ! ('' == $admin_options->tag_error) ) : ?>
							<div class="error"><?php echo( $admin_options->tag_error ); ?></div>
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
								<?php if ( $admin_options->type == $field_type->id ) { $selected = ' selected="selected"'; } ?>
								<option value="<?php echo( $field_type->id ); ?>"<?php echo( $selected ); ?> ><?php echo( $field_type->name ); ?></option>
							<?php endforeach; ?>					
						</select>
						<span class="description">required</span>
						<?php if ( ! ('' == $admin_options->type_error) ) : ?>
							<div class="error"><?php echo( $admin_options->type_error ); ?></div>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th class="row">
						<label for="profile-field-add-field-description">Description</label>
					</th>
					<td>
						<textarea height="5" name="profile-field-add-field-description" id="profile-field-add-field-description"><?php echo( $admin_options->description ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th class="row">
						<label for="profile-field-add-field-value">Value</label>
					</th>
					<td>
						<textarea height="1" name="profile-field-add-field-value" id="profile-field-add-field-value"><?php echo( $admin_options->value ); ?></textarea>
						<?php if ( ! ('' == $admin_options->value_error) ) : ?>
							<div class="error"><?php echo( $admin_options->value_error ); ?></div>
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<button class=button-primary" id="field-instance-add-button" type="submit">Add Field</button>
		</p>
	</form>
</div>
