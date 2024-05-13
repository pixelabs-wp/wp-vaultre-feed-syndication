<?php

function add_custom_button_to_listing_post_type() {
    global $post_type;
    // Check if you're on the post listing page for the 'listing' post type
    if ($post_type == 'listing') {
        echo '<div class="alignleft actions custom-button"><a href="#" class="button" id="fetch-listings-button">Fetch Property Listings</a></div>';
    }
}
add_action('manage_posts_extra_tablenav', 'add_custom_button_to_listing_post_type');




function enqueue_custom_admin_scripts() {
    // Enqueue your custom JavaScript file
    wp_enqueue_script('custom-admin', 'https://www.createvic.com.au/wp-content/plugins/wb_realestate_child/admin.js?ver=1.1', array('jquery'), '1.1', true);

    // Localize the ajaxurl for your script
    wp_localize_script('custom-admin', 'customAdminData', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));
    
     wp_enqueue_script('thickbox');
        wp_enqueue_style('thickbox');
        
            wp_enqueue_style('custom-thickbox', 'https://www.createvic.com.au/wp-content/plugins/wb_realestate_child/my.css', array(), '1.0', 'all');

}

add_action('admin_enqueue_scripts', 'enqueue_custom_admin_scripts');



// Define the fetch types
$endpoints = array(
    ["residential", "sale"],
    ["residential", "sold"],
    ["residential", "lease"],
    ["commercial", "sale"],
    ["commercial", "lease"],
    ["commercial", "sold"],
    ["commercial", "leased"],
    ["rural", "sale"],
    ["business", "sale"],
    ["land", "sale"],
    ["holidayRental", "lease"],
);

// Add a menu item in the admin panel
function fetch_types_menu() {
    add_menu_page(
        'Fetch Types',
        'Property Feed',
        'manage_options',
        'fetch-types',
        'fetch_types_page'
    );
}

