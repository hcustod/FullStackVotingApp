<?php
// Replace with your YouTube video link and SQL backup file path
$youtubeVideo = "https://youtu.be/your_video_link";
$sqlBackup = "sqlbackup.txt";
?>


<!DOCTYPE html>
<html>
<head>
    <title>Hank & Fit's Voting App</title>
</head>
<body>

    <h1>Professor's Page</h1>
    <br>
    <p><a href="index.php">Return to Application</a></p>
    <br>
    <iframe width="560" height="315" src="<?php echo $youtubeVideo; ?>" allowfullscreen></iframe>
    <br>
    <iframe src="<?php echo $sqlBackup; ?>" width="100%" height="500"></iframe>
    <br>

</body>
</html>

