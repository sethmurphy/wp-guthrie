<?php
/*
Manage our roles
*/
?>
<?php 
global $guthrie;
global $admin_options;

$profile_roles = $guthrie->get_profile_roles();
$admin_options_tab = $admin_options->admin_options_tab;
?>
<h3 class="manage-roles">Manage Roles</h3>
<?php
/*
Manage our profile
*/
?>
<?php 
global $guthrie;
global $admin_options;

$profile_roles = $guthrie->get_profile_roles();
$admin_options_tab = $admin_options->admin_options_tab;
?>
<table id="profile-roles">
	<thead>
		<tr>
			<th />
			<th>Name <span class="instructions">(click to edit in place)</span></th>
			<th>Description <span class="instructions">(click to edit in place)</span></th>
		</tr>
	</thead>
	<tbody id="profile-roles">
	<?php $i=0; ?>
	<?php foreach ($profile_roles as $role): ?>
		<tr class="profile-role" id="role_<?php echo($role->id); ?>" tabindex="<?php echo( $i ) ?>" >
			<td valign="top" class="draggable-vertical draggable-field-handle">&nbsp;</td>
			<td valign="top"><div id="name_<?php echo($role->id); ?>" name="name_<?php echo($role->id); ?>" class="profile-role-name"><?php echo($role->name); ?></div></td>
			<td valign="top"><div id="description_<?php echo($role->name); ?>" name="description_<?php echo($role->id); ?>" class="profile-role-description"><?php echo($role->description); ?></div></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<form action="<?php the_permalink(); ?>" id="roleAddForm" method="post">
	<h2 class="add-a-field">Add a Role</h2>
	<input type="hidden" value="submitted" name="submitted" />
	<table class="form-table">
		<tbody>
			<tr>
				<th class="row">
					<label for="profile-role-name">Name </label>
				</th>
				<td>
					<input type="text" name="profile-add-role-name" id="profile-add-role-name" value="<?php echo( $admin_options_tab->name ); ?>" class="regular-text" />
					<span class="description">required</span>
					<?php if ( ! ('' == $admin_options_tab->name_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->name_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th class="row">
					<label for="profile-role-description">Description </label>
				</th>
				<td>
					<input type="text" name="profile-add-role-description" id="profile-add-role-description" value="<?php echo( $admin_options_tab->description ); ?>" class="regular-text" />
					<span class="description">required</span>
					<?php if ( ! ('' == $admin_options_tab->description_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->description_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<button class="button-primary" id="field-instance-add-button" type="submit">Add Field</button>
	</p>
</form>

