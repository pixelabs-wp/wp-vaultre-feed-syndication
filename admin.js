jQuery(document).ready(function($) {
    var tbOptions = {
        modal: true, // Make the Thickbox modal
        width: 400,
        height: 200,
        inlineId: 'fetch-listings-form'
    };

    // Click event handler for the custom button
    $('#fetch-listings-button').on('click', function(e) {
        e.preventDefault(); // Prevent the default link behavior

        // Open the Thickbox
        tb_show('Fetching Property Listings', '#TB_inline', null, tbOptions);

        // Display loader and "Please wait" text
        var loaderHtml = '<div class="loader"></div>';
        var pleaseWaitText = '<p class="please-wait-text">Please wait...</p>';
        $('#TB_ajaxContent').html(loaderHtml + pleaseWaitText);

        // Make an AJAX request
        $.ajax({
            type: 'POST',
            url: customAdminData.ajaxurl,
            data: {
                action: 'call_property_listing_settings'
            },
            success: function(response) {
                // Handle the response
                console.log('AJAX response:', response);
                // You can display the response in the Thickbox or take any other action
                setTimeout(function() {
                    // Close the Thickbox after a delay
                    tb_remove();
                    alert("Update Done");
                    window.location.reload();
                }, 3000); // Adjust the delay as needed (3 seconds in this example)
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX request failed: ' + textStatus, errorThrown);
            }
        });
    });
});
