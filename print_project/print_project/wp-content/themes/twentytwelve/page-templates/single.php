<?php
/**
 * Template Name: Single
 *
 * Description: A page template that provides a key component of WordPress as a CMS
 * by meeting the need for a carefully crafted introductory page. The front page template
 * in Twenty Twelve consists of a page content area for adding text, images, video --
 * anything you'd like -- followed by front-page-only widgets in one or two columns.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); 

//Get Album ID
if(isset($_GET['v']))
{
$album_id=$_GET['v'];
//Fetching photos from Album
$result2 = mysql_query("SELECT * FROM photos where album_id=$album_id");
    while($row2 = mysql_fetch_array($result2))
   {
?> <div id="imagelist">
<?php
    echo '<p><img src="../'.$row2['location'].'"></p>';
    ?>
	<a href="phpimageeditor/index.php?imagesrc=../<?php echo $row2['location']?>" >Edit</a>
	<?php echo '</div>';
   }
}
?>
