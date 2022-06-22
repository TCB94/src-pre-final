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


$action = isset($_GET['action']) ? $_GET['action'] : null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($id < 1 && $pid < 1)
	message($lang_common['Bad request']);

// Load the viewtopic.php language file
require PUN_ROOT.'lang/'.$pun_user['language'].'/topic.php';


// If a post ID is specified we determine topic ID and page number so we can redirect to the correct message
if ($pid)
{
	$result = $db->query('SELECT topic_id FROM '.$db->prefix.'posts WHERE id='.$pid) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	if (!$db->num_rows($result))
		message($lang_common['Bad request']);

	$id = $db->result($result);

	// Determine on what page the post is located (depending on $pun_user['disp_posts'])
	$result = $db->query('SELECT id FROM '.$db->prefix.'posts WHERE topic_id='.$id.' ORDER BY posted') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$num_posts = $db->num_rows($result);

	for ($i = 0; $i < $num_posts; ++$i)
	{
		$cur_id = $db->result($result, $i);
		if ($cur_id == $pid)
			break;
	}
	++$i;	// we started at 0

	$_GET['p'] = ceil($i / $pun_user['disp_posts']);
}

// If action=new, we redirect to the first new post (if any)
else if ($action == 'new' && !$pun_user['is_guest'])
{
	$result = $db->query('SELECT MIN(id) FROM '.$db->prefix.'posts WHERE topic_id='.$id.' AND posted>'.$pun_user['last_visit']) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$first_new_post_id = $db->result($result);

	if ($first_new_post_id)
		header('Location: viewtopic.php?pid='.$first_new_post_id.'#p'.$first_new_post_id);
	else	// If there is no new post, we go to the last post
		header('Location: viewtopic.php?id='.$id.'&action=last');

	exit;
}

// If action=last, we redirect to the last post
else if ($action == 'last')
{
	$result = $db->query('SELECT MAX(id) FROM '.$db->prefix.'posts WHERE topic_id='.$id) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
	$last_post_id = $db->result($result);

	if ($last_post_id)
	{
		header('Location: viewtopic.php?pid='.$last_post_id.'#p'.$last_post_id);
		exit;
	}
}


// Fetch some info about the topic
if (!$pun_user['is_guest'])
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, s.user_id AS is_subscribed FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'subscriptions AS s ON (t.id=s.topic_id AND s.user_id='.$pun_user['id'].') LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());
else
	$result = $db->query('SELECT t.subject, t.closed, t.num_replies, t.sticky, f.id AS forum_id, f.forum_name, f.moderators, fp.post_replies, 0 FROM '.$db->prefix.'topics AS t INNER JOIN '.$db->prefix.'forums AS f ON f.id=t.forum_id LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id='.$pun_user['g_id'].') WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.id='.$id.' AND t.moved_to IS NULL') or error('Unable to fetch topic info', __FILE__, __LINE__, $db->error());

if (!$db->num_rows($result))
	message($lang_common['Bad request']);

$cur_topic = $db->fetch_assoc($result);

// Sort out who the moderators are and if we are currently a moderator (or an admin)
$mods_array = ($cur_topic['moderators'] != '') ? unserialize($cur_topic['moderators']) : array();
$is_admmod = ($pun_user['g_id'] == PUN_ADMIN || ($pun_user['g_id'] == PUN_MOD && array_key_exists($pun_user['username'], $mods_array))) ? true : false;

// Can we or can we not post replies?
if ($cur_topic['closed'] == '0')
{
	if (($cur_topic['post_replies'] == '' && $pun_user['g_post_replies'] == '1') || $cur_topic['post_replies'] == '1' || $is_admmod)
		$post_link = '<a href="post.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a>';
	else
		$post_link = '&nbsp;';
}
else
{
	$post_link = $lang_topic['Topic closed'];

	if ($is_admmod)
		$post_link .= ' / <a href="post.php?tid='.$id.'">'.$lang_topic['Post reply'].'</a>';
}


// Determine the post offset (based on $_GET['p'])
$num_pages = ceil(($cur_topic['num_replies'] + 1) / $pun_user['disp_posts']);

$p = (!isset($_GET['p']) || $_GET['p'] <= 1 || $_GET['p'] > $num_pages) ? 1 : $_GET['p'];
$start_from = $pun_user['disp_posts'] * ($p - 1);

// Generate paging links
$paging_links = $lang_common['Pages'].': '.paginate($num_pages, $p, 'viewtopic.php?id='.$id);


