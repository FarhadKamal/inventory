
<link rel="stylesheet" href="libs/css/flexselect.css" type="text/css" media="screen" />

<?php


$page_title = 'Requisition';
require_once('includes/load.php');
      // Checkin What level user has permission to view this page


page_require_level(4);
$all_products = find_by_sql('select * from products order by short_code');
$last_update_date=date('Y-m-d H:i:s');
?>
<?php
$req_id=(int)$_GET['id'];
$requisition = find_by_id('requisition',(int)$_GET['id']);
$all_requisition_details = find_by_sql('select short_code,products.name,unit_type,unit_name,quantity ,requisition_details.id from requisition_details 
inner join products on requisition_details.product_id=products.id
inner join units on units.id=products.unit_id
where req_id='.$req_id.' order by id desc');

if(!$requisition){
  $session->msg("d","Missing requisition id.");
  redirect('add_requisition.php');
}
?>
<?php



if(isset($_POST['add_requisition'])){

        $product_id    = remove_junk($db->escape($_POST['product-name']));
        $product_quantity    = remove_junk($db->escape($_POST['product-quantity']));
        $submit_by= $_SESSION['user_id'];

      $req_fields = array('product-name' );
      validate_fields($req_fields);
      if($errors){
        $session->msg("d", $errors);
       redirect('edit_requisition.php?id='.$requisition['id'], false);

      }

       if($product_quantity==0){
          $session->msg("d", "product quantity cannot be zero!");
          redirect('edit_requisition.php?id='.$requisition['id'], false);
        }

        $track_id=$requisition['id'];

        
  $db->query("update  requisition set last_update_date ='{$last_update_date}',
    last_update_by ='{$submit_by}' where id={$track_id}");
        $db->query("delete from requisition_details where req_id={$track_id} and product_id={$product_id}");
         $query  = "INSERT INTO requisition_details (";
         $query .=" req_id, product_id, quantity";
         $query .=") VALUES (";
         $query .=" '{$track_id}', '{$product_id}', '{$product_quantity}'";
         $query .=")";
       

        if($db->query($query)){
          $session->msg('s',"Product added!");
          redirect('edit_requisition.php?id='.$requisition['id'], false);
       }else {
         $session->msg('d',' Sorry failed to add product!');
         redirect('edit_requisition.php?id='.$requisition['id'], false);
       }

 } 


 if(isset($_POST['final_submit'])){
    

    $expected_date   = remove_junk($db->escape($_POST['exp-date']));
/*
    if(expected_date($expected_date)==false)
     {
        $session->msg("d", "Expected date should be at least 7 days greater than current date!");
        redirect('edit_requisition.php?id='.$requisition['id'], false);

     } 
*/



    $hold_by  =APPROVAL_REQUISITION;
    $query    = "UPDATE requisition SET";
    $query    .=" submit_status =1,submit_date=now(), hold_by='{$hold_by}'";
    $query    .=" WHERE id ='{$requisition['id']}'";

     if($db->query($query)){
         $session->msg('s',' Requisition submitted!');

           $action_by= $_SESSION['user_id'];
          $query="insert into requisition_action 
            (
            req_id, 
            action_by, 
            action_details

            )
            values
            (
            '{$requisition['id']}', 
            '{$action_by}', 

            'Submitted'
            )";

            $db->query($query);
         redirect('requisition.php', false);
       }else {
         $session->msg('d','Requisition submission failed');
         redirect('requisition.php', false);
       }

    

 } 



 if(isset($_POST['approve_submit'])){
    

    $expected_date   = remove_junk($db->escape($_POST['exp-date']));
/*
    if(expected_date($expected_date)==false)
     {
        $session->msg("d", "Expected date should be at least 7 days greater than current date!");
        redirect('edit_requisition.php?id='.$requisition['id'], false);

     } 

*/


    $hold_by  =0;
    $query    = "UPDATE requisition SET";
    $query    .=" approve_status =1, hold_by='{$hold_by}'";
    $query    .=" WHERE id ='{$requisition['id']}'";

     if($db->query($query)){
         $session->msg('s',' Requisition Approved!');

           $action_by= $_SESSION['user_id'];
          $query="insert into requisition_action 
            (
            req_id, 
            action_by, 
            action_details

            )
            values
            (
            '{$requisition['id']}', 
            '{$action_by}', 

            'Approved'
            )";

            $db->query($query);
         redirect('requisition.php', false);
       }else {
         $session->msg('d','Requisition submission failed');
         redirect('requisition.php', false);
       }

    

 } 





if(isset($_POST['edit_requisition'])){

    $expected_date   = remove_junk($db->escape($_POST['exp-date']));
/*
    if(expected_date($expected_date)==false)
     {
        $session->msg("d", "Expected date should be at least 7 days greater than current date!");
        redirect('edit_requisition.php?id='.$requisition['id'], false);

     } 
*/


  $req_fields = array('contact-person');
  validate_fields($req_fields);

  if(empty($errors)){


   // $product_id    = remove_junk($db->escape($_POST['product-name']));
   // $product_quantity    = remove_junk($db->escape($_POST['product-quantity']));

    $contact_person    = remove_junk($db->escape($_POST['contact-person']));
    $request_reason   = remove_junk($db->escape($_POST['reason']));
    $submit_by= $_SESSION['user_id'];
   

    







    $query   = "UPDATE requisition SET";
    $query  .=" contact_person ='{$contact_person}',request_reason ='{$request_reason}',expected_date ='{$expected_date}',";
    $query  .=" last_update_date ='{$last_update_date}',last_update_by ='{$submit_by}'";
    $query  .=" WHERE id ='{$requisition['id']}'";
    $result = $db->query($query);
    if($result && $db->affected_rows() === 1){
     $session->msg('s',"Requisition updated ");
     redirect('edit_requisition.php?id='.$requisition['id'], false);
   } else {
     $session->msg('d',' Sorry failed to updated!');
     redirect('edit_requisition.php?id='.$requisition['id'], false);
   }

 } else{
   $session->msg("d", $errors);
   redirect('edit_requisition.php?id='.$requisition['id'], false);
 }

}

?>
<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="panel panel-default">
    <div class="panel-heading">
      <strong>
        <span class="glyphicon glyphicon-th"></span>
        <span>Requisition No: <?php echo (int)$requisition['id'] ?></span>
      </strong>
    </div>


     
    <div class="panel-body">
     <div class="col-md-7">
       <form method="post" action="edit_requisition.php?id=<?php echo (int)$requisition['id'] ?>">
        
         <div class="form-group">
          <label>Expected Date</label>
          <div class="input-group">
            <span class="input-group-addon">
             <i class="glyphicon glyphicon-calendar"></i>
           </span>
           
           <input  type="text" class="datepicker form-control " style="width: 150px;"
                 value="<?php echo  remove_junk($requisition['expected_date']); ?>"  name="exp-date" placeholder="Date" readonly>
         </div>
       </div>





        <div class="form-group">
          <label>Contact Person Name</label>
          <div class="input-group">
            <span class="input-group-addon">
             <i class="glyphicon glyphicon-user"></i>
           </span>
           <input type="text" class="form-control" name="contact-person" value="<?php echo remove_junk($requisition['contact_person']);?>">
         </div>
       </div>


       <div class="form-group">
        <div class="form-group"> <label>Reason</label>
          <textarea class="form-control" id="exampleFormControlTextarea1" name="reason" rows="3"><?php echo remove_junk($requisition['request_reason']);?></textarea>
        </div>
      </div> 

      <button type="submit" name="edit_requisition" class="btn btn-info">Update</button>
    </form>
      <br/>

      <form method="post" action="edit_requisition.php?id=<?php echo (int)$requisition['id'] ?>">
      <div class="form-group">
        <div class="row">
          <div class="col-md-12">
            <label >Product</label>
            <select class="form-control" id="product-id" name="product-name" >
              <option value="">Select Product</option>
              <?php  foreach ($all_products as $prd): ?>
                <option value="<?php echo (int)$prd['id'] ?>">
                  <?php echo $prd['short_code'] ?> <?php echo " # " ?><?php echo $prd['name'] ?></option>
                <?php endforeach; ?>
              </select>

            </div>

          </div> 

        </div>

          <div class="form-group">
            <div class="row">
              <div class="col-md-12">
                <div id="div-result">
                </div>  
              </div>
            </div>
          </div>
         </form> 



        <div class="panel-body">
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th>Short Code</th>
                <th>Products</th>
                <th>Quantity</th>
                <th>Unit</th>

                <th class="text-center" style="width: 100px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php 

              $chk=0;
              foreach ($all_requisition_details as $reqdetails): $chk=1;   ?>
                <tr>
                  <td class="text-center"><?php echo count_id();?></td>
                  <td><?php echo $reqdetails['short_code']; ?></td>
                  <td><?php echo $reqdetails['name']; ?></td>
                  <td><?php if($reqdetails['unit_type']=='number') echo intval($reqdetails['quantity']); else echo  $reqdetails['quantity']; ?></td>
                  <td><?php echo $reqdetails['unit_name']; ?></td>

                  <td class="text-center">
                    <div class="btn-group">
                      <a href="delete_requisition.php?id=<?php echo (int)$requisition['id'];?>&det=<?php echo (int)$reqdetails['id']; ?>"  class="btn btn-xs btn-danger" data-toggle="tooltip" title="Remove">
                        <span class="glyphicon glyphicon-trash"></span>
                      </a>
                    </div>
                  </td>

                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php 



          $current_user = current_user();
         
          $login_level = $current_user['user_level'];
          
          if($login_level==4)
           {
            ?>
            <form method="post" action="edit_requisition.php?id=<?php echo (int)$requisition['id'] ?>">
             <button type="submit" name="approve_submit" class="btn btn-danger">Approve</button>
             <input  type="hidden" name="exp-date"
                 value="<?php echo  remove_junk($requisition['expected_date']); ?>" />


           </form> 


            <?php
           } 
           else{


          if($chk==1){ ?>
           <form method="post" action="edit_requisition.php?id=<?php echo (int)$requisition['id'] ?>">
             <button type="submit" name="final_submit" class="btn btn-danger">Final Submit</button>
             <input  type="hidden" name="exp-date"
                 value="<?php echo  remove_junk($requisition['expected_date']); ?>" />


           </form> 
          <?php }
          }

           ?>
        </div>





        
      
    </div>
  </div>
</div>
</div>

<?php include_once('layouts/footer.php'); ?>
<script>
  $(document).ready(function() {

    $("#product-id").flexselect();

    $( "#product-id" ).change(function() {

      var id = $('#product-id').val();



      if(id!='')
      { 
        $.post('modal.php?call=2', {

          'pid': id,

        },

        function(result) {

          if (result) {


           $("#div-result").empty();
           $("#div-result").append(result);

         }
       }
       );

      }else $('#prev-short-code').val("");

    });



  });
</script>
<script src="libs/js/liquidmetal.js" type="text/javascript"></script>
<script src="libs/js/jquery.flexselect.js" type="text/javascript"></script>