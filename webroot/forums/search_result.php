<style>
#cse-search-results iframe 
{
width:736px;
padding:2px;
}
</style>

<?php define('PUN_ROOT', './');

require PUN_ROOT.'include/common.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/search.php';
$page_title = pun_htmlspecialchars($pun_config['o_board_title']).' / '.$lang_search['Search results'];
require PUN_ROOT.'header.php';

?>
<div class="box">
	<div class="inbox">
    <div id="cse-search-results"></div>
		<script type="text/javascript">
          var googleSearchIframeName = "cse-search-results";
          var googleSearchFormName = "cse-search-box";
          var googleSearchFrameWidth = 600;
          var googleSearchDomain = "www.google.com";
          var googleSearchPath = "/cse";
        </script>
        <script type="text/javascript" src="http://www.google.com/afsonline/show_afs_search.js"></script>

    </div>
</div>
<?php
$footer_style = 'search'; 
require PUN_ROOT.'footer.php';
?>