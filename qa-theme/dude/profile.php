<?php
	/* don't allow this page to be requested directly from browser */	
	if (!defined('QA_VERSION')) {
			header('Location: /');
			exit;
	}
	
	/* user profile page */
	
	
	$handle = $this->content['raw']['account']['handle'];
	$user = ra_user_data($handle);
	$p = $this->content['form_profile']['fields'];
	
	foreach($p as $k =>$c){
		if ($k == 'website' || $k == 'location' || $k == 'email' || $k == 'name')
			$this->content['ra_profile'][$k] = $c;
	}	
	foreach($p as $k =>$c){
		if ($k == 'duration' || $k == 'lastlogin' || $k == 'lastwrite')
			$this->content['ra_visit'][$k] = $c;
	}

?>
<div class="profile-fields clearfix">
	<div class="profile-avatar pull-left">
		<?php echo $p['avatar']['html']; ?>
		<h3><?php echo $this->content['raw']['account']['handle'].'<span>'.qa_html(qa_user_level_string($this->content['raw']['account']['level'])); ?></span></h3>
		<div class="points icon-radio-checked"><?php echo $user[0]['points']; ?></div>
		<div class="badges"><?php echo ra_user_badge($handle); ?></div>
		
	</div>
	<div class="user-info">
		<div class="profile-buttons">
			<?php 
				if ( qa_opt('allow_private_messages') && (qa_get_logged_in_userid()!= $this->content['raw']['account']['userid']) &&
				!($this->content['raw']['account']['flags'] & QA_USER_FLAGS_NO_MESSAGES) )
					echo '<a class="btn btn-mini icon-envelope" href="'.qa_path_html('message/'.$handle).'">Send message</a>';
				
				if (qa_get_logged_in_userid()== $this->content['raw']['account']['userid'])		
					echo '<a class="btn btn-mini edit-profile icon-edit" href="'.qa_path_html('account').'">Edit</a>';
			?>
		</div>
		<div class="row">
			<div class="info-fields col-span-8">
				<h5>Bio</h5>
				<table>
					<?php foreach($this->content['ra_profile'] as $k => $profile): ?>
						<tr class="<?php echo $k; ?>">
							<td><?php echo @$profile['label']; ?></td>
							<td><?php echo @$profile['value']; ?></td>
						</tr>					
					<?php endforeach; ?>
				</table>	

				<h5>Visit</h5>
				<table>
					<?php foreach($this->content['ra_visit'] as $k => $visit): ?>
						<tr class="<?php echo $k; ?>">
							<td><?php echo @$visit['label']; ?></td>
							<td><?php echo @$visit['value']; ?></td>
						</tr>					
					<?php endforeach; ?>
				</table>
				
				<?php if(isset($p['permits'])): ?>
					<h5>Privileges</h5>	
					<div class="privileges">
						<div class="initial-height">
							<?php echo $p['permits']['value']; ?>
						</div>					
					</div>
					<a class="expand-priv" href="#">All Privilege</a>
				<?php endif; ?>
			</div>
			<div class="about-me col-span-4">
				<h5>About</h5>
				<?php echo $p['about']['value']; ?>
			</div>	
		</div>
	</div>
</div>

