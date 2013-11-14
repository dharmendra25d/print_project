<?php
include('config.php');
if(isset($_FILES['files'])){
    $album_id=$_POST['album_id'];
    $errors= array();
	foreach($_FILES['files']['tmp_name'] as $key => $tmp_name ){
		$file_name = $key.$_FILES['files']['name'][$key];
		$file_size =$_FILES['files']['size'][$key];
		$file_tmp =$_FILES['files']['tmp_name'][$key];
		$file_type=$_FILES['files']['type'][$key];	
		$location="photos/" . $_FILES["files"]["name"][$key];

        if($file_size > 2097152){
			$errors[]='File size must be less than 2 MB';
        }		
        $query="INSERT INTO photos (location,album_id) VALUES('$location','$album_id'); ";
        $desired_dir="photos";
        if(empty($errors)==true){
            if(is_dir($desired_dir)==false){
                mkdir("$desired_dir", 0700);		// Create directory if it does not exist
            }
            if(is_dir("$desired_dir/".$file_name)==false){
                move_uploaded_file($file_tmp,"$desired_dir/".$_FILES["files"]["name"][$key]);
            }else{									// rename the file if another one exist
                $new_dir="$desired_dir/".$file_name.time();
                 rename($file_tmp,$new_dir) ;				
            }
		 mysql_query($query);			
        }else{
                print_r($errors);
        }
    }
	if(empty($error)){
		echo "Success";
	}
}
?>

<html lang="en">
<head>
<link rel="stylesheet" href="style.css" />
</head>
<body>
<form action="" method="POST" enctype="multipart/form-data">
    Select Album: <br />
<select name="album_id" required>
<option value="" >Select One</option>
<?php
//Fetching Album Info  
$result = mysql_query("SELECT * FROM album");
while($row = mysql_fetch_array($result))
{
?>
   <option value="<?php echo $row['id'];?>"><?php echo $row['name'];?></option>
<?php 
}
?>
</select>
 <br />
Select Image: <br />

	<input type="file" name="files[]" class="ed" multiple/>
	<input type="submit"  value="Upload" id="button1" />
</form>
<?php
//Add photo to Album
$result = mysql_query("SELECT * FROM album");
while($row1 = mysql_fetch_array($result))
 {
 $result1 = mysql_query("SELECT * FROM photos where album_id=$row1[id] LIMIT 1");
   while($row = mysql_fetch_array($result1))
    {
?>
     <a href="#" onclick="document.f1.v.value='<?php echo $row1['id']?>'; document.f1.submit();"><div id="imagelist">
     <?php
     echo '<p><img src="'.$row['location'].'"></p>';
     echo '<p id="caption">'.$row1['name'].' </p>';
     echo '</div></a>';
    }
 }
?>
<form name=f1 id="f1" action="single.php" method=GET >
<input name=v type=hidden value=undefined>
</form>
</body>
</html>