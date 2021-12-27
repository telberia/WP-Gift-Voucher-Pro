document.addEventListener("DOMContentLoaded", function(event) {
    // console.log('tttttttttttttttttttttttttttt');
    jQuery('form.variations_form').on('show_variation', function() {
        jQuery('#wpgv-purchase-container').show();
    });
});