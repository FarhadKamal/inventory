<?php
  $page_title = 'Edit locaton';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(1);
?>
<?php
  //Display all catgories.
  $location = find_by_id('locations',(int)$_GET['id']);
  if(!$location){
    $session->msg("d","Missing location id.");
    redirect('add_location.php');
  }
?>

<?php
if(isset($_POST['edit_loc'])){
  $req_field = array('location-name');
  validate_fields($req_field);
  $loc_name = remove_junk($db->escape($_POST['location-name']));
  if(empty($errors)){

     $total=duplicate_check("locations","loc_name",$loc_name);
      if($total>0)
      {
         $session->msg("w", "Nothing updated, ".$loc_name." has aleady there.");
         redirect('add_location.php',false);
      }



        $sql = "UPDATE locations SET loc_name='{$loc_name}'";
       $sql .= " WHERE id='{$location['id']}'";
     $result = $db->query($sql);
     if($result && $db->affected_rows() === 1) {
       $session->msg("s", "Successfully updated Location");
       redirect('add_location.php',false);
     } else {
       $session->msg("d", "Sorry! Failed to Update");
       redirect('add_location.php',false);
     }
  } else {
    $session->msg("d", $errors);
    redirect('add_location.php',false);
  }
}
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
   <div class="col-md-12">
     <?php echo display_msg($msg); ?>
   </div>
   <div class="col-md-5">
     <div class="panel panel-default">
       <div class="panel-heading">
         <strong>
           <span class="glyphicon glyphicon-th"></span>
           <span>Editing <?php echo remove_junk(ucfirst($location['loc_name']));?></span>
        </strong>
       </div>
       <div class="panel-body">
         <form method="post" action="edit_location.php?id=<?php echo (int)$location['id'];?>">
           <div class="form-group">
               <input type="text" class="form-control" name="location-name" value="<?php echo remove_junk(ucfirst($location['loc_name']));?>">
           </div>
           <button type="submit" name="edit_loc" class="btn btn-primary">Update location</button>
       </form>
       </div>
     </div>
   </div>
</div>



<?php include_once('layouts/footer.php'); ?>