if ($pun_config['o_censoring'] == '1')
	$cur_topic['subject'] = censor_words($cur_topic['subject']);


$quickpost = false;
if ($pun_config['o_quickpost'] == '1' &&
	!$pun_user['is_guest'] &&
	($cur_topic['post_replies'] == '1' || ($cur_topic['post_replies'] == '' && $pun_user['g_post_replies'] == '1')) &&
	($cur_topic['closed'] == '0' || $is_admmod))
{
	$required_fields = array('req_message' => $lang_common['Message']);
	$quickpost = true;
}

if (!$pun_user['is_guest'] && $pun_config['o_subscriptions'] == '1')
{
	if ($cur_topic['is_subscribed'])
		// I apologize for the variable naming here. It's a mix of subscription and action I guess :-)
		$subscraction = '<p class="subscribelink clearb">'.$lang_topic['Is subscribed'].' - <a href="misc.php?unsubscribe='.$id.'">'.$lang_topic['Unsubscribe'].'</a></p>'."\n";
	else
		$subscraction = '<p class="subscribelink clearb"><a href="misc.php?subscribe='.$id.'">'.$lang_topic['Subscribe'].'</a></p>'."\n";
}
else
	$subscraction = '<div class="clearer"></div>'."\n";

$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' / '.$cur_topic['subject']);
define('PUN_ALLOW_INDEX', 1);
require PUN_ROOT.'header.php';

?>
<div class="linkst">
	<div class="inbox">
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<p class="postlink conr"><?php echo $post_link ?></p>
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<a href="viewforum.php?id=<?php echo $cur_topic['forum_id'] ?>"><?php echo pun_htmlspecialchars($cur_topic['forum_name']) ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo pun_htmlspecialchars($cur_topic['subject']) ?></li></ul>
		<div class="clearer"></div>
	</div>
</div>

<?php


require PUN_ROOT.'include/parser.php';

$bg_switch = true;	// Used for switching background color in posts
$post_count = 0;	// Keep track of post numbers

