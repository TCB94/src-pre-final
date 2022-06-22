<?php

if (!defined('PUN')) exit;
define('PUN_QJ_LOADED', 1);

?>				<form id="qjump" method="get" action="viewforum.php">
					<div><label><?php echo $lang_common['Jump to'] ?>

					<br /><select name="id" onchange="window.location=('viewforum.php?id='+this.options[this.selectedIndex].value)">
						<optgroup label="Welcome">
							<option value="46"<?php echo ($forum_id == 46) ? ' selected="selected"' : '' ?>>Announcements</option>
							<option value="47"<?php echo ($forum_id == 47) ? ' selected="selected"' : '' ?>>New Scratchers</option>
						</optgroup>
						<optgroup label="Making Scratch Projects">
							<option value="52"<?php echo ($forum_id == 52) ? ' selected="selected"' : '' ?>>Help with Scripts</option>
							<option value="53"<?php echo ($forum_id == 53) ? ' selected="selected"' : '' ?>>Show and tell</option>
							<option value="48"<?php echo ($forum_id == 48) ? ' selected="selected"' : '' ?>>Project Ideas</option>
							<option value="51"<?php echo ($forum_id == 51) ? ' selected="selected"' : '' ?>>Collaboration</option>
							<option value="50"<?php echo ($forum_id == 50) ? ' selected="selected"' : '' ?>>Requests</option>
					</optgroup>
					</select>
					<input type="submit" value="<?php echo $lang_common['Go'] ?>" accesskey="g" />
					</label></div>
				</form>
