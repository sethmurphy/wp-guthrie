<?php
/*
Author: Seth Murphy
Author URI: http://sethmurphy.com
License: GPL2
Copyright 2011	Seth Murphy	(email : seth@sethmurphy.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, AS 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

/*
Manage our roles
*/

global $guthrie;
global $admin_options;

$profile_roles = $guthrie->get_profile_roles();
$admin_options_tab = $admin_options->admin_options_tab;
?>
<h3 class="manage-roles">Manage Roles</h3>
<table id="profile-roles">
	<thead>
		<tr>
			<th />
			<th>Name <span class="instructions">(click to edit in place)</span></th>
			<th>Description <span class="instructions">(click to edit in place)</span></th>
			<th />
		</tr>
	</thead>
	<tbody>
	<?php $i=0; ?>
	<?php foreach ( $profile_roles as $role ): ?>
		<tr class="profile-role" id="role_<?php echo( $role->id ); ?>" tabindex="<?php echo( $i ) ?>" >
			<td valign="top" class="preview"><a href="<?php echo( $admin_options_tab->generate_profile_url( $role->id ) ); ?>"><?php echo( $guthrie->preview_button() ); ?></a></td>
			<td valign="top"><div tabindex="1" id="name_<?php echo( $role->id ); ?>" name="name_<?php echo( $role->id ); ?>" class="cell-content-wrapper profile-role-name"><?php echo( $role->name ); ?></div></td>
			<td valign="top"><div tabindex="1" id="description_<?php echo( $role->name ); ?>" name="description_<?php echo( $role->id ); ?>" class="cell-content-wrapper profile-role-description"><?php echo( $role->description ); ?></div></td>
			<td valign="top" class="delete"><?php echo( $guthrie->delete_button( 'delete-profile-role_' . $role->id ) ); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<form action="<?php the_permalink(); ?>" id="roleAddForm" method="post">
	<h3 class="add-a-field">Add a Role</h3>
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
					<?php if( ! ('' == $admin_options_tab->name_error) ): ?>
						<div class="error"><?php echo( $admin_options_tab->name_error ); ?></div>
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
					<?php if ( ! ( '' == $admin_options_tab->description_error ) ) : ?>
						<div class="error"><?php echo( $admin_options_tab->description_error ); ?></div>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit">
		<button class="button-primary" id="field-instance-add-button" type="submit">Add Field</button>
	</p>
</form>
