<?php

add_shortcode('boat_details', 'render_single_boat_by_query');

function render_single_boat_by_query() {
    $helper = new Boat_Listing_Helper();

    $boat_id = (string) sanitize_text_field($_GET['id']);

    $current_year = date('Y');
    $date_from = sanitize_text_field($_GET['dateFrom'] ?? $current_year . '-01-01'); // Jan 1 current year
    $date_to   = sanitize_text_field($_GET['dateTo'] ?? $current_year . '-12-31');   // Dec 31 current year

    $boat_data = $helper->fetch_all_boats( $boat_id );

    $prices = $helper->get_single_yacht_price_details($boat_id, $date_from, $date_to);

    if (empty($boat_id)) {
        return '<p>No boat ID provided.</p>';
    }

    foreach( $boat_data as $data);

    ob_start();
    ?>
    <div class="single-boat-details">

        <div class="single-boat-img">
            <div class="bl-slick-slider">
                <?php if (!empty($data['images'])): ?>
                    <?php foreach ($data['images'] as $img): ?>
                        <?php if (!empty($img['url'])): ?>
                            <div>
                                <a href="<?php echo esc_url($img['url']); ?>" data-fancybox="gallery">
                                    <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div>
                        <img src="<?php echo esc_url(plugins_url('assets/no-image.jpg', __FILE__)); ?>" alt="No image">
                    </div>
                <?php endif; ?>
            </div>
            <div class="bl-slick-slider-nav">
                <?php if (!empty($data['images'])): ?>
                    <?php foreach ($data['images'] as $img): ?>
                        <?php if (!empty($img['url'])): ?>
                            <div>
                                <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="boat-details">
            <h4>
                <?php
                echo esc_html($data['name']);

                if (!empty($data['homeBase'])) {
                    echo ' in <span class="location"><i class="ri-map-pin-line"></i> ' . esc_html($data['homeBase']) . '</span>';
                }
                ?>
            </h4>
            <div class="company-info info-card">
                <h5>Yacht Information</h5>
                <ul>
                    <li>
                        <span><?php _e('Price:', 'boat-listing'); ?></span>
                        <?php
                        if ($prices) {
                            echo esc_html($prices['min'] . ' to ' . $prices['max'] . ' ' . $prices['currency']);
                        } else {
                            echo 'N/A';
                        }
                        ?>
                        <a href="#yachtPrice" class="open-tab-link">View Full List</a>
                    </li>
                    <li><span><?php _e('Yacht Type:', 'boat-listing'); ?></span> <?php echo !empty($data['kind']) ? esc_html($data['kind']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Cabins:', 'boat-listing'); ?></span> <?php echo !empty($data['cabins']) ? esc_html($data['cabins']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Yacht Age:', 'boat-listing'); ?></span> <?php echo !empty($data['year']) ? esc_html($data['year']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Beam (Feet):', 'boat-listing'); ?></span> <?php echo !empty($data['beam']) ? esc_html($data['beam']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Engine(s):', 'boat-listing'); ?></span> <?php echo !empty($data['engine']) ? esc_html($data['engine']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Engine(s) Power:', 'boat-listing'); ?></span> <?php echo !empty($data['engine']) ? esc_html($data['engine']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Fuel Capacity:', 'boat-listing'); ?></span> <?php echo !empty($data['fuelCapacity']) ? esc_html($data['fuelCapacity']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Length (Feet):', 'boat-listing'); ?></span> <?php echo !empty($data['length']) ? esc_html($data['length']) : __('N/A', 'boat-listing'); ?> </li>
                </ul>
            </div>
           
            <button class="boat-book-now" data-id="<?php echo $boat_id; ?>">Request to book</button>
        </div>
    </div>

    <div class="bl-single-full-boat-details">
        <div class="details">

            <div class="title">Details</div>

            <div class="features">

                <div class="yachtdeails-info">
                    <div class="f-title">Features</div>
                    <ul>

                        <li><span><?php _e('Yacht Type:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['kind'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Model:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['model'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Yacht Age:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['year'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Max Persons:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['maxPeopleOnBoard'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Cabins:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['cabins'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Total Showers / WC:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['wc'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Berths (Total):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['berths'] ?? 'N/A'); ?>
                        </li>
                        
                    </ul>
                </div>

                <div class="layout yachtdeails-info">
                    <div class="f-title">Layout</div>
                    <ul>
                        <li>
                            <span><?php _e('Yacht Layout:', 'boat-listing'); ?></span>
                            <?php
                            if (!empty($data['images'])) {
                                foreach ($data['images'] as $img) {
                                    if (!empty($img['url']) && stripos($img['name'], 'layout') !== false) {
                                        echo '<a href="' . esc_url($img['url']) . '" target="_blank">
                            <img src="' . esc_url($img['url']) . '" style="max-width:60px;margin:5px;">
                            </a>';
                                    }
                                }
                            }
                            ?>
                        </li>

                        <li><span><?php _e('Yacht Length (m):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['length'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Beam (m):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['beam'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Draft (m):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['draught'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Engine(s):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['engine'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Fuel Capacity:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['fuelCapacity'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Water Capacity:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['waterCapacity'] ?? 'N/A'); ?>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="location">
            <div class="title">Location</div>

            <div class="l-features">

                <ul>
                    <li>
                        <span><?php _e('Check In Time:', 'boat-listing'); ?></span>
                        <?php echo !empty($data['defaultCheckInTime']) ? esc_html($data['defaultCheckInTime']) : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php _e('Check Out Time:', 'boat-listing'); ?></span>
                        <?php echo !empty($data['defaultCheckOutTime']) ? esc_html($data['defaultCheckOutTime']) : __('N/A', 'boat-listing'); ?>
                    </li>
                    <li>
                        <span><?php _e('Location:', 'boat-listing'); ?></span>
                        <?php echo !empty($data['homeBase']) ? esc_html($data['homeBase']) : __('N/A', 'boat-listing'); ?>
                    </li>
                </ul>

                <div class="boat-map">
                    <?php
                    $homeBase = urlencode((string) ($data['homeBase'] ?? ''));
                    ?>

                    <iframe
                            src="https://maps.google.com/maps?q=<?php echo esc_attr($homeBase); ?>&z=14&output=embed"
                            width="100%"
                            height="200"
                            style="border:0;"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

        <div class="price">
            <div class="title">Price Info</div>
            <div class="yachtPrice">
                <?php if (!empty($prices['rows'])): ?>
                    <table class="table table-stripe">
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Price</th>
                            <th>Currency</th>
                        </tr>

                        <?php foreach ($prices['rows'] as $row): ?>
                            <tr>
                                <td>
                                    <?php echo esc_html(date('Y-m-d', strtotime($row['dateFrom']))); ?>
                                </td>
                                <td>
                                    <?php echo esc_html(date('Y-m-d', strtotime($row['dateTo']))); ?>
                                </td>
                                <td><?php echo esc_html($row['price']); ?></td>
                                <td><?php echo esc_html($row['currency']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>Price not available</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- <div class="boat-listing-tab">
        <ul>
            <li><a href="#yachtBase">Yacht Base</a></li>
            <li><a href="#yachtDetails">Yacht Details</a></li>
            <li><a href="#yachtPrice">Yacht Prices</a></li>
        </ul>

        <div id="yachtBase">
            <div class="yachtBase">
                <ul>
                    <li>
                        <span><?php //_e('Check In Time:', 'boat-listing'); ?></span>
                        <?php //echo !empty($data['defaultCheckInTime']) ? esc_html($data['defaultCheckInTime']) : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php //_e('Check Out Time:', 'boat-listing'); ?></span>
                        <?php //echo !empty($data['defaultCheckOutTime']) ? esc_html($data['defaultCheckOutTime']) : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php //_e('Disabled:', 'boat-listing'); ?></span>
                        <?php //echo isset($data['disabled']) ? esc_html($data['disabled'] ? 'Yes' : 'No') : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php //_e('Base Delay Note:', 'boat-listing'); ?></span>
                        <?php //echo !empty($data['comment']) ? esc_html($data['comment']) : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php //_e('Return To Base Note:', 'boat-listing'); ?></span>
                        <?php // !empty($data['comment']) ? esc_html($data['comment']) : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php //_e('Secondary Base:', 'boat-listing'); ?></span>
                        <?php //echo !empty($data['secondaryBase']) ? esc_html($data['secondaryBase']) : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php //_e('Location:', 'boat-listing'); ?></span>
                        <?php //echo !empty($data['homeBase']) ? esc_html($data['homeBase']) : __('N/A', 'boat-listing'); ?>
                    </li>
                </ul>

                <div class="boat-map">
                    <?php
                    //$homeBase = urlencode((string) ($data['homeBase'] ?? ''));
                    ?>

                    <iframe
                            src="https://maps.google.com/maps?q=<?php //echo esc_attr($homeBase); ?>&z=14&output=embed"
                            width="600"
                            height="450"
                            style="border:0;"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

        <div id="yachtDetails">
            <div class="yachtdeails">
                <div class="features yachtdeails-info">
                    <h5>Features</h5>
                    <ul>
                        <li><span><?php //_e('Yacht Name:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['name'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Yacht Type:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['kind'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Model:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['model'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Yacht Age:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['year'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Yacht Length (m):', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['length'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Beam (m):', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['beam'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Draft (m):', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['draught'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Engine(s):', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['engine'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Fuel Capacity:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['fuelCapacity'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //('Water Capacity:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['waterCapacity'] ?? 'N/A'); ?>
                        </li>
                    </ul>
                </div>

                <div class="layout yachtdeails-info">
                    <h5>Layout</h5>
                    <ul>
                        <li>
                            <span><?php //_e('Yacht Layout:', 'boat-listing'); ?></span>
                            <?php
                        //     if (!empty($data['images'])) {
                        //         foreach ($data['images'] as $img) {
                        //             if (!empty($img['url']) && stripos($img['name'], 'layout') !== false) {
                        //                 echo '<a href="' . esc_url($img['url']) . '" target="_blank">
                        //     <img src="' . esc_url($img['url']) . '" style="max-width:60px;margin:5px;">
                        //   </a>';
                        //             }
                        //         }
                        //     }
                            ?>
                        </li>
                        <li><span><?php //_e('Max Persons:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['maxPeopleOnBoard'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Cabins:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['cabins'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Total Showers / WC:', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['wc'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php //_e('Berths (Total):', 'boat-listing'); ?></span>
                            <?php //echo esc_html($data['berths'] ?? 'N/A'); ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="yachtPrice">
            <div class="yachtPrice">
                <?php //if (!empty($prices['rows'])): ?>
                    <table class="table table-stripe">
                        <tr>
                            <th>Product</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Price</th>
                            <th>Currency</th>
                        </tr>

                        <?php //foreach ($prices['rows'] as $row): ?>
                            <tr>
                                <td>
                                    <?php
                                    // $product = $row['product'] ?? '';

                                    // // Replace underscores with space
                                    // $product = str_replace('_', ' ', $product);

                                    // // Add space before capital letters (CamelCase → words)
                                    // $product = preg_replace('/(?<!^)([A-Z])/', ' $1', $product);

                                    // echo esc_html(trim($product));
                                    ?>
                                </td>
                                <td>
                                    <?php //echo esc_html(date('Y-m-d', strtotime($row['dateFrom']))); ?>
                                </td>
                                <td>
                                    <?php //echo esc_html(date('Y-m-d', strtotime($row['dateTo']))); ?>
                                </td>
                                <td><?php //echo esc_html($row['price']); ?></td>
                                <td><?php //echo esc_html($row['currency']); ?></td>
                            </tr>
                        <?php //endforeach; ?>
                    </table>
                <?php //else: ?>
                    <p>Price not available</p>
                <?php //endif; ?>
            </div>
        </div>
         <div id="yachtCabinsDetails">
            <div class="yachtcabinsdetails">
                <?php //if (!empty($data['yachtCabinDetails'])): ?>
                    <?php //foreach ($data['yachtCabinDetails'] as $detail): 
                        //$cabin = $detail['yachtCabin'];
                        //$quantity = $detail['quantity'];
                    ?>
                        <div class="cabin-item">
                            <h5><?php //echo esc_html($cabin['cabinName'] ?? 'Cabin'); ?> (x<?php //echo intval($quantity); ?>)</h5>
                            <ul>
                                <li><strong>Type:</strong> <?php //echo esc_html($cabin['cabinType'] ?? ''); ?></li>
                                <li><strong>Position:</strong> <?php // echo esc_html($cabin['cabinPosition'] ?? ''); ?></li>
                                <?php //if (!empty($cabin['description'])): ?>
                                    <li><strong>Description:</strong> <?php //echo esc_html($cabin['description']); ?></li>
                                <?php //endif; ?>
                            </ul>
                        </div>
                    <?php //endforeach; ?>
                <?php //else: ?>
                    <p>No cabin details available.</p>
                <?php //endif; ?>
            </div>
        </div> -->

        <!-- <div id="yachtCompanyDetails">
            <ul>
                <li><span><?php //_e('Name:', 'boat-listing'); ?></span> <?php //echo !empty($company['name']) ? esc_html($company['name']) : __('N/A', 'boat-listing'); ?></li>
                <li><span><?php //_e('Address:', 'boat-listing'); ?></span> <?php //echo !empty($company['address']) ? esc_html($company['address']) : __('N/A', 'boat-listing'); ?></li>
                <li><span><?php //_e('City:', 'boat-listing'); ?></span> <?php //echo !empty($company['city']) ? esc_html($company['city']) : __('N/A', 'boat-listing'); ?></li>
                <li><span><?php //_e('Zip Code:', 'boat-listing'); ?></span> <?php //echo !empty($company['zip']) ? esc_html($company['zip']) : __('N/A', 'boat-listing'); ?></li>
                <li><span><?php //_e('Vatcode:', 'boat-listing'); ?></span> <?php //echo !empty($company['vatcode']) ? esc_html($company['vatcode']) : __('N/A', 'boat-listing'); ?></li>
                <li><span><?php //_e('Phone:', 'boat-listing'); ?></span> <?php //echo !empty($company['phone']) ? esc_html($company['phone']) : __('N/A', 'boat-listing'); ?></li>
            </ul>
        </div>

    </div> -->
    <?php

    // This shortcode for booking modal
    echo do_shortcode('[book_now_modal_shortcode]');

    return ob_get_clean();
}
