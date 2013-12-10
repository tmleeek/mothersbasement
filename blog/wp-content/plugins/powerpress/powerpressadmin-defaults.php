<?php


function powerpressadmin_default_steps($FeedSettings, $General, $Step = 0)
{
?>
<div id="powerpress_steps">
	<div class="powerpress-step active-step" id="powerpreess_step_1">
	<h3><?php echo __('Step 1', 'powerpress'); ?></h3>
	<p>
	<?php echo __('Fill out the settings on this page', 'powerpress'); ?>
	</p>
	<?php powerpressadmin_complete_check($Step >= 1); ?>
	</div>
	<div class="powerpress-step<?php echo ($Step >= 1? ' active-step':''); ?>">
	<h3><?php echo __('Step 2', 'powerpress'); ?></h3>
	<p>
	<a href="http://create.blubrry.com/resources/powerpress/using-powerpress/creating-your-first-episode-with-powerpress/" target="_blank"><?php echo __('Create a blog post with an episode', 'powerpress'); ?></a>
	</p>
	<p><a href="http://create.blubrry.com/resources/" target="_blank"><?php echo __('Need Help?', 'powerpress'); ?></a>
	</p>
	<?php powerpressadmin_complete_check($Step >= 2); ?>
	</div>
	<div class="powerpress-step<?php echo ($Step >= 2? ' active-step':''); ?>">
	<h3><?php echo __('Step 3', 'powerpress'); ?></h3>
	<p>
	<a href="http://create.blubrry.com/manual/podcast-promotion/submit-podcast-to-itunes/?podcast-feed=<?php echo urlencode(get_feed_link('podcast')); ?>" target="_blank"><?php echo __('Submit your feed to iTunes and other podcast directories', 'powerpress'); ?></a>
	</p>
	<?php powerpressadmin_complete_check($Step == 3); ?>
	</div>
	<div class="clear"></div>
</div>
<?php
	
}

function powerpressadmin_complete_check($checked=false)
{
?>
<div class="powerpress-step-complete<?php echo ($checked?' powerpress-step-completed':''); ?>">
	<p>complete
	<span class="powerpress-step-complete-box">&nbsp;</span>
	</p>
</div>
<?php
}

function powerpress_admin_defaults()
{
	$FeedAttribs = array('type'=>'general', 'feed_slug'=>'', 'category_id'=>0, 'term_taxonomy_id'=>0, 'term_id'=>0, 'taxonomy_type'=>'', 'post_type'=>'');
	
	$General = powerpress_get_settings('powerpress_general');
	$General = powerpress_default_settings($General, 'basic');
	
	$FeedSettings = powerpress_get_settings('powerpress_feed');
	$FeedSettings = powerpress_default_settings($FeedSettings, 'editfeed');
	
	$Step = 0;
	if( !empty($FeedSettings['itunes_cat_1']) && !empty($FeedSettings['email']) && !empty($FeedSettings['itunes_image']) )
		$Step = 1;
	
	$episode_total = 0;
	if( $Step == 1 )
	{
		$episode_total = powerpress_admin_episodes_per_feed('podcast');
		if( $episode_total > 0 )
			$Step = 2;
	}

	if( $Step == 2 && !empty($FeedSettings['itunes_url']) )
		$Step = 3;
	
?>
<script type="text/javascript"><!--


jQuery(document).ready(function($) {
	jQuery('#powerpress_advanced_mode_button').click( function(event) {
		event.preventDefault();
		jQuery('#powerpress_advanced_mode').val('1');
		jQuery(this).closest("form").submit();
	} );
} );
//-->
</script>
<input type="hidden" name="action" value="powerpress-save-defaults" />
<input type="hidden" id="powerpress_advanced_mode" name="General[advanced_mode_2]" value="0" />

<div id="powerpress_admin_header">
<h2><?php echo __('Blubrry PowerPress Settings', 'powerpress'); ?></h2> 
<span class="powerpress-mode"><?php echo __('Default Mode', 'powerpress'); ?>
	&nbsp; <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_basic.php&mode=advanced"); ?>" id="powerpress_advanced_mode_button" class="button-primary"><?php echo __('Switch to Advanced Mode', 'powerpress'); ?></a>
</span>
</div>

<?php

	powerpressadmin_default_steps($FeedSettings, $General, $Step);
	
	powerpressadmin_edit_blubrry_services($General);
?>
<h3><?php echo __('Podcast Settings', 'powerpress'); ?></h3>
<table class="form-table">
<tr valign="top">
<th scope="row">
<?php echo __('Program Title', 'powerpress'); ?>
</th>
<td>
<input type="text" name="Feed[title]"style="width: 60%;"  value="<?php echo $FeedSettings['title']; ?>" maxlength="250" />
(<?php echo __('leave blank to use blog title', 'powerpress'); ?>)
<p><?php echo __('Blog title:', 'powerpress') .' '. get_bloginfo_rss('name'); ?></p>
</td>
</tr>
</table>
<?php
	if( $Step > 1 ) // Only display if we have episdoes in the feed!
		powerpressadmin_edit_itunes_general($FeedSettings, $General, $FeedAttribs);
	// iTunes settings (in simple mode of course)
	powerpressadmin_edit_itunes_feed($FeedSettings, $General, $FeedAttribs);
	
	powerpressadmin_edit_artwork($FeedSettings, $General);
	powerpressadmin_appearance($General);
	powerpressadmin_advanced_options($General);
}


?>