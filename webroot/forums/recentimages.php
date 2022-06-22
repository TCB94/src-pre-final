<?php
/***********************************************************************

  experimental page to view a flow of recent images posted to the forums.
  -- JSO
  
************************************************************************/


define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';

$ajax = isset($_GET['ajax']) ? true : false;

//keep unauthorized users out of here
if (!($pun_user['g_id'] == PUN_ADMIN))
	message($lang_common['No permission']);

//get latest posts with images
$result = $db->query('SELECT id, message, posted FROM '.$db->prefix.'posts WHERE (posted > (UNIX_TIMESTAMP()-86400)) AND message LIKE "%[img]%" ORDER BY id DESC') or error('Unable to fetch post info', __FILE__, __LINE__, $db->error());

$o = '';

while ($post = $db->fetch_assoc($result)) {
	$offset = 0;
	while (strpos($post['message'], '[img]', $offset)!==false && $i<100) {
		$offset = strpos($post['message'], '[img]', $offset) + 5;
		$end = strpos($post['message'], '[/img]', $offset);
		if ($end) {
			$imgurl = substr($post['message'], $offset, strpos($post['message'], '[/img]', $offset) - $offset);
			if (preg_match('#((ht|f)tps?://)([^\s<"]*?)#e', $imgurl)) {
				$o .= '<a href="viewtopic.php?pid=' . $post['id'] . '#p' . $post['id'] . '" target="_BLANK"><img src="' . $imgurl . '" style="width: 120px; margin: 3px 1px;"></img></a>';
			}
		}
	}
}

$page_title = pun_htmlspecialchars($pun_config['o_board_title'].' / Recent Images Flow');
define('PUN_ALLOW_INDEX', 1);
if (!$ajax) {
	require PUN_ROOT.'header.php';
?>
<script type="text/javascript">
var httpRequest; 
var baseurl = '<?php echo $pun_config['o_base_url'] ?>';
if (window.XMLHttpRequest) { // Mozilla, Safari, ...  
	httpRequest = new XMLHttpRequest();  
} else if (window.ActiveXObject) { // IE 8 and older  
	httpRequest = new ActiveXObject("Microsoft.XMLHTTP");  
} 

httpRequest.onreadystatechange = function(){  
    if (httpRequest.readyState === 4) { 
		document.getElementById('imgwrap').innerHTML = httpRequest.responseText;
	}
};  
	
window.setInterval(function() {
	httpRequest.open('GET',baseurl+'/recentimages.php?ajax=1', true);  
    httpRequest.send(null);
}, 10000);
</script>
Below is a list of images posted to the forums in the last 24 hours, updated in real-time.<br><br>

<div id="imgwrap">
<?
}
//always echo $o, also when only ajaxing
echo $o;

if (!$ajax) {
	echo '</div>';
	require PUN_ROOT.'footer.php';
}
