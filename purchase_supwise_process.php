<?php
$page_title = 'Supplier wise Purchase';

require_once('includes/load.php');
  // Checkin What level user has permission to view this page
page_require_level(4);


  if(isset($_POST['submit'])){
    $req_dates = array('start-date','end-date');
    validate_fields($req_dates);

    if(empty($errors)){
      $start_date   = remove_junk($db->escape($_POST['start-date']));
      $end_date     = remove_junk($db->escape($_POST['end-date']));
    


      if($end_date  < $start_date)
      {
        $session->msg("d", "start date cannot be greater than to date!");
        redirect('purchase_supwise.php', false);
      }



     
    }
    else{
      $session->msg("d", $errors);
      redirect('purchase_supwise.php', false);
    }

  } else {
    $session->msg("d", "Select dates");
    redirect('purchase_supwise.php', false);
  }



$supsql = " select suppliers.id,sup_name from stock 

inner join grn on grn.id=stock.ref_no
inner join suppliers on suppliers.id=grn.sup_id
where ref_source='GRN'
group by suppliers.id order by sup_name  ";
$ressup = find_by_sql($supsql);

$sql = "select  loc_name ";
$i=1;
foreach($ressup as $rsup): 
  $sql .=", sum(if(suppliers.id=".$rsup['id'].",stock_price,0)) as tot".$i." ";
  $i++;
endforeach;
$sql .=" from stock  ";

$sql .=" inner join locations on locations.id=stock.loc_id ";
$sql .=" inner join grn on grn.id=stock.ref_no ";
$sql .=" inner join suppliers on suppliers.id=grn.sup_id ";

$sql .=" where ref_source='GRN' and date(stock_date)>='$start_date' and date(stock_date)<='$end_date' ";
$sql .=" group by stock.loc_id order by loc_name ";

$results = find_by_sql($sql);





$sql2 = "select  loc_name ";
$i=1;
foreach($ressup as $rsup): 
  $sql2 .=", sum(if(suppliers.id=".$rsup['id'].",stock_price,0)) as tot".$i." ";
  $i++;
endforeach;
$sql2 .=" from stock  ";

$sql2 .=" inner join locations on locations.id=stock.loc_id ";
$sql2 .=" inner join grn on grn.id=stock.ref_no ";
$sql2 .=" inner join suppliers on suppliers.id=grn.sup_id ";

$sql2 .=" where ref_source='GRN' and date(stock_date)>='$start_date' and date(stock_date)<='$end_date' ";


$results2 = find_by_sql($sql2);





?>
<!doctype html>
<html lang="en-US">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 <title>Supplier wise Purchase</title>
 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"/>
 <style>
   @media print {
     html,body{
      font-size: 9.5pt;
      margin: 0;
      padding: 0;
      }.page-break {
       page-break-before:always;
       width: auto;
       margin: auto;
     }
   }
   .page-break{
    width: 980px;
    margin: 0 auto;
  }
  .sale-head{
   margin: 40px 0;
   text-align: center;
   }.sale-head h1,.sale-head strong{
     padding: 10px 20px;
     display: block;
     }.sale-head h1{
       margin: 0;
       border-bottom: 1px solid #212121;
       }.table>thead:first-child>tr:first-child>th{
         border-top: 1px solid #000;
       }
       table thead tr th {
         text-align: center;
         border: 1px solid #ededed;
         }table tbody tr td{
           vertical-align: middle;
           }.sale-head,table.table thead tr th,table tbody tr td,table tfoot tr td{
             border: 1px solid #212121;
             white-space: nowrap;
             }.sale-head h1,table thead tr th,table tfoot tr td{
               background-color: #f8f8f8;
               }tfoot{
                 color:#000;
                 text-transform: uppercase;
                 font-weight: 500;
               }
             </style>
           </head>
           <body>
            <?php if($results): ?>
              <input 
              type="button" class="btn btn-sm" 
              onclick="tableToExcel('excel', 'Supplier wise Purchase', 'SupplierPurchase.xls')" 
              value="Export to Excel"/>
              <div class="page-break" id="excel">
               <div class="sale-head pull-center">
                 <h1>Supplier wise Purchase</h1>    
                  <strong><?php if(isset($start_date)){ echo $start_date;}?> To <?php if(isset($end_date)){echo $end_date;}?> </strong>
               </div>
               <table class="table table-border">
                <thead>
                  <tr>
                    <th>#</th>
       
                    <th>Location</th>
                    <?php  foreach($ressup as $rsup):  ?>
                      <th><?php  echo $rsup['sup_name']; ?></th>
                    <?php  endforeach; ?>
                    <th>Total</th>

                  </tr>

                </thead>
                <tbody>
                  <?php 

                  foreach($results as $result): 

                      $total=0;
                    ?>
                    <tr>
                      <td class="text-right"><?php echo count_id() ;?></td>
                   
                      <td class="desc">
                        <h6><?php echo remove_junk(ucfirst($result['loc_name']));?></h6>
                      </td>

                      <?php $i=1; foreach($ressup as $rsup):  

                        $total=$result['tot'.$i]+$total;
                      ?>

                        
                        <td class="text-right"><?php echo ($result['tot'.$i]);?></td>

                      <?php $i++; endforeach; ?>

                      <td class="text-right"><?php echo sprintf('%0.2f',$total);?></td>
                     

                    </tr>
                  <?php endforeach; ?>



                  <tfoot>
                   <tr class="text-right">

                     <td colspan="2">Total</td>

                    <?php
                     foreach($results2 as $result): 

                      $total=0;
                    ?>
             
              
              

                      <?php $i=1; foreach($ressup as $rsup):  

                        $total=$result['tot'.$i]+$total;
                      ?>

                        
                        <td class="text-right"><?php echo ($result['tot'.$i]);?></td>

                      <?php $i++; endforeach; ?>

                      <td class="text-right"><?php echo sprintf('%0.2f',$total);?></td>
                     

                    </tr>
                  <?php endforeach; ?>
                    

                   </tr>

                 </tfoot>
                </tbody>


              </table>
            </div>
            <?php
          else:
            $session->msg("d", "Sorry no stock has been found. ");
            redirect('purchase_supwise.php', false);
          endif;
          ?>
        </body>
        <script type="text/javascript">
          function tableToExcel(table, name, filename) {
            let uri = 'data:application/vnd.ms-excel;base64,', 
            template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><title></title><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--><meta http-equiv="content-type" content="text/plain; charset=UTF-8"/></head><body><table>{table}</table></body></html>', 
            base64 = function(s) { return window.btoa(decodeURIComponent(encodeURIComponent(s))) },         format = function(s, c) { return s.replace(/{(\w+)}/g, function(m, p) { return c[p]; })}

            if (!table.nodeType) table = document.getElementById(table)
              var ctx = {worksheet: name || 'Worksheet', table: table.innerHTML}

            var link = document.createElement('a');
            link.download = filename;
            link.href = uri + base64(format(template, ctx));
            link.click();
          }
        </script>
        </html>
        <?php if(isset($db)) { $db->db_disconnect(); } ?>
