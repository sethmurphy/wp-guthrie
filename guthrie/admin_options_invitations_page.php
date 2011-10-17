<?php
/*
Manage our invitations
*/
?>
<?php 
global $guthrie;
global $admin_options;

$profile_roles = $guthrie->get_profile_field_roles();
$admin_options_tab = $admin_options->admin_options_tab;
?>
<h3 class="send-an-invite">Send an Invitation</h3>
<form action="<?php the_permalink(); ?>" id="profileInviteForm" method="post">
	<input type="hidden" name="submitted" id="submitted" value="true" />
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-email">Email</label>
				</th>
				<td>
					<input type="text" name="profile-invite-email" id="profile-invite-email" value="<?php echo( $admin_options_tab->profile_invite_email ); ?>"  class="regular-text" />
					<span class="description">required</span>
					<?php if ( ! ('' == $admin_options_tab->profile_invite_email_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->profile_invite_email_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-name">Name </label>
				</th>
				<td>
					<input type="text" name="profile-invite-name" id="profile-invite-name" value="<?php echo( $admin_options_tab->profile_invite_name ); ?>"  class="regular-text" />
					<?php if ( ! ('' == $admin_options_tab->profile_invite_name_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->profile_invite_name_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-description">Description </label>
				</th>
				<td>
					<input type="text" name="profile-invite-description" id="profile-invite-description" value="<?php echo( $admin_options_tab->profile_invite_description ); ?>"  class="regular-text" />
					<?php if ( ! ('' == $admin_options_tab->profile_invite_description_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->profile_invite_description_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
			<tr valign="top">
				<th class="row">
					<label for="profile-invite-field-roles">Relationships </label>
				</th>
				<td>
					<select multiple class="profile-invite-field-roles chzn-select roles-select" name="profile-invite-field-roles" id="profile-invite-field-roles" data-placeholder="Choose a role...">
						<option value=""></option>

						<?php foreach ($profile_roles as $role): ?>
						<option value="<?php echo($role->id); ?>"><?php echo($role->name) ?></option>
						<?php endforeach; ?>
					</select>
		

					<?php if ( ! ('' == $admin_options_tab->profile_invite_field_roles_error) ) : ?>
						<div class="error"><?php echo($admin_options_tab->profile_invite_field_roles_error); ?></div>
					<?php endif; ?>
				</td>
			</tr>
		<tbody>
	</table>
	<p class="submit">
		<button class="button-primary" id="field-instance-add-button" type="submit">Send Invitation</button>
	</p>
</form>

