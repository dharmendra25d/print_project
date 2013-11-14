<?php
$current = (isset($_GET['level_action'])&&$_GET['level_action']>0&&$_GET['level_action']<4)? $_GET['level_action']: 1;
?>
<ul class="eMemberSubMenu">
   <li <?php echo ($current==1) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_membership_level_menu&level_action=1">Manage Levels</a></li>
   <li <?php echo ($current==2) ? 'class="current"' : ''; ?> ><a href="admin.php?page=eMember_membership_level_menu&level_action=2">Manage Content Protection</a></li>    
</ul>