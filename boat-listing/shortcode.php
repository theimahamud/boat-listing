<?php

    function book_now_modal_shortcode(){

        ?>
        
            <!-- Modal Placeholder -->
            <div class="modal fade boatBookingModal" id="boatBookingModal" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content" style="min-height:300px">
                        
                    </div>
                </div>
            </div>

        <?php
    }

    add_shortcode( 'book_now_modal_shortcode', 'book_now_modal_shortcode' );

?>