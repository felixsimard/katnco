<?php 
include_once("../php_includes/db_conx.php");
include_once("../php_includes/session.php");
include_once("../class/time_ago_class.php");
include_once("../class/phone_format.php");
include_once("../class/number_format.php");
include_once("../class/sizes.php");
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "get_search_filter") { 

    $filter .= '<div class="row">';

    $query_cat = mysqli_query($db_conx, "SELECT * FROM categories WHERE active='1'");
    $numrows_cat = mysqli_num_rows($query_cat);
    if($numrows_cat < 1) {
        $cat = '';
    } else {

        while($row_cat = mysqli_fetch_array($query_cat, MYSQLI_ASSOC)) {

            $cat_id = $row_cat["id"];
            $cat = $row_cat["category"];

            $filter .= '<div class="col-md-4 filter_divs">';
            $filter .= '<div id="cat_'.$cat_id.'" class="filter_tabs" style="font-size:20px;font-weight:700;">'.$cat.'</div>';

            $sub_categories = ''; //re initialize it to nothing
            $query_subcat = mysqli_query($db_conx, "SELECT * FROM subcategories WHERE parent='$cat_id' AND active='1'");
            $numrows_subcat = mysqli_num_rows($query_subcat);
            if($numrows_subcat < 1) {
                $subcat = '';
            } else {

                while($row_subcat = mysqli_fetch_array($query_subcat, MYSQLI_ASSOC)) {

                    $subcat_id = $row_subcat["id"];
                    $subcat = $row_subcat["category"];

                    $sub_categories .= '<div id="subcat_'.$subcat_id.'" onclick="filter(\'category\', \''.$subcat_id.'\', \''.$subcat.'\')" class="filter_tabs filter_hover filter_tab_unselected">';
                    //$sub_categories .= '<label class="checkbox_container">'.$subcat.'<input id="subcat_check_'.$subcat_id.'" type="checkbox"><span class="checkmark"></span></label>';
                    $sub_categories .= ''.$subcat.'';
                    $sub_categories .= '</div>';

                }

                $filter .= ''.$sub_categories.'';
                $filter .= '</div>';

            }
        }
    }


    $filter .= '</div>';

    echo $filter;
    exit();

}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "search_suggested") {

    $max_price = preg_replace('#[^0-9]#', '', $_POST["max_price"]);
    $viewport_size = preg_replace('#[^0-9]#', '', $_POST["viewport_size"]);

    // Determine LIMIT of mysql query depending on viewport size
    if($viewport_size > 767) {
        $query_limit = 15; // divisible by 3
    } else {
        $query_limit = 14; // divisible by 2
    }

    $displayed_items = preg_replace('#[^0-9, ]#i', '', $_POST["displayed_items"]);
    function displayedArray($terms) {
        //Create array of displayed items 
        if($terms) {
            $dis_items = explode(" ", $terms);
            $length = sizeof($dis_items);
            for($i = 0; $i < $length; $i++) {
                $dis_item_id = $dis_items[$i];
                $or .= 'id<>"'.$dis_item_id.'"'; 
                if($i < ($length - 1)) {
                    $or .= ' OR ';
                } 
            }
        }
        return $or;
    }
    if($displayed_items) {
        $items_already_displayed = displayedArray($displayed_items);
    } else {
        $items_already_displayed = 'id<>"impossible_id_1999"';
    }
    
    
    $query = mysqli_query($db_conx, "SELECT item_id FROM searches WHERE searcher='$log_id' GROUP BY item_id ORDER BY COUNT(id) DESC LIMIT 24");
    $numrows = mysqli_num_rows($query);

    $search_results .= '<div id="search_grid" class="row">';

    $numrows = 10; // set this to avoid the other condition for now...
    if($numrows < 25) { //need a minimum of 25 possible items ($numrows < 25)
        
        $suggestion_sql = "SELECT * FROM store WHERE ($items_already_displayed) AND active='1' ORDER BY RAND() LIMIT $query_limit";
        $query2 = mysqli_query($db_conx, $suggestion_sql);
        while($row2 = mysqli_fetch_array($query2, MYSQLI_ASSOC)) {
            $item_id = $row2["id"];
            $caption = $row2["caption"];
            $pic = $row2["pic"];
            $sizes = $row2["sizes"];
            //$min_new = $row2["min_new"];
            //$min_used = $row2["min_used"];
            $added = $row2["added"];

                /*if($min_new <= $max_price) {
                } */
            $results .= '<div id="displayed_item_'.$item_id.'" class="col-md-4 col-xs-6 search_grid_item">';
            $results .= '<div class="search_rows" onclick="flyout(\'see_item\', \''.$item_id.'\')">';
            $results .= '<div class="search_img" style="background:url(https://scavengg.com/store/'.$pic.') no-repeat center center;background-size:contain;"></div>';
            $results .= '</div>';
            $results .= '</div>';

            }

    } else { // we have enough info about the user to suggest him/her items 
    
        while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
            $item_id = $row["item_id"];

            //item info
            $query_item = mysqli_query($db_conx, "SELECT caption, pic, min_new, min_used, added FROM store WHERE active='1' AND id='$item_id' LIMIT 1");
            $rows = mysqli_fetch_row($query_item);
            $caption = $rows[0];
            $pic = $rows[1];
            //$min_new = $rows[2];
            //$min_used = $rows[3];
            $added = $rows[4];

                /*if($min_new <= $max_price) {
                } */
            $results .= '<div class="col-md-4 col-xs-6 search_grid_item">';
            $results .= '<div class="search_rows" onclick="flyout(\'see_item\', \''.$item_id.'\')">';
            $results .= '<div class="search_img" style="background:url(https://scavengg.com/store/'.$pic.') no-repeat center center;background-size:contain;"></div>';
            $results .= '</div>';
            $results .= '</div>';

            }
    }

    $search_results .= ''.$results.'</div>';
    
    echo ''.$search_results.'';
    exit();

}
?>
<?php
if(isset($_POST["action"]) && $_POST["action"] == "search_store") {

    $terms = htmlentities($_POST['terms']);
	$terms = mysqli_real_escape_string($db_conx_test, $terms);
    $action2 = htmlentities($_POST['action2']);
	$action2 = mysqli_real_escape_string($db_conx_test, $action2);
    $subcategories = htmlentities($_POST["categories"]); // array of subcategory indexes selectedt by user
    $subcategories = mysqli_real_escape_string($db_conx_test, $subcategories);
    $max_price = preg_replace('#[^0-9]#', '', $_POST["max_price"]);

    if($action2 == "custom_request") {
            $results .= '<div class="col-md-12 search_grid_item">';
            $results .= '<div class="search_rows">';
            $results .= 'Custom Order <hr>';
            $results .= '<input id="custom_order_desc" class="inputs" value="'.$terms.'" placeholder="Description" maxlength="200" autocorrect="off" spellcheck="false">';
            $results .= '<input id="custom_order_size" class="inputs_half" placeholder="Size" maxlength="25" autocorrect="off" spellcheck="false"><br>';
            $results .= '<input id="custom_order_brand" class="inputs_half" placeholder="Brand" maxlength="50" autocorrect="off" spellcheck="false">';
            $results .= '<input id="custom_order_season" class="inputs_half" placeholder="Season" maxlength="10" autocorrect="off" spellcheck="false">';
            $results .= '<form id="requests_image" style="border-radius:2px;" action="" method="post" enctype="multipart/form-data">';
            $results .= '<div id="requests_avatar_preview" style="border-radius:2px;">';
            $results .= '<label for="requests_file_settings" id="requests_file_label_settings">';
            $results .= '<img id="requests_file_selector_settings" class="requests_settings_previewing" src="https://scavengg.com/photos/img_placeholder.png">';
            $results .= '<input type="file" name="requests_file_settings" id="requests_file_settings" required />';
            $results .= '</label>';
            $results .= '</div>';
            $results .= '</form>';
            $results .= '<hr><button id="custom_order_btn" class="btns" onclick="customRequest()">Request Item</button>';
            $results .= '<div class="small_text">Requests are free. You will be notified when we get your item.</div>';
            $results .= '</div>';
            $results .= '</div>';

            echo $results;
            exit();
    }

    // Helper functions
    function termsArray($column, $terms, $operator) {
        //Create terms array
        if($terms) {
            $terms_array = explode(" ", $terms);
            for($i = 0; $i < count($terms_array); $i++) {
                if($operator == "like") {
                    $keywords .= "".$column." LIKE '%".$terms_array[$i]."%'";
                } else { // equals
                    $keywords .= "".$column."='".$terms_array[$i]."'";
                }
                if($i < (count($terms_array) - 1)) {
                    $keywords .= ' OR ';
                } 
            }
        }
        return $keywords;
    }
    function filtersArray($column, $terms) {
        //Create array of possibly selected filters
        if($terms) {
            $subcat_ids = explode(",", $terms);
            $length = sizeof($subcat_ids);
            for($i = 0; $i < $length; $i++) {
                $subcat_id = $subcat_ids[$i];
                $or .= ''.$column.'='.$subcat_id.''; 
                if($i < ($length - 1)) {
                    $or .= ' OR ';
                } 
            }
        }
        return $or;
    }

    function orderArray($column, $terms) {
        //For Ordering results
        if($terms) {
            $terms_array = explode(" ", $terms);
            for($i = 0; $i < count($terms_array); $i++) {
                $keywords .= "case when ".$column." LIKE '%".$terms_array[$i]."%' then 1 else 0 end";
                if($i < (count($terms_array) - 1)) {
                    $keywords .= ' + ';
                } 
            }
        }
        return $keywords;
    }

    $search_results .= '<div id="search_grid">';

    if($terms == "" && $subcategories == "") { // nothing provided...
        $results = "";
    } else {

        // Create array of possible selected filters
        $filters_keywords = filtersArray('category', $subcategories, "equals");

            // Create arrays of keywords for caption, brand
            $caption_keywords = termsArray('caption', $terms, "like");
            $brand_keywords = termsArray('brand', $terms, "like");

            // For Ordering Results
            $caption_order_array = orderArray('caption', $terms);

            //check if the search words are subcategories
            $subcatkeywords = termsArray('category', $terms, "like");
            $query_subcat = mysqli_query($db_conx_test, "SELECT id FROM subcategories WHERE ($subcatkeywords) AND active='1'");
            $check_subcat = mysqli_num_rows($query_subcat);
            if($check_subcat > 0) { // search terms match a subcategory
                while($subcat_row = mysqli_fetch_array($query_subcat, MYSQLI_ASSOC)) {
                    $subcategory_id = $subcat_row["id"];
                    $searched_subcat .= ''.$subcategory_id.' ';
                }
                $searched_subcat = trim($searched_subcat); // remove ending space
            } else {
                $searched_subcat = '';
            } 
            // Create array of keywords for possible category
            $subcat_keywords = termsArray('category', $searched_subcat, "equals");

            // FORMAT THE QUERY CONDITIONS
                $conditions = "(caption='impossible_caption'";
                if($caption_keywords) {
                    $conditions .= " OR $caption_keywords";
                }
                if($brand_keywords) {
                    $conditions .= " OR $brand_keywords";
                }
                if($subcat_keywords) {
                    $conditions .= " OR $subcat_keywords";
                }
                if($filters_keywords) {
                    $conditions .= " OR $filters_keywords";
                }
                $conditions .= ")";

                if($caption_order_array) {
                    $order_relevance = "".$caption_order_array." DESC";
                } else {
                    $order_relevance = "RAND()";
                }

    
        // RUN SEARCH QUERY

        // Search query below does not rank results by relevance
        //$search_sql = "SELECT * FROM store WHERE $conditions AND  active='1' ORDER BY RAND()";
        
        // Ranks results in order of relevance
        $search_sql = "SELECT * FROM store WHERE $conditions AND active='1' ORDER BY $order_relevance";
        $query = mysqli_query($db_conx_test, $search_sql);

        // In any case, the query is executed below
        $numrows = mysqli_num_rows($query);


        //Testing purposes
        //$results .= '<div classs="col-md-12 search_grid_item"><div class="search_rows">'.$search_sql.'</div></div>';

        if($numrows < 1) {
            $results .= '<div class="col-md-12 search_grid_item">';
            $results .= '<div class="search_rows">';
            $results .= 'Custom Order <hr>';
            $results .= '<input id="custom_order_desc" class="inputs" value="'.$terms.'" placeholder="Description" maxlength="200" autocorrect="off" spellcheck="false">';
            $results .= '<input id="custom_order_size" class="inputs_half" placeholder="Size" maxlength="25" autocorrect="off" spellcheck="false"><br>';
            $results .= '<input id="custom_order_brand" class="inputs_half" placeholder="Brand" maxlength="50" autocorrect="off" spellcheck="false">';
            $results .= '<input id="custom_order_season" class="inputs_half" placeholder="Season" maxlength="10" autocorrect="off" spellcheck="false">';
            $results .= '<form id="requests_image" style="border-radius:2px;" action="" method="post" enctype="multipart/form-data">';
            $results .= '<div id="requests_avatar_preview" style="border-radius:2px;">';
            $results .= '<label for="requests_file_settings" id="requests_file_label_settings">';
            $results .= '<img id="requests_file_selector_settings" class="requests_settings_previewing" src="https://scavengg.com/photos/img_placeholder.png">';
            $results .= '<input type="file" name="requests_file_settings" id="requests_file_settings" required />';
            $results .= '</label>';
            $results .= '</div>';
            $results .= '</form>';
            $results .= '<hr><button id="custom_order_btn" class="btns" onclick="customRequest()">Request Item</button>';
            $results .= '<div class="small_text">Requests are free. You will be notified when we get your item.</div>';
            $results .= '</div>';
            $results .= '</div>';
        } else {

            while($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
                $item_id = $row["id"];
                $caption = $row["caption"];
                $pic = $row["pic"];
                //$min_new = round($row["min_new"], 2);
                //$min_used = round($row["min_used"], 2);
                $added = $row["added"];

                /*if($min_new <= $max_price) {

                }*/                    
                
                $results .= '<div class="col-md-4 col-xs-6 search_grid_item">';
                $results .= '<div class="search_rows" onclick="flyout(\'see_item\', \''.$item_id.'\')">';
                $results .= '<div class="search_img" style="background:url(https://scavengg.com/store/'.$pic.') no-repeat center center;background-size:contain;"></div>';
                $results .= '</div>';
                $results .= '</div>';

            }
        }
    }

    $search_results .= ''.$results.'</div>';

    echo $results;
    exit();

}
?>