// Retrieve the posts (and their respective poster/online status)
$result = $db->query('SELECT u.email, u.title, u.url, u.location, u.use_avatar, u.signature, u.email_setting, u.num_posts, u.registered, u.admin_note, p.id, p.poster AS username, p.poster_id, p.poster_ip, p.poster_email, p.message, p.hide_smilies, p.posted, p.edited, p.edited_by, g.g_id, g.g_user_title, o.user_id AS is_online FROM '.$db->prefix.'posts AS p INNER JOIN '.$db->prefix.'users AS u ON u.id=p.poster_id INNER JOIN '.$db->prefix.'groups AS g ON g.g_id=u.group_id LEFT JOIN '.$db->prefix.'online AS o ON (o.user_id=u.id AND o.user_id!=1 AND o.idle=0) WHERE p.topic_id='.$id.' ORDER BY p.id LIMIT '.$start_from.','.$pun_user['disp_posts'], true) or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());
while ($cur_post = $db->fetch_assoc($result))
{
	$post_count++;
	$user_avatar = '';
	$user_info = array();
	$user_contacts = array();
	$post_actions = array();
	$is_online = '';
	$signature = '';

	// If the poster is a registered user.
	if ($cur_post['poster_id'] > 1)
	{
		//$username = '<a href="profile.php?id='.$cur_post['poster_id'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>';
		$username = '<a href="/users/'.$cur_post['username'].'">'.pun_htmlspecialchars($cur_post['username']).'</a>';
		$user_title = get_title($cur_post);

		if ($pun_config['o_censoring'] == '1')
			$user_title = censor_words($user_title);

		// Format the online indicator
		$is_online = ($cur_post['is_online'] == $cur_post['poster_id']) ? '<strong>'.$lang_topic['Online'].'</strong>' : $lang_topic['Offline'];

		if ($pun_config['o_avatars'] == '1' && $cur_post['use_avatar'] == '1' && $pun_user['show_avatars'] != '0')
		{
			if ($img_size = @getimagesize($pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif'))
				$user_avatar = '<img src="'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.gif" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg'))
				$user_avatar = '<img src="'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.jpg" '.$img_size[3].' alt="" />';
			else if ($img_size = @getimagesize($pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png'))
				$user_avatar = '<img src="'.$pun_config['o_avatars_dir'].'/'.$cur_post['poster_id'].'.png" '.$img_size[3].' alt="" />';
		}
		else
			$user_avatar = '';

		// We only show location, register date, post count and the contact links if "Show user info" is enabled
		if ($pun_config['o_show_user_info'] == '1')
		{

			/*if ($cur_post['location'] != '')
			{
				if ($pun_config['o_censoring'] == '1')
					$cur_post['location'] = censor_words($cur_post['location']);

				$user_info[] = '<dd>'.$lang_topic['From'].': '.pun_htmlspecialchars($cur_post['location']);
			}*/

			$user_info[] = '<dd>'.$lang_common['Registered'].': '.date($pun_config['o_date_format'], $cur_post['registered']);

			if ($pun_config['o_show_post_count'] == '1' || $pun_user['g_id'] < PUN_GUEST)
				$user_info[] = '<dd><a href="search.php?action=show_user&user_id='.$cur_post['poster_id'].'">'.$lang_common['Posts'].'</a>: '.$cur_post['num_posts'];

			// Now let's deal with the contact links (E-mail and URL)
			/*if (($cur_post['email_setting'] == '0' && !$pun_user['is_guest']) || $pun_user['g_id'] < PUN_GUEST)
				$user_contacts[] = '<a href="mailto:'.$cur_post['email'].'">'.$lang_common['E-mail'].'</a>';
			else if ($cur_post['email_setting'] == '1' && !$pun_user['is_guest'])
				$user_contacts[] = '<a href="misc.php?email='.$cur_post['poster_id'].'">'.$lang_common['E-mail'].'</a>';

			if ($cur_post['url'] != '')
				$user_contacts[] = '<a href="'.pun_htmlspecialchars($cur_post['url']).'">'.$lang_topic['Website'].'</a>';*/
		}

		if ($pun_user['g_id'] < PUN_GUEST)
		{
			$user_info[] = '<dd>IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['poster_ip'].'</a>';

			if ($cur_post['admin_note'] != '')
				$user_info[] = '<dd>'.$lang_topic['Note'].': <strong>'.pun_htmlspecialchars($cur_post['admin_note']).'</strong>';
		}
	}
	// If the poster is a guest (or a user that has been deleted)
	else
	{
		$username = pun_htmlspecialchars($cur_post['username']);
		$user_title = get_title($cur_post);

		if ($pun_user['g_id'] < PUN_GUEST)
			$user_info[] = '<dd>IP: <a href="moderate.php?get_host='.$cur_post['id'].'">'.$cur_post['poster_ip'].'</a>';

		if ($pun_config['o_show_user_info'] == '1' && $cur_post['poster_email'] != '' && !$pun_user['is_guest'])
			$user_contacts[] = '<a href="mailto:'.$cur_post['poster_email'].'">'.$lang_common['E-mail'].'</a>';
	}

	// Generation post action array (quote, edit, delete etc.)
	if (!$is_admmod)
	{
		if (!$pun_user['is_guest'])
			$post_actions[] = '<li class="postreport"><a href="misc.php?report='.$cur_post['id'].'">'.$lang_topic['Report'].'</a>';

		if ($cur_topic['closed'] == '0')
		{
			if ($cur_post['poster_id'] == $pun_user['id'])
			{
				if ((($start_from + $post_count) == 1 && $pun_user['g_delete_topics'] == '1') || (($start_from + $post_count) > 1 && $pun_user['g_delete_posts'] == '1'))
					$post_actions[] = '<li class="postdelete"><a href="delete.php?id='.$cur_post['id'].'">'.$lang_topic['Delete'].'</a>';
				if ($pun_user['g_edit_posts'] == '1')
					$post_actions[] = '<li class="postedit"><a href="edit.php?id='.$cur_post['id'].'">'.$lang_topic['Edit'].'</a>';
			}

			if (($cur_topic['post_replies'] == '' && $pun_user['g_post_replies'] == '1') || $cur_topic['post_replies'] == '1')
				$post_actions[] = '<li class="postquote"><a href="post.php?tid='.$id.'&amp;qid='.$cur_post['id'].'">'.$lang_topic['Quote'].'</a>';
		}
	}
	else
		$post_actions[] = '<li class="postreport"><a href="misc.php?report='.$cur_post['id'].'">'.$lang_topic['Report'].'</a>'.$lang_topic['Link separator'].'</li><li class="postdelete"><a href="delete.php?id='.$cur_post['id'].'">'.$lang_topic['Delete'].'</a>'.$lang_topic['Link separator'].'</li><li class="postedit"><a href="edit.php?id='.$cur_post['id'].'">'.$lang_topic['Edit'].'</a>'.$lang_topic['Link separator'].'</li><li class="postquote"><a href="post.php?tid='.$id.'&amp;qid='.$cur_post['id'].'">'.$lang_topic['Quote'].'</a>';


	// Switch the background color for every message.
	$bg_switch = ($bg_switch) ? $bg_switch = false : $bg_switch = true;
	$vtbg = ($bg_switch) ? ' roweven' : ' rowodd';


	// Perform the main parsing of the message (BBCode, smilies, censor words etc)
	$cur_post['message'] = parse_message($cur_post['message'], $cur_post['hide_smilies']);

	// Do signature parsing/caching
	if ($cur_post['signature'] != '' && $pun_user['show_sig'] != '0')
	{
		if (isset($signature_cache[$cur_post['poster_id']]))
			$signature = $signature_cache[$cur_post['poster_id']];
		else
		{
			$signature = parse_signature($cur_post['signature']);
			$signature_cache[$cur_post['poster_id']] = $signature;
		}
	}

?>
<div id="p<?php echo $cur_post['id'] ?>" class="blockpost<?php echo $vtbg ?><?php if (($post_count + $start_from) == 1) echo ' firstpost'; ?>">
	<h2><span><span class="conr">#<?php echo ($start_from + $post_count) ?>&nbsp;</span><a href="viewtopic.php?pid=<?php echo $cur_post['id'].'#p'.$cur_post['id'] ?>"><?php echo format_time($cur_post['posted']) ?></a></span></h2>
	<div class="box">
		<div class="inbox">
			<div class="postleft">
				<dl>
					<dt><strong><?php echo $username ?></strong></dt>
					<dd class="usertitle"><strong><?php echo $user_title ?></strong></dd>
					<dd class="postavatar"><?php echo $user_avatar ?></dd>
<?php if (count($user_info)) echo "\t\t\t\t\t".implode('</dd>'."\n\t\t\t\t\t", $user_info).'</dd>'."\n"; ?>
<?php if (count($user_contacts)) echo "\t\t\t\t\t".'<dd class="usercontacts">'.implode('&nbsp;&nbsp;', $user_contacts).'</dd>'."\n"; ?>
				</dl>
			</div>
			<div class="postright">
				<h3><?php if (($post_count + $start_from) > 1) echo ' Re: '; ?><?php echo pun_htmlspecialchars($cur_topic['subject']) ?></h3>
				<div class="postmsg">
					<?php echo $cur_post['message']."\n" ?>
<?php if ($cur_post['edited'] != '') echo "\t\t\t\t\t".'<p class="postedit"><em>'.$lang_topic['Last edit'].' '.pun_htmlspecialchars($cur_post['edited_by']).' ('.format_time($cur_post['edited']).')</em></p>'."\n"; ?>
				</div>
<?php if ($signature != '') echo "\t\t\t\t".'<div class="postsignature"><hr />'.$signature.'</div>'."\n"; ?>
			</div>
			<div class="clearer"></div>
			<div class="postfootleft"><?php if ($cur_post['poster_id'] > 1) echo '<p>'.$is_online.'</p>'; ?></div>
			<div class="postfootright"><?php echo (count($post_actions)) ? '<ul>'.implode($lang_topic['Link separator'].'</li>', $post_actions).'</li></ul></div>'."\n" : '<div>&nbsp;</div></div>'."\n" ?>
		</div>
	</div>
</div>

<?php

}

?>
<div class="postlinksb">
	<div class="inbox">
		<p class="postlink conr"><?php echo $post_link ?></p>
		<p class="pagelink conl"><?php echo $paging_links ?></p>
		<ul><li><a href="index.php"><?php echo $lang_common['Index'] ?></a></li><li>&nbsp;&raquo;&nbsp;<a href="viewforum.php?id=<?php echo $cur_topic['forum_id'] ?>"><?php echo pun_htmlspecialchars($cur_topic['forum_name']) ?></a></li><li>&nbsp;&raquo;&nbsp;<?php echo pun_htmlspecialchars($cur_topic['subject']) ?></li></ul>
		<?php echo $subscraction ?>
	</div>
</div>

<?php

// Display quick post if enabled
if ($quickpost)
{

?>
<div class="blockform">
	<h2><span><?php echo $lang_topic['Quick post'] ?></span></h2>
	<div class="box">
		<form method="post" action="post.php?tid=<?php echo $id ?>" onsubmit="this.submit.disabled=true;if(process_form(this)){return true;}else{this.submit.disabled=false;return false;}">
			<div class="inform">
				<fieldset>
					<legend><?php echo $lang_common['Write message legend'] ?></legend>
					<div class="infldset txtarea">
						<input type="hidden" name="form_sent" value="1" />
						<input type="hidden" name="form_user" value="<?php echo (!$pun_user['is_guest']) ? pun_htmlspecialchars($pun_user['username']) : 'Guest'; ?>" />
						<label><textarea name="req_message" rows="7" cols="75" tabindex="1"></textarea></label>
						<ul class="bblinks">
							<li><a href="help.php#bbcode" onclick="window.open(this.href); return false;"><?php echo $lang_common['BBCode'] ?></a>: <?php echo ($pun_config['p_message_bbcode'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							<li><a href="help.php#img" onclick="window.open(this.href); return false;"><?php echo $lang_common['img tag'] ?></a>: <?php echo ($pun_config['p_message_img_tag'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
							<li><a href="help.php#smilies" onclick="window.open(this.href); return false;"><?php echo $lang_common['Smilies'] ?></a>: <?php echo ($pun_config['o_smilies'] == '1') ? $lang_common['on'] : $lang_common['off']; ?></li>
						</ul>
					</div>
<b>Adding Scratch Blocks:</b>

<br>To add Scratch Blocks to your post, type <b>[blocks]</b> before you start and <b>[/blocks]</b> at the end of the blocks.
<br>Click a category below, then click the block you want to add. Then fill in the blank spaces in the text box above.
<br>
<br>

<a href="#req_message"><img onload="divsdisappear()" src="img/sbcats/motion.png" onClick="motion(); LoadMotion();" alt="motion"></a>
<a href="#req_message"><img src="img/sbcats/looks.png" onClick="looks(); LoadLooks();" alt="looks"></a>
<a href="#req_message"><img src="img/sbcats/sound.png" onClick="sound(); LoadSound();" alt="sound"></a>
<a href="#req_message"><img src="img/sbcats/pen.png" onClick="pen(); LoadPen();" alt="pen"></a>
<a href="#req_message"><img src="img/sbcats/control.png" onClick="control(); LoadControl();" alt="control"></a>
<a href="#req_message"><img src="img/sbcats/sensing.png" onClick="sensing(); LoadSensing();" alt="sensing"></a>
<a href="#req_message"><img src="img/sbcats/numbers.png" onClick="numbers(); LoadNumbers();" alt="numbers"></a>
<a href="#req_message"><img src="img/sbcats/variables.png" onClick="variables(); LoadVariables();" alt="variables"></a>

<br>
<div id="motion" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img border=0 name="move_steps" alt="move_steps" onclick="insertAtCursor(document.post.req_message, '<move(  )steps>');" /></a> 
  <a href="#req_message"><img border=0 name="turncw_degrees" alt="turn clockwise" onclick="insertAtCursor(document.post.req_message, '<turn cw(  )degrees>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="turnccw_degrees" alt="turn counterclockwise" onclick="insertAtCursor(document.post.req_message, '<turn cw(  )degrees>');" /></a> 
  <a href="#req_message"><img border=0 name="pointindirection_" alt="point in direction" onclick="insertAtCursor(document.post.req_message, '<point in direction( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="pointtowards_" alt="point towards" onclick="insertAtCursor(document.post.req_message, '<point towards( ');" /></a> 
  <a href="#req_message"><img border=0 name="gotox_y_" alt="go to x: y:" onclick="insertAtCursor(document.post.req_message, '<go to x:(  )y:( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="goto" alt="go to" onclick="insertAtCursor(document.post.req_message, '<go to[ ');" /></a> 
  <a href="#req_message"><img border=0 name="glide_secsto_x_y_" alt="glide" onclick="insertAtCursor(document.post.req_message, '<glide(  )secs to x:(  )y:( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="changexby_" alt="change x by" onclick="insertAtCursor(document.post.req_message, '<change x by( ');" /></a> 
  <a href="#req_message"><img border=0 name="setxto_" alt="set x to" onclick="insertAtCursor(document.post.req_message, '<set x to( ');" /></a> 
<br> 
  <a href="#req_message"><img border=0 name="changeyby_" alt="change y by" onclick="insertAtCursor(document.post.req_message, '<change y by( ');" /></a> 
  <a href="#req_message"><img border=0 name="setyto_" alt="set y to" onclick="insertAtCursor(document.post.req_message, '<set y to( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="ifonedgebounce" alt="if on edge bounce" onclick="insertAtCursor(document.post.req_message, '<if on edge, bounce>');" /></a> 
  <a href="#req_message"><img border=0 name="xposition" alt="x position" onclick="insertAtCursor(document.post.req_message, '<x position>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="yposition" alt="y position" onclick="insertAtCursor(document.post.req_message, '<y position>');" /></a> 
  <a href="#req_message"><img border=0 name="direction" alt="direction" onclick="insertAtCursor(document.post.req_message, '<direction>');" /></a> 
<layer></layer></div> 

<div id="looks" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img border=0 name="switchtocostume_" alt="switch to costume" onclick="insertAtCursor(document.post.req_message, '<switch to costume[ ');" /></a> 
  <a href="#req_message"><img border=0 name="nextcostume" alt="next costume" onclick="insertAtCursor(document.post.req_message, '<next costume>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="say_for_secs" alt="say_for_secs" onclick="insertAtCursor(document.post.req_message, '<say[  ]for(  )secs>');" /></a>   
  <a href="#req_message"><img border=0 name="say_" alt="say" onclick="insertAtCursor(document.post.req_message, '<say[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="think_for_secs" alt="think_for_secs" onclick="insertAtCursor(document.post.req_message, '<think[  ]for(  )secs>');" /></a> 
  <a href="#req_message"><img border=0 name="think_" alt="think" onclick="insertAtCursor(document.post.req_message, '<think[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="change_effectby_" alt="change_effectby_" onclick="insertAtCursor(document.post.req_message, '<change[  ]effect by( ');" /></a> 
  <a href="#req_message"><img border=0 name="set_effectto_" alt="seteffectto_" onclick="insertAtCursor(document.post.req_message, '<set[  ]effect to( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="cleargraphiceffects" alt="clear graphic effects" onclick="insertAtCursor(document.post.req_message, '<clear graphic effects>');" /></a> 
  <a href="#req_message"><img border=0 name="changesizeby_" alt="change size by" onclick="insertAtCursor(document.post.req_message, '<change size by( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="setsizeto_" alt="set size to" onclick="insertAtCursor(document.post.req_message, '<set size to(  )%>');" /></a> 
  <a href="#req_message"><img border=0 name="size" alt="size" onclick="insertAtCursor(document.post.req_message, '<size>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="show" alt="show" onclick="insertAtCursor(document.post.req_message, '<show>');" /></a> 
  <a href="#req_message"><img border=0 name="hide" alt="hide" onclick="insertAtCursor(document.post.req_message, '<hide>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="gotofront" alt="go to front" onclick="insertAtCursor(document.post.req_message, '<go to front>');" /></a> 
  <a href="#req_message"><img border=0 name="goback_layers" alt="go back" onclick="insertAtCursor(document.post.req_message, '<go back(  )layers>');" /></a> 

<layer></layer></div> 

<div id="sound" style="overflow: auto; width: 400px; height: 100px;">
<br>
   <a href="#req_message"><img border=0 name="playsound_" alt="play sound" onclick="insertAtCursor(document.post.req_message, '<play sound[ ');" /></a> 
  <a href="#req_message"><img border=0 name="playsound_andwait" alt="play sound and wait" onclick="insertAtCursor(document.post.req_message, '<play sound[  ]and waits>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="stopallsounds" alt="stop all sounds" onclick="insertAtCursor(document.post.req_message, '<stop all sounds>');" /></a> 
  <a href="#req_message"><img border=0 name="playdrum_for_secs" alt="play drum_for_secs" onclick="insertAtCursor(document.post.req_message, '<play drum(  )for(  )secss>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="playnote_for_secs" alt="play note_for_secs" onclick="insertAtCursor(document.post.req_message, '<play note(  )for(  )secss>');" /></a> 
  <a href="#req_message"><img border=0 name="setinstrumentto_" alt="set instrument to" onclick="insertAtCursor(document.post.req_message, '<set instrument to( ');" /></a> 

<layer></layer></div> 

<div id="pen" style="overflow: auto; width: 400px; height: 100px;">
<br>
<a href="#req_message"><img border=0 name="clear" alt="clear" onclick="insertAtCursor(document.post.req_message, '<clear>');" /></a> 
  <a href="#req_message"><img border=0 name="pendown" alt="pen down" onclick="insertAtCursor(document.post.req_message, '<pen down>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="penup" alt="pen up" onclick="insertAtCursor(document.post.req_message, '<pen up>');" /></a> 
  <a href="#req_message"><img border=0 name="changepencolorby_" alt="change pen color by" onclick="insertAtCursor(document.post.req_message, '<change pen color by( ');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="setpencolorto_" alt="set pen color to" onclick="insertAtCursor(document.post.req_message, '<set pen color to( ');" /></a> 
  <a href="#req_message"><img border=0 name="changepenshadeby_" alt="change pen shade by" onclick="insertAtCursor(document.post.req_message, '<change pen shade by( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="setpenshadeto_" alt="set pen shade to" onclick="insertAtCursor(document.post.req_message, '<set pen shade to( ');" /></a> 
  <a href="#req_message"><img border=0 name="changepensizeby_" alt="change pen size by" onclick="insertAtCursor(document.post.req_message, '<change pen size by( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="setpensizeto_" alt="set pen size to" onclick="insertAtCursor(document.post.req_message, '<set pen size to( ');" /></a> 
  <a href="#req_message"><img border=0 name="stamp" alt="stamp" onclick="insertAtCursor(document.post.req_message, '<stamp>');" /></a>

 
<layer></layer></div> 

<div id="control" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img border=0 name="whengreenflagclicked" alt="when green flag clicked" onclick="insertAtCursor(document.post.req_message, '<when green flag clicked>');" /></a> 
  <a href="#req_message"><img border=0 name="when_keypressed" alt="when_keypressed" onclick="insertAtCursor(document.post.req_message, '<when[  ]key pressed>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="when_clicked" alt="when_clicked" onclick="insertAtCursor(document.post.req_message, '<when[  ]clicked>');" /></a> 
  <a href="#req_message"><img border=0 name="wait_secs" alt="wait_secs" onclick="insertAtCursor(document.post.req_message, '<wait(  )secsc>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="forever" alt="forever" onclick="insertAtCursor(document.post.req_message, '<forever>');" /></a> 
  <a href="#req_message"><img border=0 name="repeat_" alt="repeat" onclick="insertAtCursor(document.post.req_message, '<repeat( ');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="broadcast_" alt="broadcast" onclick="insertAtCursor(document.post.req_message, '<broadcast[ ');" /></a> 
  <a href="#req_message"><img border=0 name="broadcast_andwait" alt="broadcast and wait" onclick="insertAtCursor(document.post.req_message, '<broadcast[  ]and wait c>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="whenireceive_" alt="when I receive" onclick="insertAtCursor(document.post.req_message, '<when I receive[ ');" /></a> 
  <a href="#req_message"><img border=0 name="foreverif_" alt="forever if" onclick="insertAtCursor(document.post.req_message, '<forever if>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="if_" alt="if" onclick="insertAtCursor(document.post.req_message, '<if>');" /></a> 
  <a href="#req_message"><img border=0 name="endcontrol" alt="end" onclick="insertAtCursor(document.post.req_message, '<end>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="else" alt="else" onclick="insertAtCursor(document.post.req_message, '<else>');" /></a> 
  <a href="#req_message"><img border=0 name="waituntil" alt="wait until" onclick="insertAtCursor(document.post.req_message, '<wait until>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="repeatuntil" alt="repeat until" onclick="insertAtCursor(document.post.req_message, '<repeat until>');" /></a> 
  <a href="#req_message"><img border=0 name="stopscript" alt="stop script" onclick="insertAtCursor(document.post.req_message, '<stop script>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="stopall" alt="stop all" onclick="insertAtCursor(document.post.req_message, '<stop all>');" /></a> 

 
<layer></layer></div> 

<div id="sensing" style="overflow: auto; width: 400px; height: 100px;">
 <br>
  <a href="#req_message"><img border=0 name="mousex" alt="mouse x" onclick="insertAtCursor(document.post.req_message, '<mouse x>');" /></a> 
  <a href="#req_message"><img border=0 name="mousey" alt="mouse y" onclick="insertAtCursor(document.post.req_message, '<mouse y>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="mousedownq" alt="mouse down" onclick="insertAtCursor(document.post.req_message, '<mouse down?>');" /></a> 
  <a href="#req_message"><img border=0 name="key_pressedq" alt="key pressed" onclick="insertAtCursor(document.post.req_message, '<key[  ]pressed?>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="touching_q" alt="touching_?" onclick="insertAtCursor(document.post.req_message, '<touching[ ');" /></a> 
  <a href="#req_message"><img border=0 name="touchingcolor_q" alt="touching color" onclick="insertAtCursor(document.post.req_message, '<touching color[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="color_isover_q" alt="color is over" onclick="insertAtCursor(document.post.req_message, '<color[  ]is over[ ');" /></a> 
  <a href="#req_message"><img border=0 name="distanceto_" alt="distance to" onclick="insertAtCursor(document.post.req_message, '<distance to[ ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="resettimer" alt="reset timer" onclick="insertAtCursor(document.post.req_message, '<reset timer>');" /></a> 
  <a href="#req_message"><img border=0 name="timer" alt="timer" onclick="insertAtCursor(document.post.req_message, '<timer>');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="loudness" alt="loudness" onclick="insertAtCursor(document.post.req_message, '<loudness>');" /></a> 
  <a href="#req_message"><img border=0 name="loudq" alt="loud?" onclick="insertAtCursor(document.post.req_message, '<loud?>');" /></a>  
 
<layer></layer></div> 

<div id="numbers" style="overflow: auto; width: 400px; height: 100px;">
<br>
  <a href="#req_message"><img border=0 name="_plus_" alt="+" onclick="insertAtCursor(document.post.req_message, '((  <+>  ))');" /></a> 
  <a href="#req_message"><img border=0 name="_minus_" alt="-" onclick="insertAtCursor(document.post.req_message, '((  <->  ))');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="_times_" alt="*" onclick="insertAtCursor(document.post.req_message, '((  <*>  ))');" /></a> 
  <a href="#req_message"><img border=0 name="_dividedby_" alt="/" onclick="insertAtCursor(document.post.req_message, '((  </>  ))');" /></a> 
<br>  
  <a href="#req_message"><img border=0 name="pickrandom_to_" alt="pick random_to_" onclick="insertAtCursor(document.post.req_message, '<pick random(  )to( ');" /></a> 
  <a href="#req_message"><img border=0 name="_lessthan_" alt="less than" onclick="insertAtCursor(document.post.req_message, '<(  <<>  )>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="_equals_" alt="equals" onclick="insertAtCursor(document.post.req_message, '<(  <=>  )>');" /></a> 
  <a href="#req_message"><img border=0 name="_greaterthan_" alt="greater than" onclick="insertAtCursor(document.post.req_message, '<(  <>>  )>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="_and_" alt="and" onclick="insertAtCursor(document.post.req_message, '<<  <and>  >>');" /></a> 
  <a href="#req_message"><img border=0 name="_or_" alt="or" onclick="insertAtCursor(document.post.req_message, '<<  <or>  >>');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="not_" alt="not" onclick="insertAtCursor(document.post.req_message, '<<  <not>  >>');" /></a> 
  <a href="#req_message"><img border=0 name="_mod_" alt="mod" onclick="insertAtCursor(document.post.req_message, '((  <mod>  ))');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="abs_" alt="abs" onclick="insertAtCursor(document.post.req_message, '<abs( ');" /></a> 
  <a href="#req_message"><img border=0 name="round_" alt="round" onclick="insertAtCursor(document.post.req_message, '<round( ');" /></a> 
 
<layer></layer></div> 

<div id="variables" style="overflow: auto; width: 400px; height: 100px;">
<br>
<a href="#req_message"><img border=0 name="change_by_" alt="change_by_" onclick="insertAtCursor(document.post.req_message, '<change{  }by( ');" /></a> 
  <a href="#req_message"><img border=0 name="set_to_" alt="set_to_" onclick="insertAtCursor(document.post.req_message, '<set{  }to( ');" /></a> 
<br>
  <a href="#req_message"><img border=0 name="_var" alt="{" onclick="insertAtCursor(document.post.req_message, '<{  }>');" /></a> 
 
 
<layer></layer></div>

				</fieldset>
			</div>
			<p><input type="submit" name="submit" tabindex="2" value="<?php echo $lang_common['Submit'] ?>" accesskey="s" /></p>
		</form>
	</div>
</div>
<?php

}

// Increment "num_views" for topic
$low_prio = ($db_type == 'mysql') ? 'LOW_PRIORITY ' : '';
$db->query('UPDATE '.$low_prio.$db->prefix.'topics SET num_views=num_views+1 WHERE id='.$id) or error('Unable to update topic', __FILE__, __LINE__, $db->error());

$forum_id = $cur_topic['forum_id'];
$footer_style = 'viewtopic';
require PUN_ROOT.'footer.php';
