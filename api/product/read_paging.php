<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
  
// include database and object files
require __DIR__."/../../vendor/autoload.php";
  
// utilities
$utilities = new Api\Shared\Utilities();
  
// instantiate database and product object
$database = new \Api\Config\Database();
$db = $database->getConnection();
  
// initialize object
$product = new \Api\Objects\Product($db);

// show error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
  
// home page url
$home_url="http://localhost/api_db/api/";
  
// page given in URL parameter, default page is one
$page = isset($_GET['page']) ? $_GET['page'] : 1;
  
// set number of records per page
$records_per_page = 5;
  
// calculate for the query LIMIT clause
$from_record_num = ($records_per_page * $page) - $records_per_page;
  
// query products
$stmt = $product->readPaging($from_record_num, $records_per_page);
$num = $stmt->rowCount();
  
// check if more than 0 record found
if($num > 0){
    // products array
    $products_arr = array();
    $products_arr["records"] = array();
    $products_arr["paging"] = array();
  
    // retrieve our table contents
    // fetch() is faster than fetchAll()
    // http://stackoverflow.com/questions/2770630/pdofetchall-vs-pdofetch-in-a-loop
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        // extract row
        // this will make $row['name'] to
        // just $name only

        $product_item=array(
            "id" => $row->$id,
            "name" => $row->$name,
            "description" => html_entity_decode($row->$description),
            "price" => $row->$price,
            "category_id" => $row->$category_id,
            "category_name" => $row->$category_name
        );
  
        array_push($products_arr["records"], $product_item);
    }
  
  
    // include paging
    $total_rows = $product->count();
    $page_url = "{$home_url}product/read_paging.php?";
    $paging = $utilities->getPaging($page, $total_rows, $records_per_page, $page_url);
    $products_arr["paging"] = $paging;
  
    // set response code - 200 OK
    http_response_code(200);
  
    // make it json format
    echo json_encode($products_arr);
}
  
else{
  
    // set response code - 404 Not found
    http_response_code(404);
  
    // tell the user products does not exist
    echo json_encode(
        array("message" => "No products found.")
    );
}