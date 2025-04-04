<?php

require_once('phasher.class.php'); 

session_start();

$result = isset($_SESSION['comparison_result']) ? $_SESSION['comparison_result'] : null;

error_log("Retrieved comparison_result from session: " . ($result !== null ? $result : "null"));

unset($_SESSION['comparison_result']);

$result = null;

$file1 = '../images/P8.jpg'; // Path to the first image
$file2 = '../images/P8a.jpg'; // Path to the second image

$I = PHasher::Instance();

for ($i = 8; $i < 15; $i++) 
{
    // Check if the image files exist	
	$file1 = '../images/P' . $i . '.jpg';
	$file2 = '../images/P' . $i . 'a.jpg';
	
    if (file_exists($file1) && file_exists($file2))
	{
        // Compare the images
        $comparisonResult = $I->Compare($file1, $file2);

        error_log("Comparison Result: " . $comparisonResult); 

        if ($comparisonResult > 90) 
		{ 
            $_SESSION['comparison_result'] = $_SESSION['comparison_result'] + 1; 
        } 
		else 
		{
            $_SESSION['comparison_result'] = $_SESSION['comparison_result'] + 0; 
        }
    } 
	else 
	{
        echo '2';
        $_SESSION['comparison_result'] = "One or both image files do not exist.";
        error_log("Error: One or both image files do not exist. File1: $file1, File2: $file2");
		$_SESSION['comparison_result'] = -1;
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Screen Background</title>
     <link rel="stylesheet" href="../home.css"> 
</head>
<style>

    body {
        background-image: url('../images/Parking_lot2.jpg');
        background-size: 100% 100%; 
        background-position: center;
        background-repeat: no-repeat;
        height: 100vh;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .white-box {
        background-color: white;
        width: 200px;
        height: 200px;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        text-align: center;
        font-size: 24px;
        color: black;
    }
</style>
<body>
<div class="white-box">
        <?php
            // Display the result if it has been set
            if ($result !== null) {
                if (is_numeric($result)) {
                    echo "Свободни места: $result";
                } else {
                    echo $result;
                }
            } else {
                echo "Провери дали има свободни места:";
            }
        ?>
        <br>
        <form method="POST" action="process_for_stadium.php">
            <button type="submit" class="primary-btn">Провери</button>
        </form>
        <button class="secondary-btn" onclick='window.location.assign("../pages/choose_area.html")'>Назад</button>
    </div>
</body>
</html>
