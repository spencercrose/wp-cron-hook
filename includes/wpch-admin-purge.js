(function( $ ) {
  'use strict';

  /**
   * Admin AJAX form handlers.
   */

  $(function() {

    // update form schema
    $("li#wp-admin-bar-wpch_purge_cache .ab-item")
      .on('click', function(e){
        $.ajax({
          type:"GET",
          url: wpch_local_obj.purge_url,
          beforeSend: function (xhr) {
    				xhr.setRequestHeader( 'X-WP-Nonce', wpch_local_obj.purge_nonce)
    			},
          success: function(response){
            console.log('Response:', response);
            alert("Post has been cleared from the cache!");
          },
          error: function(response){
            console.warn('Error:', response);
          }
        });
      });
  });

})( jQuery );
