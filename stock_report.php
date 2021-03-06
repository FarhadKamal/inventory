<?php
$page_title = 'Stock Report';
require_once('includes/load.php');
  // Checkin What level user has permission to view this page
page_require_level(4);


?>
<?php include_once('layouts/header.php'); 


$all_locations = find_by_sql('select * from  locations order by loc_name');

?>
<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="panel">
      <div class="panel-heading">

      </div>
      <div class="panel-body">
        <form class="clearfix" method="post" target="_blank" action="stock_report_process.php">
          <div class="form-group">
            <label class="form-label">Date Range</label>
            <div class="input-group">
              <?php   

              $pdate = strtotime(date("Y-m-d"));
              $pdate = strtotime("-30 day", $pdate); ?>
              <input type="text" class="datepicker form-control" name="start-date" placeholder="From" value="<?php echo date("Y-m-d",$pdate); ?>">
              <span class="input-group-addon"><i class="glyphicon glyphicon-menu-right"></i></span>
              <input type="text" class="datepicker form-control" name="end-date" placeholder="To" value="<?php echo date("Y-m-d"); ?>">
            </div>
          </div>

          
          <div class="form-group"> <label>Location</label>
            <div class="input-group">

              <span class="input-group-addon">
               <i class="glyphicon glyphicon-flag"></i>
             </span>

             <select class="form-control"  name="location" >
              <option value="0">ALL</option>
              <?php  foreach ($all_locations as $loc): ?>
                <option value="<?php echo (int)$loc['id'] ?>">
                 <?php echo $loc['loc_name'] ?></option>
               <?php endforeach; ?>
             </select>
           </div>
         </div>

         <br/>
         <div class="form-group">
           <button type="submit" name="submit" class="btn btn-primary">Generate Report</button>
         </div>
       </form>
     </div>

   </div>
 </div>

</div>
<?php include_once('layouts/footer.php'); ?>
