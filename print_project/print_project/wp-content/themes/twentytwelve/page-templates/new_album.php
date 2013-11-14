<?php
/**
 * Template Name: Add Album
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


//Adding Album info to Database
if (isset($_POST['Submit'])) {
 $name=$_POST['album'];
 $save=mysql_query("INSERT INTO album (name, user_id) VALUES ('$name','')");
 echo"Successfully Added";
 echo"<br />";
 ?>
 <a href="?page_id=10">Add Photos</a>';
<?php
 }
?>
<!--Creating New Album-->
 <form action="" method="post" enctype="multipart/form-data" name="addroom">
 New Album: <br />
   <input type="text" name="album" class="ed">
   <br />
   <input type="submit" name="Submit" value="Add" id="button1" />
 </form>
 <!-- End Album -->
</body>
</html>