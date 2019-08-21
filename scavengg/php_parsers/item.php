<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
?>
<?php
// ADD TO CART
if(isset($_POST["action"]) && $_POST["action"] == "add_to_cart") {

    $item_id = preg_replace('#[^0-9]#', '', $_POST["item_id"]);
    $uid = preg_replace('#[^0-9]#', '', $_POST["uid"]);
    $size = htmlentities($_POST['size']);
	$size = mysqli_real_escape_string($db_conx_test, $size);
    //$state = htmlentities($_POST['state']);
    //$state = mysqli_real_escape_string($db_conx_test, $state);
    $price = preg_replace('#[^0-9]#', '', $_POST["price"]);

    //Check if EXACT SAME item (size + condition) already added to cart
    $sql_check = "SELECT id FROM carts WHERE item_id='$item_id' AND customer='$uid' AND size='$size' AND price='$price' LIMIT 1";
    $query_check = mysqli_query($db_conx_test, $sql_check);
    $check = mysqli_num_rows($query_check);
    $row_id = mysqli_fetch_row($query_check);
    $db_id = $row_id[0];
    if($check > 0) { // item already in user's cart
        echo 'item_already_added';
        exit();
    } else {
        mysqli_query($db_conx_test, "INSERT INTO carts (item_id, customer, size, price, state, added) VALUES ('$item_id','$uid','$size','$price','new',now())");
        $cart_item_id = mysqli_insert_id($db_conx_test); 

        echo 'added_to_cart';
        exit();
    }


}
?>
<?php
// Look up price for a given size
if(isset($_POST["action"]) && $_POST["action"] == "update_displayed_price") {

    $item_id = preg_replace('#[^0-9]#', '', $_POST["item_id"]);
    $size = $_POST["size"];

    $query = mysqli_query($db_conx_test, "SELECT price FROM prices WHERE item_id='$item_id' AND size='$size' LIMIT 1");
    $row = mysqli_fetch_row($query);
    $price = $row[0];

    echo $price;
    exit();

}
?>
<?php 
// ITEM FLYOUT
if(isset($_POST["action"]) && $_POST["action"] == "get_item") {

    $item_id = preg_replace('#[^0-9]#', '', $_POST["item_id"]);

    if($item_id == "") {
        $item = '';
    } else {
        $query = mysqli_query($db_conx_test, "SELECT * FROM store WHERE id='$item_id' LIMIT 1");
        $numrows = mysqli_num_rows($query);
        if($numrows < 1) {
            $item .= '<div class="row">';
            $item .= '<div class="col-md-12" style="text-align:center;font-size:40px;font-weight:700;margin:20px auto;color:#e6e6e6;">';
            $item .= 'This item does not exist anymore, sorry.';
            $item .= '</div>';
            $item .= '</div>';
        } else {
            while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                $item_id = $row["id"];
                $caption = $row["caption"];
                $pic = $row["pic"];
                $sizes = $row["sizes"];
                $min_new = round($row["min_new"], 2);
                $min_used = round($row["min_used"], 2);
                $min_newish = round($row["min_newish"], 2);
                $added = $row["added"];

                //Get according sizes
                $query = mysqli_query($db_conx_test, "SELECT size FROM charts WHERE id='$sizes' AND active='1' LIMIT 1");
                $numrow = mysqli_num_rows($query);
                if($numrow < 1) {
                    $sizes .= '<option></option>';
                } else {
                    $row = mysqli_fetch_row($query);
                    $size_str = $row[0];
                    if($size_str == "OS") {
                        $size_chart = '<select id="size_chart" class="select_input"><option selected="true" value="OS">OS</option></select>&#9662';
                    } else {
                        $size_array = explode('-', $size_str);
                        for($i = 0; $i < sizeof($size_array); $i++) {
                            $sizes .= '<option value="'.$size_array[$i].'">'.$size_array[$i].'</option>';
                        }
                        $size_chart = '<select id="size_chart" class="select_input" onchange="updateDisplayedPrice(\''.$item_id.'\', this.value, \'item_price_per_size\')"><option selected="true" disabled="true" value="">Select size</option>'.$sizes.'</select>&#9662';
                    }
                }

                $item .= '<div id="item_'.$item_id.'" class="row">';
                $item .= '<div class="col-md-2">';
                $item .= '<img class="flyout_img" src="https://scavengg.com/store/'.$pic.'">';
                $item .= '</div>';
                $item .= '<div class="col-md-10" style="font-weight:600;">';
                $item .= ''.$caption.'<br>';
                //$item .= '<select id="condition" class="select_input"><option disabled="disabled" value="">Condition</option>';
                //if($min_new != "") {
                    //$item .= '<option value="new" selected="true">New '.$min_new.'$</option>';
                //}
                //if($min_newish != "") {
                    //$item .= '<option value="newish">New, no tags '.$min_newish.'$</option>';
                //}
                if($min_used != "") {
                    $item .= '<option value="used">Used '.$min_used.'$</option>';
                }
                //$item .= '</select>';
                //$item .= '&#9662;';
                $item .= ''.$size_chart.'';
                $item .= '&nbsp;&nbsp;';
                $item .= '<div class="disabled_price_input" style="margin:0px;">$</div><input id="item_price_per_size" class="disabled_price_input" value="'.$min_new.'" disabled="true">';
                $item .= '<hr>';
                $item .= '<button class="dark_btn" onclick="addCart(\''.$item_id.'\')">Add to Cart</button>';
                $item .= '<button class="light_btn" onclick="addCart(\''.$item_id.'\', \'checkout\')">Fast Checkout</button>';
                $item .= '</div>';
                $item .= '</div>';

            }
            //Add to searches table
            $sql_search = "INSERT INTO searches (item_id, searcher, time) VALUES ('$item_id','$log_id',now())";
            mysqli_query($db_conx_test, $sql_search);
        }
    }

    echo $item;
    exit();

}

?>