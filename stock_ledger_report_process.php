<?php
$page_title = 'Ledger';
$results = '';
require_once('includes/load.php');
  // Checkin What level user has permission to view this page
page_require_level(4);
?>
<?php
if(isset($_POST['submit'])){
  $req_dates = array('start-date','end-date','product');
  validate_fields($req_dates);

  if(empty($errors)){
    $start_date   = remove_junk($db->escape($_POST['start-date']));
    $end_date     = remove_junk($db->escape($_POST['end-date']));
    $location     = remove_junk($db->escape($_POST['location']));
    $product     = remove_junk($db->escape($_POST['product']));


    $pro=find_value_by_sql('select products.*,unit_name,unit_type from products
      inner join units on units.id=products.unit_id
      where products.id='.$product);

    if($end_date  < $start_date)
    {
      $session->msg("d", "start date cannot be greater than to date!");
      redirect('stock_ledger_report.php', false);
    }




    $results      = find_stock_ledger_by_dates($start_date,$end_date,$location,$product  );
    //$res          = find_stock_by_product($start_date,$end_date,$location,$product  );
    $resop        = find_opening_stock_ledger_by_dates($start_date,$end_date,$location,$product  );


    $price=find_value_by_sql("select ifnull(sum(stock_price)/sum(stock_qty),0)  as price from stock where product_id=".$product)['price'];





  }
  else{
    $session->msg("d", $errors);
    redirect('stock_ledger_report.php', false);
  }

} else {
  $session->msg("d", "Select dates");
  redirect('stock_ledger_report.php', false);
}
?>
<!doctype html>
<html lang="en-US">
<head>
 <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
 <title>Stock Ledger Report</title>
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
            onclick="tableToExcel('excel', 'Stock Ledger Report', 'StockLedgerReport.xls')" 
            value="Export to Excel"/>
              <div class="page-break" id="excel">
               <div class="sale-head pull-center">
                 <h1>Product Stock Ledger</h1>
                 <strong><?php if(isset($start_date)){ echo $start_date;}?> To <?php if(isset($end_date)){echo $end_date;}?> </strong>
                 <?php if($location>0){ 
                  $loc= find_by_id('locations',$location);
                  ?>
                  <strong><?php echo  $loc['loc_name']; ?> </strong>
                <?php } ?>
                <strong><?php echo  $pro['name']; ?> [ <?php echo  $pro['short_code']; ?> ]</strong>
                <strong>Unit: <?php echo  $pro['unit_name']; ?></strong>
              </div>
              <table class="table table-border">
                <thead>
                  <tr>
                    <th rowspan="2">#</th>

                    <th rowspan="2">Date</th>

                    <th colspan="4">Quantity</th>
                   <!-- <th colspan="4">Value</th>  -->



                  </tr>
                  <tr>
                   <th>Opening</th>
                   <th>Received</th>
                   <th>Issued</th>
                   <th>Closed</th>
                   <!--
                   <th>Opening</th>
                   <th>Received</th>
                   <th>Issued</th>
                   <th>Closed</th>
                 -->
                 </tr> 

               </thead>
               <tbody>


                <?php 
                $opening_qty=sprintf('%0.2f',$resop['opening_qty']);
                foreach($results as $result):
                   //$opening_qty=$result['closing_qty']+($result['issue_qty']*-1)-$result['receive_qty'] ;
                 $closing_qty=$opening_qty+$result['issue_qty']+$result['receive_qty'];
                 ?>
                 <tr>
                  <td class="text-right"><?php echo count_id() ;?></td>

                  <td ><?php echo ($result['stock_date']);?></td>


                  <?php if($result['unit_type']=='number'){ ?>
                    <td ><?php echo intval($opening_qty);?></td>
                  <?php } else{ ?>
                   <td ><?php echo sprintf('%0.2f',$opening_qty)?></td>
                 <?php }  ?>


                 <?php if($result['unit_type']=='number'){ ?>
                  <td ><?php echo intval($result['receive_qty']);?></td>
                <?php } else{ ?>
                  <td ><?php echo ($result['receive_qty']);?></td>
                <?php }  ?>

                <?php if($result['unit_type']=='number'){ ?>
                  <td ><?php echo intval($result['issue_qty']);?></td>
                <?php } else{ ?>
                  <td ><?php echo ($result['issue_qty']);?></td>
                <?php }  ?>    

                <?php if($result['unit_type']=='number'){ ?>
                  <td ><?php echo intval($closing_qty);?></td>
                <?php } else{ ?>
                  <td ><?php echo sprintf('%0.2f',$closing_qty)?></td>
                <?php }  ?>   
                <!--
                <td ><?php echo sprintf('%0.2f',$opening_qty*$price);?></td>
                <td ><?php echo sprintf('%0.2f',$result['receive_qty']*$price);?></td>
                <td ><?php echo sprintf('%0.2f',$result['issue_qty']*$price);?></td>
                <td ><?php echo sprintf('%0.2f',$closing_qty*$price);?></td> -->
              </tr>
              <?php 
              $opening_qty= $closing_qty;

            endforeach; ?>

          </tbody>

        </table>
      </div>
      <?php
    else:
      $session->msg("d", "Sorry no stock has been found. ");
      redirect('stock_ledger_report.php', false);
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
