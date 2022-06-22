<?php
/***********************************************************************

  Copyright (C) 2002-2005  Rickard Andersson (rickard@punbb.org)

  This file is part of PunBB.

  PunBB is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  PunBB is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
  MA  02111-1307  USA

************************************************************************/


define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';


if ($pun_user['g_read_board'] == '0')
	message($lang_common['No view']);


$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id < 1)
	message($lang_common['Bad request']);

// Fetch some info about the post, the topic and the forum
$result = $db->query('SELECT f.id AS fid, f.forum_name, f.moderators, f.redirect_url, fp.post_replies, fp.post_topics, t.id AS tid, t.subject, t.posted, t.closed, p.poster, p.poster_id, p.message, p.hide_smilies FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'topics AS t ON t.id=p.topic_id INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND p.id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
if (!$db->num_rows($result))
	message($lang_common['Bad request']);

$cur_post = $db->fetch_assoc($result);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_post['moderators'] != '') ? unserialize($cur_post['moderators']) : array();
$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Determine whether this post is the "topic post" or not
$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$cur_post['tid'].' ORDER BY posted LIMIT 1') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
$topic_post_id = $db->result($result);

$can_edit_subject = ($id == $topic_post_id && (($pun_user['g_edit_subjects_interval'] == '0' || (time() - $cur_post['posted']) < $pun_user['g_edit_subjects_interval']) || $is_admmod)) ? true : false;

// Do we have permission to edit this post?
if (($pun_user['g_edit_posts'] == '0' ||
	$cur_post['poster_id'] != $pun_user['id'] ||
	$cur_post['closed'] == '1') &&
	!$is_admmod)
	message($lang_common['No permission']);

// Load the post.php/edit.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/post.php';

// Start with a clean slate
$errors = array();


if (isset($_POST['form_sent']))
{
	if ($is_admmod)
		confirm_referrer('edit.php');

	// If it is a topic it must contain a subject
	if ($can_edit_subject)
	{
		$subject = pun_trim($_POST['req_subject']);

		if ($subject == '')
			$errors[] = $lang_post['No subject'];
		else if (pun_strlen($subject) > 70)
			$errors[] = $lang_post['Too long subject'];
		else if ($pun_config['p_subject_all_caps'] == '0' && strtoupper($subject) == $subject && $pun_user['g_id'] > PUN_MOD)
			$subject = ucwords(strtolower($subject));
	}

	// Clean up message from POST
	$message = pun_linebreaks(pun_trim($_POST['req_message']));

	if ($message == '')
		$errors[] = $lang_post['No message'];
	else if (strlen($message) > 65535)
		$errors[] = $lang_post['Too long message'];
	else if ($pun_config['p_message_all_caps'] == '0' && strtoupper($message) == $message && $pun_user['g_id'] > PUN_MOD)
		$message = ucwords(strtolower($message));

	// Validate BBCode syntax
	if ($pun_config['p_message_bbcode'] == '1' && strpos($message, '[') !== false && strpos($message, ']') !== false)
	{
		require PUN_ROOT.'include/parser.php';
		$message = preparse_bbcode($message, $errors);
	}


	$hide_smilies = isset($_POST['hide_smilies']) ? intval($_POST['hide_smilies']) : 0;
	if ($hide_smilies != '1') $hide_smilies = '0';

	// Did everything go according to plan?
	if (empty($errors) && !isset($_POST['preview']))
	{
		$edited_sql = (!isset($_POST['silent']) || !$is_admmod) ? $edited_sql = ', edited='.time().', edited_by=\''.$db->escape($pun_user['username']).'\'' : '';

		require PUN_ROOT.'include/search_idx.php';

		if ($can_edit_subject)
		{
			// Update the topic and any redirect topics
			$db->query('UPDATE '.$db->prefix.'topics SET subject=\''.$db->escape($subject).'\' WHERE id='.$cur_post['tid'].' OR moved_to='.$cur_post['tid']) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

			// We changed the subject, so we need to take that into account when we update the search words
			update_search_index('edit', $id, $message, $subject);
		}
		else
			update_search_index('edit', $id, $message);

		// Update the post
		$db->query('UPDATE '.$db->prefix.'posts SET message=\''.$db->escape($message).'\', hide_smilies=\''.$hide_smilies.'\''.$edited_sql.' WHERE id='.$id) or error('Unable to update post', __FILE__, __LINE__, $db->error());

		redirect('viewtopic.php?pid='.$id.'#p'.$id, $lang_post['Edit redirect']);
	}
}



