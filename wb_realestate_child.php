<?php
/* Plugin Name: Real Estate Property Feed
  Plugin URI: www.createvic.com.au
  Description: Synchronize real estate records from WebsiteBlue Feed Server and migrate to your site.
  Version: 1.0.0
  Author: Turing Labs
  Author URI: www.createvic.com.au
  License: GPLv2 or later
 */


require_once 'fetchTypes.php';

set_time_limit(0);
add_action('admin_menu', 'scm_property_listing_pages');

function scm_property_listing_pages()
{
    add_submenu_page(
        'edit.php?post_type=listing',
        __('Listing Settings', 'menu-listing'),
        __('Listing Settings', 'menu-listing'),
        'manage_options',
        'listingsettings',
        'scm_property_listing_settings_page'
    );
}




add_action('wp_ajax_call_property_listing_settings', 'new_listing_ApiData', 1, 0);
//add_action('wp_ajax_call_property_listing_settings', 'new_listing_ApiData', 1, 0);


function scm_property_listing_settings_page($listItem, $classification, $ptype)
{

    $createdPosts = array();
    $createdAgents = array();
    $updatedPosts = array();


        $uniqId = '';
        $api_re_author_image_id = get_option('wb_realestate_agent_image_id');
        
 
         
                //print_r($listItem);die();
                $addressField = $listItem->address;
                $itemsPhotos = $listItem->photos;
                $agentList = $listItem->contactStaff;
                if (isset($listItem->leaseLifeId)) {
                    $uniqId = 'R' . $listItem->leaseLifeId;
                } else {
                    $uniqId = 'L' . $listItem->saleLifeId;
                }
                $args = array(
                    'post_type'   => 'listing',
                    'meta_query'  => array(
                        array(
                            'key' => '_listing_id',
                            'value' => $uniqId
                        )
                    )
                );
                $my_query = new WP_Query($args);
                $post_data_id = null;
                if (empty($my_query->have_posts())) {
                    // $newPost[] = $uniqId;

                    $property_contract = $property_sold = $property_id = $featured = $_price_offer = $_price_period = $a_post_id = $priceview = $land_area = $land_unit = $details_1 =  $details_2 =  $details_3 = $details_4 =  $details_5 =  $details_6 =  $details_7 =  $details_8 =  $floor_plan =  $video_url = $pools = '';
                    $rea_soi = $latitude =  $longitude =  $address_display = $listingType = '';
                    $bedrooms = $garages =  $toilets = $bathrooms = $carports = $price = 0;
                    $user_id = $uAgentname = $agent_logo = $agent_name = $agent_company = $agent_description = $agent_phone = $agent_website = $agent_twitter = $agent_facebook = $agent_instagram = $agent_email = $agent_linkedin = '';

                    $a_agents = array();

                    foreach ($agentList as $lAgent) {
                        $a_uargs = array(
                            'role__in' => array('listing_agent'),
                            'meta_key' => '_agent_id',
                            'meta_value' => $lAgent->id,
                            'fields' => 'ID'
                        );
                        //print_r($lAgent);
                        $uAgentname = $lAgent->username;
                        $lagentFirst = $lAgent->firstName;
                        $lagentLast = $lAgent->lastName;
                        $agent_name = $lAgent->username;
                        $agent_id = $lAgent->id;
                        $agent_logos = $lAgent->photo;
                        $agent_logo = $agent_logos->original;
                        $agent_website = $lAgent->websiteUrl;
                        //$agent_description = $lAgent->name;
                        //$agent_company = $lAgent->name;
                        //$agent_facebook = $lAgent->social_facebook;
                        //$agent_instagram = $lAgent->social_pinterest;
                        //$agent_twitter = $lAgent->social_twitter;
                        //$agent_linkedin = $lAgent->social_linkedin;
                        $agent_email = $lAgent->email;
                        $agemt_data = get_users($a_uargs);
                        if (!empty($agemt_data)) {
                            foreach ($agemt_data as $a_user) {
                                   $user_id = $a_user;
                                break;
                            }
                        } else {
                            if ($user_id == '') {
                                $random_password = wp_generate_password(12, false);
                                $username =  sanitize_title($lAgent->username);
                                $agent_email = $lAgent->email;
                                $aEmail = $lAgent->email;

                                $phoneNumbers = $lAgent->phoneNumbers;
                                $aPhone = '';
                                $aMobile = '';
                                if (!empty($phoneNumbers)) {
                                    foreach ($phoneNumbers as $phoneNumber) {
                                        if ($phoneNumber->type == 'Mobile') {
                                            $aMobile = $phoneNumber->number;
                                        } else {
                                            $aPhone = $phoneNumber->number;
                                        }
                                    }
                                }
                                $getuser = get_user_by('email', $aEmail);
                                if (empty($getuser)) {
                                    $aName = $lAgent->username;

                                    $agent_role = "listing_agent";
                                    $a_post_post_type = "agent";
                                    $a_post_data = [
                                        'post_title' => $lAgent->username,
                                        'post_name' => $lAgent->username,
                                        'post_status' => 'publish',
                                        'post_type' => $a_post_post_type,
                                        'post_author' => 1,
                                        'comment_status' => 'closed'
                                    ];
                                    $userdata = array(
                                        'user_login' => $username,
                                        'user_url' => '',
                                        'user_pass' => $random_password,
                                        'user_email' => $aEmail,
                                        'display_name' => $aName,
                                        'nickname' => $aName,
                                        'first_name' => $aName,
                                        'role' => $agent_role
                                    );

                                    $user_id = wp_insert_user($userdata);
                                } else {
                                    $user_id = $getuser->ID;
                                }
                                update_user_meta($user_id, '_agent_id', $lAgent->id);
                                update_user_meta($user_id, 'mobile', $aMobile);
                                update_user_meta($user_id, 'phone', $aPhone);
                                update_user_meta($user_id, 'phone_ah', $aPhone);
                                update_user_meta($user_id, 'sort_order', 0);
                                $a_post_data['post_content'] = "";
                                $a_post_id = wp_insert_post($a_post_data);
                                update_post_meta($a_post_id, '_thumbnail_id', $api_re_author_image_id);
                                update_post_meta($a_post_id, 'agent_phone', $aMobile);
                                update_post_meta($a_post_id, 'agent_phone_bh', $aPhone);
                                update_post_meta($a_post_id, 'agent_phone_ah', $aPhone);
                                update_post_meta($a_post_id, 'agent_email', $aEmail);
                                update_post_meta($a_post_id, 'display_agent', 1);
                                $_agent['user_id'] = $user_id;

                                array_push($createdAgents, $username);
                            }
                        }
                    }
                    //die();

                    $l_id = $listItem->id;
                    $post_title   = $listItem->heading;
                    $laddress = $listItem->address;
                    $suburbList = $laddress->suburb;
                    $suburb = $suburbList->name;
                    $postcode = $suburbList->postcode;
                    $lstatesList = $laddress->state;
                    $stateName = $lstatesList->name;
                    $address = str_replace('/', '-', $laddress->streetNumber . " " . $laddress->street . " " . $suburb . " " . $stateName  . " " . $postcode);
                    if (!empty($laddress->unitNumber)) {
                        $street_address = $laddress->unitNumber . '/' . $laddress->streetNumber . ' ' . $laddress->street;
                    } else {
                        $street_address = $laddress->streetNumber . ' ' . $laddress->street;
                    }
                    $geolocations = $listItem->geolocation;
                    if (isset($geolocations->latitude)) {
                        $latitude = $geolocations->latitude;
                    } else {
                        $latitude = $geolocations->latitude;
                    }
                    if (isset($listItem->searchPrice)) {
                        $price = $listItem->searchPrice;
                    }
                    if (isset($listItem->displayPrice)) {
                        $priceview = $listItem->displayPrice;
                    }
                    if (isset($laddress->display)) {
                        $address_display = $laddress->display;
                    }
                    if (isset($geolocations->longitude)) {
                        $longitude = $geolocations->longitude;
                    } else {
                        $longitude = $geolocations->longitude;
                    }
                    $listing_permalink = strtolower($uniqId . '-' . $address);
                    $l_id = $listItem->id;
                    $post_desc   = $listItem->description;
                    $lpost_type = 'listing';
                    $post_author = $user_id;
                    $post_data = [
                        'post_title'     => $post_title,
                        'post_content'   => $post_desc,
                        'post_name'      => $listing_permalink,
                        'post_status'    => 'publish',
                        'post_type'      => $lpost_type,
                        'post_author'    => $post_author,
                        'comment_status' => 'closed'
                    ];
                    $post_data['post_date'] = date('Y-m-d H:i:s', strtotime($listItem->modified));
                    $post_date = date('Y-m-d H:i:s', strtotime($listItem->modified));
                    $post_data['post_date_gmt'] = $post_data['post_date'];
                    $post_id = wp_insert_post($post_data, true);

                    array_push($createdPosts, $listing_permalink);


                    if (isset($listItem->bed)) {
                        $bedrooms = $listItem->bed;
                    }
                    if (isset($listItem->bath)) {
                        $bathrooms = $listItem->bath;
                    } else {
                        $bathrooms = 1;
                    }
                    if (isset($listItem->toilets)) {
                        $toilets = $listItem->toilets;
                    }
                    if (isset($listItem->garages)) {
                        $garages = $listItem->garages;
                    } else {
                        $garages = 1;
                    }
                    if (isset($listItem->carports)) {
                        $carports = $listItem->carports;
                    }
                    if (isset($listItem->soiUrl)) {
                        $soiUrl = $listItem->soiUrl;
                    }

                    if (isset($listItem->commercialListingType)) {
                        $listingType = $listItem->commercialListingType;
                    }
                    if (isset($listItem->addressVisibility)) {
                        $addressVisibility = $listItem->addressVisibility;
                    }
                    if (isset($listItem->displayAddress)) {
                        $displayAddress = $listItem->displayAddress;
                    }

                    //$terms_location = ucwords($suburb);
                    $terms_location = ucwords($suburb);
                    $terms_location_term = term_exists($terms_location, 'location');
                    if (empty($terms_location_term)) {
                        $terms_location_term = wp_insert_term($terms_location, 'location', array('parent' => $terms_location_term['term_id']));
                    }
                    wp_set_object_terms($post_id, $terms_location, 'location');

                    if (isset($listItem->authorityType)) {
                        $authorityTypes = $listItem->authorityType;
                        $authorityType = $authorityTypes->name;
                        if ($authorityType == 'Exclusive') {
                            $recently_listed_category = 'Recently Listed';
                            wp_set_object_terms($post_id, $recently_listed_category, 'listing-category', true);
                            $recently_listed_monthly_category = 'Recently Listed - Monthly';

                            wp_set_object_terms($post_id, $recently_listed_monthly_category, 'listing-category', true);

                            $listed_date = date('Y-m-d', strtotime($post_date));
                            $prev_week = date('Y-m-d');
                            $next_week = date('Y-m-d', strtotime('+ 7 days'));

                            $prev_month = date('Y-m-d', strtotime('previous month'));
                            if ($listed_date > $prev_month) {
                                $recently_listed_monthly_category = 'Recently Listed - Monthly';

                                wp_set_object_terms($post_id, $recently_listed_monthly_category, 'listing-category', true);
                            }
                        }
                    }
                    
                    
                 if(is_wp_error($agent_id)){
                     //echo "agentId issue";
                     $listAgentID = $user_id . '-Agent_';
                 } elseif(is_wp_error($user_id)){
                    //echo "user_id issue"; 
                    $listAgentID = '-Agent_' . $agent_id;
                 } else{
                     $listAgentID = $user_id . '-Agent_' . $agent_id;
                 }
                    
                    
                    
                    wp_set_object_terms($post_id, $listAgentID, 'listing-agents', true);

                    $listingTypeName = '';
                    $listingType3 = '';
                    $postCategory1 = '';
                    $postCategory2 = '';
                    if (isset($listItem->type)) {
                        $listingType2 = $listItem->type;
                        $listingTypeName = ucwords($listingType2->name);
                        $postCategory1 = $listingType2->name;
                        $propertyClass =  $listingType2->propertyClass;
                        $listingType3 = $propertyClass->name;
                        $postCategory2 = ucwords($propertyClass->name);
                        $terms_status = ucwords($listingType3);
                        
                    
                        
                        

                        wp_set_object_terms($post_id, ucwords($listingTypeName), 'listing-type', true);
                        wp_set_object_terms($post_id, ucwords($listingType3), 'listing-category', true);
                        
                        
                        if (in_array($listingType3, array("commercial", "commercialLand"))) {
                            
                            wp_set_object_terms($post_id, 'Commercial', 'listing-type', true);
                            
                        } else if ($listingType3 == 'business') {
                            
                            wp_set_object_terms($post_id, 'Business', 'listing-type', true);
                            
                        } else {
                            
                            wp_set_object_terms($post_id, ucwords($listingType3), 'listing-type', true);
                            
                        }
                        
                        
                        if ($listingTypeName == 'rental' || $listingType3 == 'rental') {
                            
                            $pcat = 'for-rent';
                            $pcat2 = 'rent';
                            wp_set_object_terms($post_id, $pcat2, 'listing-category', true);
                            wp_set_object_terms($post_id, $pcat, 'listing-category', true);
                            
                        } else {
                            
                            wp_set_object_terms($post_id, $listingTypeName, 'listing-category', true);
                            wp_set_object_terms($post_id, $listingType3, 'listing-category', true);
                            
                        }
                    }
                    $rental_category = array('rental', 'holidayrental');


                    //$_price_status = ( in_array(strtolower($listingType3), $rental_category) ) ? "rent" : "sale";
                    if ($classification == "residential" && $ptype == "sale") {
                        $propCat = 'residential';
                        $propCat2 = 'for-sale';
                        $_price_status = 'sale';
                        
                        
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat5, 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                       
                       
                    }
                    
                    if ($classification == "residential" && $ptype == "sold") {
                        $propCat = 'residential';
                        $propCat2 = 'for-sale';
                        $_price_status = 'sale';
                        
                        
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, "sold", 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        //wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                       
                       
                    }
                    
                    
                    elseif ($classification == "residential" && $ptype == "lease") {
                        $propCat = 'rent';
                        $propCat2 = 'for-rent';
                        $_price_status = 'rent';
                        $propCat3 = 'residential';
                        wp_set_object_terms($post_id, 'rental', 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                    }
                    
                    
                    elseif ($classification == "commercial" && $ptype == "sale") {
                        $propCat = 'sale';
                        $propCat2 = 'for-sale';
                        $_price_status = 'sale';
                        $propCat3 = 'commercial';
  
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                        wp_set_object_terms($post_id, 'commercial-sale', 'listing-category', true);
                    }
                    
                    elseif ($classification == "commercial" && $ptype == "sold") {
                        $propCat = 'sale';
                        $_price_status = 'sale';
                        $propCat3 = 'commercial';
  
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, "sold", 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                        
                    }
                    elseif ($classification == "commercial" && $ptype == "lease") {
                        $propCat = 'rent';
                        $propCat2 = 'for-rent';
                        $_price_status = 'rent';
                        $propCat3 = 'commercial';
                        
                        
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                        wp_set_object_terms($post_id, 'commercial-lease', 'listing-category', true);
                        
                    }
                    elseif ($classification == "commercial" && $ptype == "leased") {
                        $propCat = 'rent';
                        $propCat2 = 'leased';
                        $_price_status = 'rent';
                        
                        
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                   
                        
                    }
                    elseif ($classification == "commercial" && $ptype == "sale") {
                        $propCat = 'sale';
                        $propCat2 = 'for-sale';
                        $_price_status = 'sale';
                        $propCat3 = 'business';
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                    }
                    elseif ($classification == "rural" && $ptype == "lease") {
                        $propCat = 'rural';
                        $_price_status = 'sale';
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        //wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                    }
                    elseif ($classification == "land" && $ptype == "sale") {
                        $propCat = 'land';
                        $_price_status = 'sale';
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        //wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                    }
                    elseif ($classification == "holidayRental" && $ptype == "lease") {
                        $propCat = 'rent';
                        $propCat2 = 'for-rent';
                        $_price_status = 'rent';
                        $propCat3 = 'holidayrental';
                        wp_set_object_terms($post_id, 'rental', 'listing-category', true);
                        wp_set_object_terms($post_id, $_price_status, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat2, 'listing-category', true);
                        wp_set_object_terms($post_id, $propCat3, 'listing-category', true);
                    }
                    
                    
                    
                    $base_category = ucwords("For " . $_price_status);
                    $Category1 = ucwords($postCategory1);

                    $Category2 = ucwords($postCategory2);
                    $base_category_term = term_exists($base_category, 'listing-category');
                    $base1_category_term = term_exists($Category1, 'listing-category');
                    $base2_category_term = term_exists($Category2, 'listing-category');
                    if (empty($base_category_term['term_id'])) {
                        $base_category_term = wp_insert_term($base_category, 'listing-category');
                    }
                    if (empty($base1_category_term['term_id'])) {
                        $base1_category_term = wp_insert_term($Category1, 'listing-category');
                    }
                    if (empty($base2_category_term['term_id'])) {
                        $base2_category_term = wp_insert_term($Category2, 'listing-category');
                    }
                    
                    
                    wp_set_object_terms($post_id, $base_category, 'listing-category',true);
                    wp_set_object_terms($post_id, $base_category, 'listing-category',true);
                    wp_set_object_terms($post_id, $Category1, 'listing-category',true);
                    wp_set_object_terms($post_id, $Category2, 'listing-category',true);

                    if (isset($listItem->landArea)) {
                        $landUnit = $listItem->landArea;
                        $land_unit = $landUnit->units;
                        $land_area = $landUnit->value;
                    }
                    
                    if ($_price_status == "rent") {
                        $_price_label = "/week";
                        $_price_period = "/week";
                        $newpcat = "rent";
                        $newpcat2 = "for-rent";
                        wp_set_object_terms($post_id, $newpcat, 'listing-category', true);
                        wp_set_object_terms($post_id, $newpcat2, 'listing-category', true);
                    } 
                    
                    
                     else {
                         $newpcat = "sale";
                         $newpcat2 = "for-sale";
                    
                     }
                    
                    
                    $auction_date = array();
                    $auction_auctioneer = "";
                    $auction_venue = "";
                    $auction_week = '';
                    if (isset($listItem->auctionDetails)) {
                        $lvAuction = $listItem->auctionDetails;
                        if ($lvAuction->dateTime != 'null') {
                            $auction_date = $lvAuction->dateTime;
                            $auction_venue = $lvAuction->venue;
                            $auction_auctioneer = $lvAuction->auctioneer;
                        } else {
                            //$auction_date = array($lvAuction->dateTime);
                        }

                        if (!empty($auction_date[0])) {
                            $obj_date = new DateTime();
                            $cur_time = $obj_date->format("Y-m-d-H:i");
                            $auction_week = date('Y-m-d', strtotime($auction_date[0]));
                            if ($cur_time > $auction_date[0]) {
                                $auction_date = array();
                            }
                        }
                    }


                    if (isset($listItem->soiUrl)) {
                        $floor_plan = $listItem->soiUrl;
                        $rea_soi = $listItem->soiUrl;
                    }
                    $_refid = "";
                    if (!empty($listItem->refid)) {
                        $_refid = $listItem->referenceID;
                    }
                    $underoffer = '';
                    $cmListType = '';
                    if (isset($listItem->commercialListingType)) {
                        $cmListType = ucwords($listItem->commercialListingType);
                        wp_set_object_terms($post_id, $cmListType, 'listing-category', true);
                    }

                    $_price_sold_rented = 0;
                    $property_id = $uniqId;
                    update_post_meta($post_id, '_listing_sticky', $_price_status);
                    update_post_meta($post_id, '_listing_status', $_price_status);
                    update_post_meta($post_id, '_listing_not_available', 0);
                    update_post_meta($post_id, '_listing_title', $post_title);
                    update_post_meta($post_id, '_price_sold_rented', $_price_sold_rented);
                    update_post_meta($post_id, '_under_offer', $underoffer);
                    update_post_meta($post_id, '_listing_headline', $post_title);
                    update_post_meta($post_id, '_listing_refid', $_refid);
                    update_post_meta($post_id, '_price_period', $_price_period);
                    update_post_meta($post_id, '_mod_time', $listItem->modified);
                    update_post_meta($post_id, '_listing_id', $uniqId);
                    update_post_meta($post_id, '_details_agent_ids', $post_author);
                    update_post_meta($post_id, '_details_agent_emails', $agent_email);
                    update_post_meta($post_id, '_agent_email', $agent_email);
                    update_post_meta($post_id, '_property_id', $uniqId);
                    update_post_meta($post_id, '_price', $price);
                    update_post_meta($post_id, '_agent_name', $agent_name);
                    update_post_meta($post_id, '_agent_id', $agent_id);
                    update_post_meta($post_id, '_agent_company', $agent_company);
                    update_post_meta($post_id, '_agent_description', $agent_description);
                    update_post_meta($post_id, '_agent_phone', $agent_phone);
                    update_post_meta($post_id, '_agent_website', $agent_website);
                    update_post_meta($post_id, '_agent_twitter', $agent_twitter);
                    update_post_meta($post_id, '_agent_facebook', $agent_facebook);
                    update_post_meta($post_id, '_agent_instagram', $agent_instagram);
                    update_post_meta($post_id, '_agent_linkedin', $agent_linkedin);

                    update_post_meta($post_id, '_agent_logo', $agent_logo);
                    update_post_meta($post_id, '_details_1', $bedrooms);
                    update_post_meta($post_id, '_details_2', $bathrooms);
                    update_post_meta($post_id, '_details_3', $garages);
                    update_post_meta($post_id, '_details_4', $details_4);
                    update_post_meta($post_id, '_details_5', $details_5);
                    update_post_meta($post_id, '_details_6', $details_6);
                    update_post_meta($post_id, '_details_7', $land_area);
                    update_post_meta($post_id, '_details_8', $details_8);
                    update_post_meta($post_id, '_listing_detail_pool', $pools);
                    update_post_meta($post_id, '_listing_address_street_number', $laddress->streetNumber);
                    update_post_meta($post_id, '_listing_address_street_name', $laddress->street);
                    update_post_meta($post_id, '_map_address', $address);
                    update_post_meta($post_id, '_suburb', $suburb);
                    update_post_meta($post_id, '_price_period', $_price_period);
                    // Default Fields
                    update_post_meta($post_id, 'property_price', $price);
                    update_post_meta($post_id, 'property_area', $land_area);
                    update_post_meta($post_id, 'properties_area_post_text', $land_unit);
                    update_post_meta($post_id, 'property_bedrooms', $bedrooms);
                    update_post_meta($post_id, 'property_bathrooms', $bathrooms);
                    update_post_meta($post_id, 'property_garages', $garages);
                    update_post_meta($post_id, 'property_width', "reg");
                    update_post_meta($post_id, 'property_longitude', $longitude);
                    update_post_meta($post_id, 'property_price_view', $priceview);
                    update_post_meta($post_id, 'property_display_address', $address_display);
                    update_post_meta($post_id, 'property_street_address', $street_address);
                    update_post_meta($post_id, 'property_suburb', $suburb);
                    update_post_meta($post_id, 'property_postcode', $postcode);
                    update_post_meta($post_id, 'property_state', $stateName);
                    //update_post_meta($post_id, 'property_inspection_times', serialize($inspectiontimes));
                    //update_post_meta($post_id, 'property_auction_date', serialize($_auction_date));
                    update_post_meta($post_id, 'property_floor_plan', $floor_plan);
                    update_post_meta($post_id, 'property_video_url', $video_url);
                    update_post_meta($post_id, 'property_rea_soi', $rea_soi);
                    update_post_meta($post_id, '_details_rea_soi', $rea_soi);
                    update_post_meta($post_id, '_listing_soi', $rea_soi);
                    update_post_meta($post_id, '_listing_reference_id', $_refid);
                    update_post_meta($post_id, '_noo_property_under_offer', 'no');
                    $location = array(
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    );
                    $lat_lng = $latitude . "," . $longitude;

                    update_post_meta($post_id, '_geolocation_lat', $latitude);
                    update_post_meta($post_id, '_geolocation_long', $longitude);

                    update_post_meta($post_id, '_map_geo', $lat_lng);
                    update_post_meta($post_id, '_map_location', $lat_lng);
                    update_post_meta($post_id, '_map_note', "");
                    update_post_meta($post_id, '_map_secret', "");
                    update_post_meta($post_id, 'property_latitude', $latitude);
                    update_post_meta($post_id, 'agent_display', "agent");
                    update_post_meta($post_id, 'agent_select', $uAgentname);
                    update_post_meta($post_id, '_details_street_address', $street_address);
                    update_post_meta($post_id, 'property_contract', $property_contract);
                    update_post_meta($post_id, 'property_sold', $property_sold);
                    update_post_meta($post_id, 'property_id', $property_id);
                    update_post_meta($post_id, 'property_address', $address);
                    update_post_meta($post_id, '_details_adrees_display', $address_display);
                    update_post_meta($post_id, '_details_adrees_suburb', $suburb);
                    update_post_meta($post_id, '_details_adrees_state', $stateName);
                    update_post_meta($post_id, '_details_adrees_postcode', $postcode);
                    update_post_meta($post_id, 'property_agents', $agent_id);
                    update_post_meta($post_id, 'property_under_offer', 'no');
                    update_post_meta($post_id, 'property_featured', $featured);
                    update_post_meta($post_id, 'property_sticky', '');
                    update_post_meta($post_id, 'property_reduced', '');
                    update_post_meta($post_id, 'property_exclusivity', '');
                    update_post_meta($post_id, '_agent_responsible_ids', $agent_id);
                    update_post_meta($post_id, '_agent_responsible_emails', $agent_email);
                    update_post_meta($post_id, 'property_map_location', $location);
                    update_post_meta($post_id, 'property_map_location_latitude', $latitude);
                    update_post_meta($post_id, 'property_map_location_longitude', $longitude);
                    update_post_meta($post_id, '_price_offer', $_price_status);
                    update_post_meta($post_id, '_price_status', $_price_status);

                    if (!empty($itemsPhotos)) {
                        new_scm_set_listing_images($itemsPhotos, $post_id, $l_id, $post_author, true);
                        
                        
                    }
                } else {
                    if ($my_query->have_posts()) {
                        while ($my_query->have_posts()) :
                            $my_query->the_post();
                            //$availablePost[] = $uniqId;
                            $post_data_id = get_the_ID();
                            $listing_status = $_price_status = $property_contract = $property_sold = $property_id = $featured = $_price_offer = $_price_period = $a_post_id = $priceview = $land_area = $land_unit = $details_1 =  $details_2 =  $details_3 = $details_4 =  $details_5 =  $details_6 =  $details_7 =  $details_8 =  $floor_plan =  $video_url = $pools = '';
                            $rea_soi = $latitude =  $longitude =  $address_display = $listingType = '';
                            $bedrooms = $garages =  $toilets = $bathrooms = $carports = $price = 0;
                            $user_id = $uAgentname = $agent_logo = $agent_name = $agent_company = $agent_description = $agent_phone = $agent_website = $agent_twitter = $agent_facebook = $agent_instagram = $agent_email = $agent_linkedin = '';

                            $a_agents = array();

                            foreach ($agentList as $lAgent) {
                                $a_uargs = array(
                                    'role__in' => array('listing_agent'),
                                    'meta_key' => '_agent_id',
                                    'meta_value' => $lAgent->id,
                                    'fields' => 'ID'
                                );
                                //print_r($lAgent);
                                $uAgentname = $lAgent->username;
                                $lagentFirst = $lAgent->firstName;
                                $lagentLast = $lAgent->lastName;
                                $agent_name = $lAgent->username;
                                $agent_id = $lAgent->id;
                                $agent_logos = $lAgent->photo;
                                $agent_logo = $agent_logos->original;
                                $agent_website = $lAgent->websiteUrl;
                                //$agent_description = $lAgent->name;
                                //$agent_company = $lAgent->name;
                                //$agent_facebook = $lAgent->social_facebook;
                                //$agent_instagram = $lAgent->social_pinterest;
                                //$agent_twitter = $lAgent->social_twitter;
                                //$agent_linkedin = $lAgent->social_linkedin;
                                $agent_email = $lAgent->email;
                                $agemt_data = get_users($a_uargs);
                                if (!empty($agemt_data)) {
                                    foreach ($agemt_data as $a_user) {
                                        $user_id = $a_user;
                                        break;
                                    }
                                } else {
                                    if ($user_id == '') {
                                        $random_password = wp_generate_password(12, false);
                                        $username =  sanitize_title($lAgent->username);
                                        $agent_email = $lAgent->email;
                                        $aEmail = $lAgent->email;

                                        $phoneNumbers = $lAgent->phoneNumbers;
                                        $aPhone = '';
                                        $aMobile = '';
                                        if (!empty($phoneNumbers)) {
                                            foreach ($phoneNumbers as $phoneNumber) {
                                                if ($phoneNumber->type == 'Mobile') {
                                                    $aMobile = $phoneNumber->number;
                                                } else {
                                                    $aPhone = $phoneNumber->number;
                                                }
                                            }
                                        }
                                        $getuser = get_user_by('email', $aEmail);
                                        if (empty($getuser)) {
                                            $aName = $lAgent->username;

                                            $agent_role = "listing_agent";
                                            $a_post_post_type = "agent";
                                            $a_post_data = [
                                                'post_title' => $lAgent->username,
                                                'post_name' => $lAgent->username,
                                                'post_status' => 'publish',
                                                'post_type' => $a_post_post_type,
                                                'post_author' => 1,
                                                'comment_status' => 'closed'
                                            ];
                                            $userdata = array(
                                                'user_login' => $username,
                                                'user_url' => '',
                                                'user_pass' => $random_password,
                                                'user_email' => $aEmail,
                                                'display_name' => $aName,
                                                'nickname' => $aName,
                                                'first_name' => $aName,
                                                'role' => $agent_role
                                            );

                                            $user_id = wp_insert_user($userdata);
                                        } else {
                                            $user_id = $getuser->ID;
                                        }
                                        update_user_meta($user_id, '_agent_id', $lAgent->id);
                                        update_user_meta($user_id, 'mobile', $aMobile);
                                        update_user_meta($user_id, 'phone', $aPhone);
                                        update_user_meta($user_id, 'phone_ah', $aTphone);
                                        update_user_meta($user_id, 'sort_order', 0);
                                        $a_post_data['post_content'] = "";
                                        $a_post_id = wp_insert_post($a_post_data);
                                        update_post_meta($a_post_id, '_thumbnail_id', $api_re_author_image_id);
                                        update_post_meta($a_post_id, 'agent_phone', $aMobile);
                                        update_post_meta($a_post_id, 'agent_phone_bh', $aPhone);
                                        update_post_meta($a_post_id, 'agent_phone_ah', $aTphone);
                                        update_post_meta($a_post_id, 'agent_email', $aEmail);
                                        update_post_meta($a_post_id, 'display_agent', 1);
                                        $_agent['user_id'] = $user_id;
                                    }
                                }
                            }

                            $post_title   = $listItem->heading;
                            $laddress = $listItem->address;
                            $suburbList = $laddress->suburb;
                            $suburb = $suburbList->name;
                            $postcode = $suburbList->postcode;
                            $lstatesList = $laddress->state;
                            $stateName = $lstatesList->name;
                            $address = str_replace('/', '-', $laddress->streetNumber . " " . $laddress->street . " " . $suburb . " " . $stateName  . " " . $postcode);
                            if (!empty($laddress->unitNumber)) {
                                $street_address = $laddress->unitNumber . '/' . $laddress->streetNumber . ' ' . $laddress->street;
                            } else {
                                $street_address = $laddress->streetNumber . ' ' . $laddress->street;
                            }
                            $geolocations = $listItem->geolocation;
                            if (isset($geolocations->latitude)) {
                                $latitude = $geolocations->latitude;
                            } else {
                                $latitude = $geolocations->latitude;
                            }
                            if (isset($listItem->searchPrice)) {
                                $price = $listItem->searchPrice;
                            }
                            if (isset($listItem->displayPrice)) {
                                $priceview = $listItem->displayPrice;
                            }
                            if (isset($laddress->display)) {
                                $address_display = $laddress->display;
                            }
                            if (isset($geolocations->longitude)) {
                                $longitude = $geolocations->longitude;
                            } else {
                                $longitude = $geolocations->longitude;
                            }
                            $listing_permalink = strtolower($uniqId . '-' . $address);
                            $l_id = $listItem->id;
                            $post_desc   = $listItem->description;
                            $lpost_type = 'listing';
                            $post_author = $user_id;
                            $post_data = [
                                'post_title'     => $post_title,
                                'post_content'   => $post_desc,
                                'post_name'      => $listing_permalink,
                                'post_status'    => 'publish',
                                'post_type'      => $lpost_type,
                                'post_author'    => $post_author,
                                'comment_status' => 'closed'
                            ];
                            $post_data['ID'] = $post_data_id;
                            $post_data['post_date'] = date('Y-m-d H:i:s', strtotime($listItem->modified));
                            $post_date = date('Y-m-d H:i:s', strtotime($listItem->modified));
                            $post_data['post_date_gmt'] = $post_data['post_date'];
                            $post_id =  wp_update_post($post_data, true);
                            array_push($updatedPosts, $listing_permalink);


                            $_price_sold_rented = 0;
                            $property_id = $uniqId;
                            if (isset($listItem->bed)) {
                                $bedrooms = $listItem->bed;
                            }
                            if (isset($listItem->bath)) {
                                $bathrooms = $listItem->bath;
                            } else {
                                $bathrooms = 1;
                            }
                            if (isset($listItem->toilets)) {
                                $toilets = $listItem->toilets;
                            }
                            if (isset($listItem->garages)) {
                                $garages = $listItem->garages;
                            } else {
                                $garages = 1;
                            }
                            if (isset($listItem->carports)) {
                                $carports = $listItem->carports;
                            }
                            if (isset($listItem->soiUrl)) {
                                $soiUrl = $listItem->soiUrl;
                            }

                            if (isset($listItem->commercialListingType)) {
                                $listingType = $listItem->commercialListingType;
                            }
                            if (isset($listItem->addressVisibility)) {
                                $addressVisibility = $listItem->addressVisibility;
                            }
                            if (isset($listItem->displayAddress)) {
                                $displayAddress = $listItem->displayAddress;
                            }



                            if (isset($listItem->soiUrl)) {
                                $floor_plan = $listItem->soiUrl;
                                $rea_soi = $listItem->soiUrl;
                            }
                            $_refid = "";
                            if (!empty($listItem->refid)) {
                                $_refid = $listItem->referenceID;
                            }
                            $underoffer = '';
                            $cmListType = '';
                            if (isset($listItem->commercialListingType)) {
                                $cmListType = $listItem->commercialListingType;
                                wp_set_object_terms($post_id, $cmListType, 'listing-category', true);
                            }

                            $_price_sold_rented = 0;
                            $property_id = $uniqId;

                            update_post_meta($post_id, '_listing_not_available', 0);
                            update_post_meta($post_id, '_listing_title', $post_title);
                            update_post_meta($post_id, '_price_sold_rented', $_price_sold_rented);
                            update_post_meta($post_id, '_under_offer', $underoffer);
                            update_post_meta($post_id, '_listing_headline', $post_title);
                            update_post_meta($post_id, '_mod_time', $listItem->modified);
                            update_post_meta($post_id, '_listing_id', $uniqId);
                            update_post_meta($post_id, '_details_agent_ids', $post_author);
                            update_post_meta($post_id, '_details_agent_emails', $agent_email);
                            update_post_meta($post_id, '_agent_email', $agent_email);
                            update_post_meta($post_id, '_property_id', $uniqId);
                            update_post_meta($post_id, '_price', $price);
                            update_post_meta($post_id, '_map_address', $address);
                            update_post_meta($post_id, '_suburb', $suburb);
                            // Default Fields
                            update_post_meta($post_id, 'property_price', $price);
                            update_post_meta($post_id, 'property_street_address', $street_address);
                            update_post_meta($post_id, 'property_suburb', $suburb);
                            update_post_meta($post_id, 'property_postcode', $postcode);
                            update_post_meta($post_id, 'property_state', $stateName);
                            //update_post_meta($post_id, 'property_inspection_times', serialize($inspectiontimes));
                            //update_post_meta($post_id, 'property_auction_date', serialize($_auction_date));
                            if (!empty($itemsPhotos)) {
                                new_scm_set_listing_images($itemsPhotos, $post_id, $l_id, $post_author, true);
                            }



                        endwhile;
                        wp_reset_postdata();
                    }
                
            
        }

    
return array("newPosts" => $createdPosts, "newAgents" => $createdAgents, "updatedPosts" => $updatedPosts);
}


function new_scm_set_listing_images($images, $post_id, $l_id, $post_author, $rest = false) {
	if (empty($post_id) || empty($images) || empty($l_id))
		return false;

	if (empty($post_author)) {
		$post_author = 1;
	}

	$_gallery = array();

	$api_re_image_url = $backup_api_re_image_url = get_option('wb_realestate_image_url');
	$api_re_template = get_option('wb_realestate_template');

	$image_url = '';
	$i = 0;
	foreach ($images as $image) {
		$api_re_image_url = $backup_api_re_image_url;
		$img_exist = false;
		$img_url = $image->url;


			$_gallery[] = $img_url;
			
			
			if($i == 0){
			
		
			update_post_meta($post_id, '_thumbnail_id', $img_url);
				
				}
		
			

		$i++;
	}
	if (!empty($_gallery)) {
			update_post_meta($post_id, 'additional_img', implode(",", $_gallery));
		}
	update_field('estate_property_gallery', $_gallery, $post_id);
		update_post_meta($post_id, 'property_gallery', $_gallery);
		update_post_meta($post_id, '_gallery', $_gallery);	

	unset($images, $post_id, $l_id, $post_author, $_gallery, $img_sizes, $attach_data, $i_query, $wp_filetype, $post_img_data, $iargs);
}



function new_listing_ApiData($save = 1)
{
     $api_key = get_option('api_key_option', ''); // Retrieve the API key from options
    $bearer_token = get_option('bearer_token_option', ''); // Retrieve the bearer token from options
    $base_url = 'https://ap-southeast-2.api.vaultre.com.au/api/v1.3';
    
    $endpoints = get_option('enabled_fetch_types', array());
    
   

    
    $createdPosts = array();
    $createdAgents = array();
    $updatedPosts = array();
    
    
    foreach ($endpoints as $endpoint) {
         $endpoint = explode('-', $endpoint);

    
        $classification = $endpoint[0];
        $salelease = $endpoint[1];
        $page = 1; // Start with page 1
          do {
            
            if($salelease == "sold"){
                $next_url = "/properties/{$classification}/sale/sold?published=true&pagesize=50&page={$page}";
            } elseif($salelease == "leased") {
                $next_url = "/properties/{$classification}/lease/leased?published=true&pagesize=50&page={$page}";
            } else {
                $next_url = "/properties/{$classification}/{$salelease}/available?published=true&status=listing&pagesize=50&page={$page}";
            }
            // Construct the URL with pagination parameters
            
            $endpoint_url = $base_url . $next_url;


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $endpoint_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'X-Api-Key: '.$api_key,
                    'Authorization: Bearer '.$bearer_token
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $decoded_response = json_decode($response);
             if($salelease == "sold"){
                 $decoded_response->ptypes = (object)array($classification => "sold");
             } else {
                 $decoded_response->ptypes = (object)array($classification => $salelease);
             }
            
            // Add the response to the listings array
   
            
            foreach($decoded_response->items as $listItem){
                $result = @scm_property_listing_settings_page($listItem, $classification, $salelease);
                
                if($result["newPosts"][0] !== null){
                    array_push($createdPosts, $result["newPosts"][0]);
                }
                
                if($result["newAgents"][0] !== null){
                    array_push($createdAgents, $result["newAgents"][0]);
                }
                
                if($result["updatedPosts"][0] !== null){
                    array_push($updatedPosts, $result["updatedPosts"][0]);
                }
                
          
            }

            // Check if there's a next page URL
            if (isset($decoded_response->urls->next)) {
                $next_url = $decoded_response->urls->next;
            } else {
                $next_url = null; // No more pages
            }

            $page++; // Move to the next page
         } while ($next_url !== null); // Continue until there are no more pages
    }
    
    
    $current_datetime = date('Y-m-d H:i:s');

     update_option("wpcasa_last_update", $current_datetime);
    
    
      if($save == 1){
 
        wp_send_json(array("status" => "completed", "data" => array("newPosts" => $createdPosts, "newAgents" => $createdAgents, "updatedPosts" => $updatedPosts)));
       
    } else {
         return array("newPosts" => $createdPosts, "newAgents" => $createdAgents, "updatedPosts" => $updatedPosts);
    } 
     error_log( 'Feed Ran at : '.$current_datetime  );
    wp_die();
}



function add_update_post_meta($post_id = null, $key = null, $value = "") {
	if ( is_string($value) ) {
		$value = trim($value);
	}
	if (!add_post_meta($post_id, $key, $value, true)) {
		update_post_meta($post_id, $key, $value);
	}
}


