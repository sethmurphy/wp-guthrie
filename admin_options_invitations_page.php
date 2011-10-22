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
Manage our invitations
*/

global $guthrie;
global $admin_options;

$profile_roles = $guthrie->get_profile_roles();
$profile_invitations = $guthrie->get_profile_invitations();
$admin_options_tab = $admin_options->admin_options_tab;
?>
<table id="profile-invitations">
	<thead>
		<tr>
			<th />
			<th>Email</th>
			<th>Name <span class="instructions">(click to edit in place)</span></th>
			<th>Description <span class="instructions">(click to edit in place)</span></th>
			<th>Relationships</th>
			<th />
		</tr>
	</thead>
	<tbody>
	<?php $i=0; ?>
	<?php foreach ($profile_invitations as $invitation): ?>
		<tr class="profile-invitation" id="invitation_<?php echo($invitation->id); ?>" tabindex="<?php echo( $i ) ?>" >
			<td valign="top" class="preview"><a href="<?php echo( $admin_options_tab->generate_profile_url( $invitation->guid ) ); ?>"><?php echo( $guthrie->preview_button() ); ?></a></td>
			<td valign="top"><div id="email_<?php echo($invitation->id); ?>" name="email_<?php echo($invitation->id); ?>" class="cell-content-wrapper profile-invitation-email"><?php echo($invitation->email); ?></div></td>
			<td valign="top"><div tabindex="1" id="name_<?php echo($invitation->id); ?>" name="name_<?php echo($invitation->id); ?>" class="cell-content-wrapper profile-invitation-name"><?php echo($invitation->name); ?></div></td>
			<td valign="top"><div tabindex="1" id="description_<?php echo($invitation->id); ?>" name="description_<?php echo($invitation->id); ?>" class="cell-content-wrapper profile-invitation-description"><?php echo($invitation->description); ?></div></td>
			<td>
				<select multiple class="invitation-roles chzn-select roles-select" id="roles_<?php echo($invitation->id) ?>_role" data-placeholder="Choose a role...">
					<option value=""></option>
					<?php $chosen_roles = explode ( ',' , $invitation->roles ); ?>

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
			<td valign="top" class="delete"><?php echo( $guthrie->delete_button('delete-profile-invitation_' . $invitation->id) ); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<h3 class="send-an-invite">Send an Invitation</h3>
<form action="<?php the_permalink(); ?>" id="profileInviteForm" method="post">
	<input type="hidden" name="profile-invite-submitted" id="profile-invite-submitted" value="true" />
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-email">Email</label>
				</th>
				<td>
					<input type="text" name="profile-invite-email" id="profile-invite-email" value="<?php echo( $admin_options_tab->email ); ?>"  class="regular-text" />
					<span class="description">required</span>
					<?php if ( ! ('' == $admin_options_tab->email_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->email_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-name">Name </label>
				</th>
				<td>
					<input type="text" name="profile-invite-name" id="profile-invite-name" value="<?php echo( $admin_options_tab->name ); ?>"  class="regular-text" />
					<?php if ( ! ('' == $admin_options_tab->name_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->name_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-description">Description </label>
				</th>
				<td>
					<input type="text" name="profile-invite-description" id="profile-invite-description" value="<?php echo( $admin_options_tab->description ); ?>"  class="regular-text" />
					<?php if ( ! ('' == $admin_options_tab->description_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->description_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-field-roles">Relationships </label>
				</th>
				<td>
					<select multiple class="profile-invite-roles chzn-select roles-select" name="profile-invite-roles" id="profile-invite-roles" data-placeholder="Choose a role...">
						<option value=""></option>

						<?php foreach ($profile_roles as $role): ?>
						<option value="<?php echo($role->id); ?>"><?php echo($role->name) ?></option>
						<?php endforeach; ?>
					</select>
		

					<?php if ( ! ('' == $admin_options_tab->roles_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->roles_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
		<tbody>
	</table>
	<p class="submit">
		<button class="button-primary" id="field-instance-add-button" type="submit">Send Invitation</button>
	</p>
</form>