$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_post['Edit post'];
$required_fields = array('req_subject' => $lang_common['Subject'], 'req_message' => $lang_common['Message']);
$focus_element = array('edit', 'req_message');
require PUN_ROOT.'header.php';

$cur_index = 1;

?>
<div class="linkst">
	<div class="inbox">
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<a href="viewforum.php?id=<?php echo $cur_post['fid'] ?>"><?php echo pun_htmlspecialchars($cur_post['forum_name']) ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo pun_htmlspecialchars($cur_post['subject']) ?></li></ul>
	</div>
</div>

<?php

// If there are errors, we display them
if (!empty($errors))
{

?>
<div id="posterror" class="block">
	<h2><span><?php echo $lang_post['Post errors'] ?></span></h2>
	<div class="box">
		<div class="inbox"
			<p><?php echo $lang_post['Post errors info'] ?></p>
			<ul>
<?php

	while (list(, $cur_error) = each($errors))
		echo "\t\t\t\t".'<li><strong>'.$cur_error.'</strong></li>'."\n";
?>
			</ul>
		</div>
	</div>
</div>

<?php

}
else if (isset($_POST['preview']))
{
	require_once PUN_ROOT.'include/parser.php';
	$preview_message = parse_message($message, $hide_smilies);

?>
<div id="postpreview" class="blockpost">
	<h2><span><?php echo $lang_post['Post preview'] ?></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postright">
				<div class="postmsg">
					<?php echo $preview_message."\n" ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php

}

?>
<div class="blockform">
	<h2><?php echo $lang_post['Edit post'] ?></h2>
	<div class="box">
		<form id="edit" name="post" method="post" action="edit.php?id=<?php echo $id ?>&amp;action=edit" onsubmit="return process_form(this)">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_post['Edit post legend'] ?></legend>
					<input type="hidden" name="form_sent" value="1" />
					<div class="infldset txtarea">
<?php if ($can_edit_subject): ?>						<label><?php echo $lang_common['Subject'] ?><br />
						<input class="longinput" type="text" name="req_subject" size="80" maxlength="70" tabindex="<?php echo $cur_index++ ?>" value="<?php echo pun_htmlspecialchars(isset($_POST['req_subject']) ? $_POST['req_subject'] : $cur_post['subject']) ?>" /><br /></label>