// Callback function to display the fetch types settings page
function fetch_types_page() {
    global $endpoints;

       // Check if the API key and bearer token are submitted
    if (isset($_POST['update_settings'])) {
        $enabled_types = isset($_POST['enabled_types']) ? $_POST['enabled_types'] : array();
        update_option('enabled_fetch_types', $enabled_types);

        // Save the API key and bearer token to WordPress options
        $api_key = sanitize_text_field($_POST['api_key']);
        $bearer_token = sanitize_text_field($_POST['bearer_token']);
        update_option('api_key_option', $api_key);
        update_option('bearer_token_option', $bearer_token);

        echo '<div class="updated"><p>Settings updated</p></div>';
    }

    $enabled_types = get_option('enabled_fetch_types', array());
    $api_key = get_option('api_key_option', ''); // Retrieve the API key from options
    $bearer_token = get_option('bearer_token_option', ''); // Retrieve the bearer token from options

    echo '<div class="wrap">';
    echo '<h2>Fetch Types</h2>';
    echo '<form method="post">';
    echo '<h3>Enabled Fetch Types</h3>';
    foreach ($endpoints as $type) {
        $type_key = implode('-', $type);
        $checked = in_array($type_key, $enabled_types) ? 'checked' : '';
        echo '<label>';
        echo "<input type='checkbox' name='enabled_types[]' value='$type_key' $checked>";
        echo "{$type[0]} - {$type[1]}";
        echo '</label><br>';
    }

    // Add input fields for API key and bearer token
    echo '<h3>API Settings</h3>';
    echo '<label for="api_key">API Key:</label>';
    echo '<input type="password" name="api_key" id="api_key" value="' . esc_attr($api_key) . '"><br>';
    echo '<label for="bearer_token">Bearer Token:</label>';
    echo '<input type="password" name="bearer_token" id="bearer_token" value="' . esc_attr($bearer_token) . '"><br>';

    echo '<p><input type="submit" name="update_settings" class="button button-primary" value="Update Settings"></p>';
    echo '</form>';
    echo '</div>';

    echo '<hr><h2 style="width:100%;">Feed <small style="text-align:right"> Last Updated: '.get_option("wpcasa_last_update").'</small></h2>';
    echo '<button id="call-settings-page-button" class="button button-primary">Load Property Listings</button>';
// Within your 'fetch_types_page' function, add the progress bar and percentage display container
echo '<div id="progress-container" style="display: none;">';
echo '<div id="progress-bar" style="width: 0%;"></div>';
echo '<div id="progress-percentage">0%</div>';
echo '</div>';
    
    // Within your 'fetch_types_page' function, add a loading message container
echo '<div id="loading-message" style="display: none;">Loading data...</div>';


    echo '<div id="log-container"></div>';
    ?>
    
    <script>
    var css = `#log-container h3 {
    font-size: 18px;
    margin-bottom: 10px;
}

#log-container pre {
    background-color: #f5f5f5;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
}
`,
    head = document.head || document.getElementsByTagName('head')[0],
    style = document.createElement('style');

head.appendChild(style);

style.type = 'text/css';
if (style.styleSheet){
  // This is required for IE8 and below.
  style.styleSheet.cssText = css;
} else {
  style.appendChild(document.createTextNode(css));
}


jQuery(document).ready(function($) {
    $('#call-settings-page-button').on('click', function(e) {
        e.preventDefault();
        $('#loader').show(); // Show the loader
        $('#loading-message').show(); // Show the loading message

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'call_property_listing_settings', // The AJAX action
            },
            success: function(response) {
                $('#loader').hide(); // Hide the loader
                $('#loading-message').hide(); // Hide the loading message
         
                    displayJsonLog(response);
                
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#loader').hide(); // Hide the loader
                $('#loading-message').hide(); // Hide the loading message
                console.error('AJAX request failed: ' + textStatus, errorThrown);
            },
        });
    });
});




    
function displayJsonLog(jsonData) {
   
    let logContainer = document.querySelector("#log-container");
    logContainer.innerHTML = ''; // Clear the previous log

    // Group data by category
    let dataByCategory = {
        'New Properties': jsonData.data.newPosts,
        'Updated Properties': jsonData.data.updatedPosts,
        'New Agents': jsonData.data.newAgents
    };

    // Define colors for each category
    let categoryColors = {
        'New Properties': 'black',
        'New Agents': 'black',
        'Updated Posts': 'black'
    };
 console.log(dataByCategory);
   // Create a single pre element for all data


for (let category in dataByCategory) {
    let categoryHeader = '<h3 style="color: ' + categoryColors[category] + '">' + category + '</h3>';
    logContainer.innerHTML += categoryHeader;

    if (Array.isArray(dataByCategory[category])) {
        dataByCategory[category].forEach((item) => {
            // Create a text node for each item and append it to the pre element
            logContainer.innerHTML += '<pre>'+item+'</pre>';
        });
    }
}




    alert("Fetch Complete");
}



    </script>
    <?php
}

// Hook into the admin menu
add_action('admin_menu', 'fetch_types_menu');


function set_term_custom($post_id, $taxonomy, $term_slug) {
      global $wpdb;

    // Get the term ID by slug
    $term_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT term_id FROM {$wpdb->terms} WHERE slug = %s",
            $term_slug
        )
    );

    if ($term_id) {
        // Check if the term already exists for the post
        $existing_term = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = %s",
                $term_id,
                $taxonomy
            )
        );

        if (!$existing_term) {
            // Insert a new term relationship for the post
            $result = $wpdb->insert(
                $wpdb->term_relationships,
                array(
                    'object_id' => $post_id,
                    'term_taxonomy_id' => $term_id,
                )
            );

            if ($result === false) {
                echo "Error inserting term relationship: " . $wpdb->last_error;
            }

            // Update term count
            $count_updated = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->term_taxonomy} SET count = count + 1 WHERE term_id = %d",
                    $term_id
                )
            );

            if ($count_updated === false) {
                echo "Error updating term count: " . $wpdb->last_error;
            }
        } else {
            echo "Term already exists for the post.";
        }
    } else {
        echo "Term with slug '{$term_slug}' not found.";
    }
}

