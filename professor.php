<?php
// Replace with your YouTube video link and SQL backup file path
$youtubeVideo = "https://youtu.be/your_video_link";
$sqlBackup = "sqlbackup.txt";
?>
<h1>Professor's Page</h1>
<iframe width="560" height="315" src="<?php echo $youtubeVideo; ?>" frameborder="0" allowfullscreen></iframe>
<iframe src="<?php echo $sqlBackup; ?>" width="100%" height="500"></iframe>