<?php endif; ?>						<label><?php echo $lang_common['Message'] ?><br />
						<textarea name="req_message" rows="20" cols="95" tabindex="<?php echo $cur_index++ ?>"><?php echo pun_htmlspecialchars(isset($_POST['req_message']) ? $message : $cur_post['message']) ?></textarea><br /></label>
						<ul class="bblinks">
							<li><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a>: <?php echo ($pun_config['p_message_bbcode'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							<li><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a>: <?php echo ($pun_config['p_message_img_tag'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							<li><a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies'] ?></a>: <?php echo ($pun_config['o_smilies'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							
						</ul>
					</div>

<br>To add Scratch Blocks to your post, type [blocks] before you start and [/blocks] at the end of the blocks.
<br>Click a category below, then click the block you want to add. Then fill in the blank spaces in the text box above.
<br>
<br>
<form>
<input type=button onClick="motion()" value="Motion">
<input type=button onClick="looks()" value="Looks">
<input type=button onClick="sound()" value="Sound">
<input type=button onClick="pen()" value="Pen">
<input type=button onClick="control()" value="Control">
<input type=button onClick="sensing()" value="Sensing">
<input type=button onClick="numbers()" value="Numbers">
<input type=button onClick="variables()" value="Variables">
</form>
<br>
<div id="motion" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img onload="divsdisappear()" border=0 src="img/sb_no_nums/move_steps.png" alt="move_steps" onclick="insertAtCursor(document.post.req_message, '<move(  )steps>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/turncw_degrees.png" alt="turn clockwise" onclick="insertAtCursor(document.post.req_message, '<turn cw(  )degrees>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/turnccw_degrees.png" alt="turn counterclockwise" onclick="insertAtCursor(document.post.req_message, '<turn cw(  )degrees>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/pointindirection_.png" alt="point in direction" onclick="insertAtCursor(document.post.req_message, '<point in direction( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/pointtowards_.png" alt="point towards" onclick="insertAtCursor(document.post.req_message, '<point towards( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/gotox_y_.png" alt="go to x: y:" onclick="insertAtCursor(document.post.req_message, '<go to x:(  )y:( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/goto.png" alt="go to" onclick="insertAtCursor(document.post.req_message, '<go to[ ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/glide_secsto_x_y_.png" alt="glide" onclick="insertAtCursor(document.post.req_message, '<glide(  )secs to x:(  )y:( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/changexby_.png" alt="change x by" onclick="insertAtCursor(document.post.req_message, '<change x by( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/setxto_.png" alt="set x to" onclick="insertAtCursor(document.post.req_message, '<set x to( ');" /></a> 
<br> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/changeyby_.png" alt="change y by" onclick="insertAtCursor(document.post.req_message, '<change y by( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/setyto_.png" alt="set y to" onclick="insertAtCursor(document.post.req_message, '<set y to( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/ifonedgebounce.png" alt="if on edge bounce" onclick="insertAtCursor(document.post.req_message, '<if on edge, bounce>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/xposition.png" alt="x position" onclick="insertAtCursor(document.post.req_message, '<x position>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/yposition.png" alt="y position" onclick="insertAtCursor(document.post.req_message, '<y position>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/direction.png" alt="direction" onclick="insertAtCursor(document.post.req_message, '<direction>');" /></a> 
<layer></layer></div> 

<div id="looks" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/switchtocostume_.png" alt="switch to costume" onclick="insertAtCursor(document.post.req_message, '<switch to costume[ ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/nextcostume.png" alt="next costume" onclick="insertAtCursor(document.post.req_message, '<next costume>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/say_for_secs.png" alt="say_for_secs" onclick="insertAtCursor(document.post.req_message, '<say[  ]for(  )secs>');" /></a>   
  <a href="#req_message"><img border=0 src="img/sb_no_nums/say_.png" alt="say" onclick="insertAtCursor(document.post.req_message, '<say[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/think_for_secs.png" alt="think_for_secs" onclick="insertAtCursor(document.post.req_message, '<think[  ]for(  )secs>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/think_.png" alt="think" onclick="insertAtCursor(document.post.req_message, '<think[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/change_effectby_.png" alt="change_effectby_" onclick="insertAtCursor(document.post.req_message, '<change[  ]effect by( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/set_effectto_.png" alt="seteffectto_" onclick="insertAtCursor(document.post.req_message, '<set[  ]effect to( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/cleargraphiceffects.png" alt="clear graphic effects" onclick="insertAtCursor(document.post.req_message, '<clear graphic effects>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/changesizeby_.png" alt="change size by" onclick="insertAtCursor(document.post.req_message, '<change size by( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/setsizeto_.png" alt="set size to" onclick="insertAtCursor(document.post.req_message, '<set size to(  )%>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/size.png" alt="size" onclick="insertAtCursor(document.post.req_message, '<size>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/show.png" alt="show" onclick="insertAtCursor(document.post.req_message, '<show>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/hide.png" alt="hide" onclick="insertAtCursor(document.post.req_message, '<hide>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/gotofront.png" alt="go to front" onclick="insertAtCursor(document.post.req_message, '<go to front>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/goback_layers.png" alt="go back" onclick="insertAtCursor(document.post.req_message, '<go back(  )layers>');" /></a> 

<layer></layer></div> 

<div id="sound" style="overflow: auto; width: 400px; height: 100px;">
<br>
   <a href="#req_message"><img border=0 src="img/sb_no_nums/playsound_.png" alt="play sound" onclick="insertAtCursor(document.post.req_message, '<play sound[ ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/playsound_andwait.png" alt="play sound and wait" onclick="insertAtCursor(document.post.req_message, '<play sound[  ]and waits>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/stopallsounds.png" alt="stop all sounds" onclick="insertAtCursor(document.post.req_message, '<stop all sounds>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/playdrum_for_secs.png" alt="play drum_for_secs" onclick="insertAtCursor(document.post.req_message, '<play drum(  )for(  )secss>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/playnote_for_secs.png" alt="play note_for_secs" onclick="insertAtCursor(document.post.req_message, '<play note(  )for(  )secss>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/setinstrumentto_.png" alt="set instrument to" onclick="insertAtCursor(document.post.req_message, '<set instrument to( ');" /></a> 

<layer></layer></div> 

<div id="pen" style="overflow: auto; width: 400px; height: 100px;">
<br>
<a href="#req_message"><img border=0 src="img/sb_no_nums/clear.png" alt="clear" onclick="insertAtCursor(document.post.req_message, '<clear>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/pendown.png" alt="pen down" onclick="insertAtCursor(document.post.req_message, '<pen down>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/penup.png" alt="pen up" onclick="insertAtCursor(document.post.req_message, '<pen up>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/changepencolorby_.png" alt="change pen color by" onclick="insertAtCursor(document.post.req_message, '<change pen color by( ');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/setpencolorto_.png" alt="set pen color to" onclick="insertAtCursor(document.post.req_message, '<set pen color to( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/changepenshadeby_.png" alt="change pen shade by" onclick="insertAtCursor(document.post.req_message, '<change pen shade by( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/setpenshadeto_.png" alt="set pen shade to" onclick="insertAtCursor(document.post.req_message, '<set pen shade to( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/changepensizeby_.png" alt="change pen size by" onclick="insertAtCursor(document.post.req_message, '<change pen size by( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/setpensizeto_.png" alt="set pen size to" onclick="insertAtCursor(document.post.req_message, '<set pen size to( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/stamp.png" alt="stamp" onclick="insertAtCursor(document.post.req_message, '<stamp>');" /></a>

 
<layer></layer></div> 

<div id="control" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/whengreenflagclicked.png" alt="when green flag clicked" onclick="insertAtCursor(document.post.req_message, '<when green flag clicked>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/when_keypressed.png" alt="when_keypressed" onclick="insertAtCursor(document.post.req_message, '<when[  ]key pressed>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/when_clicked.png" alt="when_clicked" onclick="insertAtCursor(document.post.req_message, '<when[  ]clicked>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/wait_secs.png" alt="wait_secs" onclick="insertAtCursor(document.post.req_message, '<wait(  )secsc>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/forever.png" alt="forever" onclick="insertAtCursor(document.post.req_message, '<forever>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/repeat_.png" alt="repeat" onclick="insertAtCursor(document.post.req_message, '<repeat( ');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/broadcast_.png" alt="broadcast" onclick="insertAtCursor(document.post.req_message, '<broadcast[ ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/broadcast_andwait.png" alt="broadcast and wait" onclick="insertAtCursor(document.post.req_message, '<broadcast[  ]and wait c>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/whenireceive_.png" alt="when I receive" onclick="insertAtCursor(document.post.req_message, '<when I receive[ ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/foreverif_.png" alt="forever if" onclick="insertAtCursor(document.post.req_message, '<forever if>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/if_.png" alt="if" onclick="insertAtCursor(document.post.req_message, '<if>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/endcontrol.png" alt="end" onclick="insertAtCursor(document.post.req_message, '<end>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/else.png" alt="else" onclick="insertAtCursor(document.post.req_message, '<else>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/waituntil.png" alt="wait until" onclick="insertAtCursor(document.post.req_message, '<wait until>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/repeatuntil.png" alt="repeat until" onclick="insertAtCursor(document.post.req_message, '<repeat until>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/stopscript.png" alt="stop script" onclick="insertAtCursor(document.post.req_message, '<stop script>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/stopall.png" alt="stop all" onclick="insertAtCursor(document.post.req_message, '<stop all>');" /></a> 

 
<layer></layer></div> 

<div id="sensing" style="overflow: auto; width: 400px; height: 100px;">
 <br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/mousex.png" alt="mouse x" onclick="insertAtCursor(document.post.req_message, '<mouse x>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/mousey.png" alt="mouse y" onclick="insertAtCursor(document.post.req_message, '<mouse y>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/mousedownq.png" alt="mouse down" onclick="insertAtCursor(document.post.req_message, '<mouse down?>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/key_pressedq.png" alt="key pressed" onclick="insertAtCursor(document.post.req_message, '<key[  ]pressed?>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/touching_q.png" alt="touching_?" onclick="insertAtCursor(document.post.req_message, '<touching[ ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/touchingcolor_q.png" alt="touching color" onclick="insertAtCursor(document.post.req_message, '<touching color[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/color_isover_q.png" alt="color is over" onclick="insertAtCursor(document.post.req_message, '<color[  ]is over[ ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/distanceto_.png" alt="distance to" onclick="insertAtCursor(document.post.req_message, '<distance to[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/resettimer.png" alt="reset timer" onclick="insertAtCursor(document.post.req_message, '<reset timer>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/timer.png" alt="timer" onclick="insertAtCursor(document.post.req_message, '<timer>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/loudness.png" alt="loudness" onclick="insertAtCursor(document.post.req_message, '<loudness>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/loudq.png" alt="loud?" onclick="insertAtCursor(document.post.req_message, '<loud?>');" /></a>  
 
<layer></layer></div> 

<div id="numbers" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_plus_.png" alt="+" onclick="insertAtCursor(document.post.req_message, '((  <+>  ))');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_minus_.png" alt="-" onclick="insertAtCursor(document.post.req_message, '((  <->  ))');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_times_.png" alt="*" onclick="insertAtCursor(document.post.req_message, '((  <*>  ))');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_dividedby_.png" alt="/" onclick="insertAtCursor(document.post.req_message, '((  </>  ))');" /></a> 
<br>  
  <a href="#req_message"><img border=0 src="img/sb_no_nums/pickrandom_to_.png" alt="pick random_to_" onclick="insertAtCursor(document.post.req_message, '<pick random(  )to( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_lessthan_.png" alt="less than" onclick="insertAtCursor(document.post.req_message, '<(  <<>  )>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_equals_.png" alt="equals" onclick="insertAtCursor(document.post.req_message, '<(  <=>  )>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_greaterthan_.png" alt="greater than" onclick="insertAtCursor(document.post.req_message, '<(  <>>  )>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_and_.png" alt="and" onclick="insertAtCursor(document.post.req_message, '<<  <and>  >>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_or_.png" alt="or" onclick="insertAtCursor(document.post.req_message, '<<  <or>  >>');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/not_.png" alt="not" onclick="insertAtCursor(document.post.req_message, '<<  <not>  >>');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_mod_.png" alt="mod" onclick="insertAtCursor(document.post.req_message, '((  <mod>  ))');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/abs_.png" alt="abs" onclick="insertAtCursor(document.post.req_message, '<abs( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/round_.png" alt="round" onclick="insertAtCursor(document.post.req_message, '<round( ');" /></a> 
 
<layer></layer></div> 

<div id="variables" style="overflow: auto; width: 400px; height: 100px;">
<br>
<a href="#req_message"><img border=0 src="img/sb_no_nums/change_by_.png" alt="change_by_" onclick="insertAtCursor(document.post.req_message, '<change{  }by( ');" /></a> 
  <a href="#req_message"><img border=0 src="img/sb_no_nums/set_to_.png" alt="set_to_" onclick="insertAtCursor(document.post.req_message, '<set{  }to( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 src="img/sb_no_nums/_var.png" alt="{" onclick="insertAtCursor(document.post.req_message, '<{  }>');" /></a> 
 
 
<layer></layer></div>				

</fieldset>
<?php

$checkboxes = array();
if ($pun_config['o_smilies'] == '1')
{
	if (isset($_POST['hide_smilies']) || $cur_post['hide_smilies'] == '1')
		$checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" checked="checked" tabindex="'.($cur_index++).'" />&nbsp;'.$lang_post['Hide smilies'];
	else
		$checkboxes[] = '<label><input type="checkbox" name="hide_smilies" value="1" tabindex="'.($cur_index++).'" />&nbsp;'.$lang_post['Hide smilies'];
}

if ($is_admmod)
{
	if ((isset($_POST['form_sent']) && isset($_POST['silent'])) || !isset($_POST['form_sent']))
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" checked="checked" />&nbsp;'.$lang_post['Silent edit'];
	else
		$checkboxes[] = '<label><input type="checkbox" name="silent" value="1" tabindex="'.($cur_index++).'" />&nbsp;'.$lang_post['Silent edit'];
}

if (!empty($checkboxes))
{

?>
			</div>
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_common['Options'] ?></legend>
					<div class="infldset">
						<div class="rbox">
							<?php echo implode('</label>'."\n\t\t\t\t\t\t\t", $checkboxes).'</label>'."\n" ?>
						</div>
					</div>
				</fieldset>
<?php

	}

?>
			</div>
			<p><input type="submit" name="submit" value="<?php echo $lang_common['Submit'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="s" /><input type="submit" name="preview" value="<?php echo $lang_post['Preview'] ?>" tabindex="<?php echo $cur_index++ ?>" accesskey="p" /><a href="javascript:history.go(-1)"><?php echo $lang_common['Go back'] ?></a></p>
		</form>
	</div>
</div>
<?php

require PUN_ROOT.'footer.php';
