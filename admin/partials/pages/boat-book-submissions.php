<?php
$helper = new Boat_Listing_Helper();
$reserve = $helper->fetch_book_reservation();
$boat = $helper->fetch_all_boats();
?>
<div class="wrap">
    <h2>Boat book reservation request lists</h2>
    <table class="bl-data-table" class="display">
        <thead>
            <tr>
                <th>Book Date</th>
                <th>Boat Name</th>
                <th>Name</th>
                <th>Email</th>
                <th>Number</th>
                <th>Address</th>
                <th>Additional Service</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if (!empty($reserve)) :
                foreach($reserve as $datas) :
                    $boat = $helper->fetch_all_boats($datas['boat_id']);

                    $url = isset($boat['id']) ? esc_url(site_url('/boat-details?id=' . $boat['id'])) : '#';
                    $data = isset($datas['book_data']) && is_array($datas['book_data']) ? $datas['book_data'] : [];

            ?>
            <tr>
                <td><?php echo esc_html($data['book_date'] ?? 'N/A'); ?></td>
                <td>
                    <a href="<?php echo $url; ?>" target="_blank">
                        <?php echo esc_html($boat['data']['name'] ?? 'N/A'); ?>
                    </a>
                </td>
                <td><?php echo esc_html($data['full_name'] ?? 'N/A'); ?></td>
                <td><?php echo esc_html($data['email'] ?? 'N/A'); ?></td>
                <td><?php echo esc_html($data['contact'] ?? 'N/A'); ?></td>
                <td><?php echo esc_html($data['address'] ?? 'N/A'); ?></td>
                <td><?php echo esc_html(implode(', ', (array) ($data['additional_services'] ?? []))); ?></td>
                <td><?php echo esc_html($datas['inserted_at'] ?? 'N/A'); ?></td>
            </tr>
            <?php 
                endforeach;
            else:
            ?>
            <tr>
                <td colspan="8">No reservation found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
