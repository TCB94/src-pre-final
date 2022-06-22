<?php
define('PUN_ROOT', dirname(__FILE__) ."/");
require PUN_ROOT.'include/common.php';
//This is called by a cron_job once per day, and updates New Scratchers / TBGers that qualify to full privileges.

echo "Starting...";

$query_time = $db->query('SELECT conf_value FROM '.$db->prefix.'config WHERE conf_name = "o_next_update"');
$time_to_update = $db->fetch_assoc($query_time);

// only will be run the first time to set the variable.
if ($time_to_update==false){

	$db->query('INSERT INTO '.$db->prefix.'config (conf_name, conf_value) VALUES ("o_next_update", "'.strtotime('yesterday').'")');
	$time_to_update = $db->fetch_assoc($db->query('SELECT conf_value FROM '.$db->prefix.'config WHERE conf_name = "o_next_update"'));
}

if (time() > $time_to_update[conf_value]){
	$db->query('UPDATE '.$db->prefix.'config SET conf_value = '.strtotime('tomorrow').' WHERE conf_name = "o_next_update"');
	$thirty_days_ago = time() - 86400*30;
	$two_days_ago = time() - 86400*2;
	
	// Sets the group ID that New Scratchers / TBGers who qualify will be promoted to.
	// If this is the main forums... set to 'Scratcher' group, group 5.	
	if( $pun_config['o_base_url'] == 'http://scratch.mit.edu/forums')
		$should_be_group_id = 5;		

	// If this is the tbgforums ... set to 'TBGer' group, group 4.
	else if( $pun_config['o_base_url'] == 
'http://scratch.mit.edu/tbgforums'){
		$should_be_group_id = 4;
	}	
	// Don't see the right base url value? Die!
	else{
		echo "Uh oh, I cant recognize the forum by the base url. I'm dieing....";
		die();
	}
		

	// Get all New Scratchers / TBG accounts (as defined by membership in: $pun_config['o_default_user_group']) older than 30 days.
	$new_scratchr_ids = $db->query('SELECT username FROM 
'.$db->prefix.'users WHERE group_id 
='.$pun_config['o_default_user_group'].' and registered < ' . 
$thirty_days_ago.' and last_visit > ' . $two_days_ago);

	$new_scratchr = true;
	$num_accts_promoted = 0;
	$num_accts_checked = 0;

	while ($new_scratchr){
		$new_scratchr = $db->fetch_assoc($new_scratchr_ids);
		if (!$new_scratchr) break;
		$new_username = $new_scratchr[username];
		$num_accts_checked++;

		// Get num projects	
		$cr = curl_init('http://scratch.mit.edu/api/getnumprojectsbyuser/'.$new_username);
		curl_setopt($cr,CURLOPT_RETURNTRANSFER, true);
		$num_projects = curl_exec($cr);
		curl_close($cr);
		$num_projects = (int)trim($num_projects);
		if (gettype($num_projects)!= 'integer') {
			return;
		}

		if ($num_projects > 2){
			// Get num comments
			$cr = curl_init('http://scratch.mit.edu/api/getnumcommentsbyuser/'.$new_username);
			curl_setopt($cr,CURLOPT_RETURNTRANSFER, true);
			$num_comments = curl_exec($cr);
			curl_close($cr);
			$num_comments = (int)trim($num_comments);
			if (gettype($num_comments)!= 'integer') {
				return;
			}

			// Criteria for promotion is > 2 projects, > 10 comments (and acct. > 30 days old)
			if ( $num_comments > 10){
				$db->query('UPDATE '.$db->prefix.'users SET group_id = '.$should_be_group_id.' WHERE username = "'.$new_username.'"') or error('Unable to update user group', __FILE__, __LINE__, $db->error());
				$num_accts_promoted++;
			}
		}
			
	}

	echo "update complete. $num_accts_promoted accounts 
out of $num_accts_checked checked were promoted.";		

}
else
	echo "Update already done today." ;



?>
