

<?php
  $page_title = 'Home Page';
  require_once('includes/load.php');
  if (!$session->isUserLoggedIn(true)) { redirect('index.php', false);}
?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
<?php
   $current_user = current_user();
         
  $login_level = $current_user['user_level']; 

  if($login_level ==4){

?>

  <div class="col-md-3">
       <div class="panel panel-box clearfix">
         <div class="panel-icon pull-left bg-red">
          <i class="glyphicon glyphicon-volume-up"></i>
        </div>
        <div class="panel-value pull-right">
          <h2 class="margin-top"> <?php  



           $total_pending    = find_value_by_sql('select count(*) as tot from requisition where submit_status=1 and approve_status=0 and cancel_status=0 and hold_by='.$_SESSION['user_id']);
          echo $total_pending['tot'];


           ?> </h2>
          <p class="text-muted">Pending Requisition</p>
        </div>
       </div>
    </div>

  <?php } ?>  
 <div class="col-md-12">
    <div class="panel">
      <div class="jumbotron text-center">
         <h1>Have a nice day!</h1>
    
      </div>
    </div>
 </div>
</div>
<?php include_once('layouts/footer.php'); ?>


<?php
 

if($login_level ==4){

?>
<meta http-equiv="refresh" content="5" > 
<?php
}
?>