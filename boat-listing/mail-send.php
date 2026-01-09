<?php

class Boat_Listing_Mail_Send{

    protected $helper;

    public function __construct() {

        $this->helper = new Boat_Listing_Helper(); // instantiate helper

        //add_action('phpmailer_init', [$this, 'mailtrap']);
        add_filter('wp_mail_from', [$this, 'from_mail']);
        add_filter('wp_mail_from_name', [$this, 'mail_from_name']);

        // Register custom interval
        add_filter('cron_schedules', [$this, 'add_custom_cron_interval']);

        // Schedule the event
        add_action('wp', [$this, 'schedule_email_cron']);

        // Hook to cron action
        add_action('bl_cron_send_booking_emails', [$this, 'send_pending_booking_emails']);
    }

    function from_mail(){
        return get_option('bl_from_mail'); // should match Mailtrap domain
    }

    function mail_from_name(){
        return get_option('bl_from_name');
    }

    // function mailtrap( $phpmailer ) {
    //     // $phpmailer->isSMTP();
    //     // $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
    //     // $phpmailer->SMTPAuth = true;
    //     // $phpmailer->Port = 2525;
    //     // $phpmailer->Username = '955c1e1f75a837';
    //     // $phpmailer->Password = 'd1438971103ce8';

    //     $phpmailer->isSMTP();     
    //     $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
    //     $phpmailer->SMTPAuth = true; // Ask it to use authenticate using the Username and Password properties
    //     $phpmailer->Port = 25;
    //     $phpmailer->Username = '955c1e1f75a837';
    //     $phpmailer->Password = 'd1438971103ce8';

    //     // Additional settings…
    //     //$phpmailer->SMTPSecure = 'tls'; // Choose 'ssl' for SMTPS on port 465, or 'tls' for SMTP+STARTTLS on port 25 or 587
    //     $phpmailer->From = $this->from_mail();
    //     $phpmailer->FromName = $this->mail_from_name();
    // }

    // Step 1: Add 30 second interval
    public function add_custom_cron_interval($schedules) {
        if (!isset($schedules['every_60_seconds'])) {
            $schedules['every_60_seconds'] = [
                'interval' => 60,
                'display'  => __('Every 60 Seconds')
            ];
        }
        return $schedules;
    }

    // Step 2: Schedule cron event
    public function schedule_email_cron() {
        if (!wp_next_scheduled('bl_cron_send_booking_emails')) {
            wp_schedule_event(time(), 'every_60_seconds', 'bl_cron_send_booking_emails');
        }
    }

    // Step 3: Main function to send mail
    public function send_pending_booking_emails() {
        
        global $wpdb;
        $table =  $wpdb->prefix . 'boat_book_request';

        $pending_mail = $this->helper->fetch_book_reservation_for_mail_send();
        

        if (!empty($pending_mail)) {
            foreach ($pending_mail as $datas):

                $boat_info = $this->helper->fetch_all_boats($datas['boat_id']);
                $boat = $boat_info['data'];
                $data = $datas['book_data'];

                $single_boat_url = site_url('/boat-details/' . $datas['boat_id']); // ✅ Add your boat details URL

                $to = array( get_option('bl_to_mail') );
                $subject = 'New Boat Booking Request from ' . esc_html($data['full_name']);
                $headers = array(
                    'Content-Type: text/html; charset=UTF-8',
                    'From: ' . $this->mail_from_name() . ' <' . $this->from_mail() . '>'
                );

                ob_start(); ?>
                <div style="background-color: #f8f9fa; padding: 30px; font-family: Arial, sans-serif; font-size: 15px; color: #333;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 620px; margin: auto; background-color: #ffffff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        
                        <!-- Header -->
                        <tr>
                        <td style="background-color: #1e2a38; padding: 20px; text-align: center;">
                            <h2 style="margin: 0; font-size: 22px; color: #ffffff;">🚤 New Boat Booking</h2>
                        </td>
                        </tr>

                        <!-- Body -->
                        <tr>
                        <td style="padding: 25px;">
                            <p style="margin-bottom: 15px;">
                            <strong><?php echo esc_html($data['full_name']); ?></strong> has submitted a booking request for the boat <strong><?php echo esc_html($boat['name']); ?></strong>.
                            </p>

                            <table role="presentation" cellpadding="8" cellspacing="0" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <tr>
                                <td style="width: 35%; font-weight: bold; vertical-align: top;">📅 Booking Date:</td>
                                <td><?php echo esc_html($data['book_date']); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">📧 Email Address:</td>
                                <td><?php echo esc_html($data['email']); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">📞 Contact Number:</td>
                                <td><?php echo esc_html($data['contact']); ?></td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">🏠 Address:</td>
                                <td><?php echo nl2br(esc_html($data['address'])); ?></td>
                            </tr>
                            <?php if (!empty($data['additional_services'])): ?>
                            <tr>
                                <td style="font-weight: bold;">🧾 Additional Services:</td>
                                <td><?php echo esc_html(implode(', ', (array) $data['additional_services'])); ?></td>
                            </tr>
                            <?php endif; ?>
                            </table>

                            <!-- Button -->
                            <div style="margin-top: 30px;">
                            <a href="<?php echo esc_url($single_boat_url); ?>" style="background-color: #000000; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">🔎 View Boat Details</a>
                            </div>
                        </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                        <td style="background-color: #f1f3f5; text-align: center; padding: 15px; font-size: 12px; color: #666;">
                            This is an automated message sent from your boat booking system.
                        </td>
                        </tr>

                    </table>
                </div>
                <?php
                $msg_body = ob_get_clean();

                if (wp_mail($to, $subject, $msg_body, $headers)) {
                    $wpdb->update(
                        $table,
                        ['mail_status' => 'sent'],
                        ['id' => $datas['id']],
                        ['%s'],
                        ['%d']
                    );

                    // ✅ 2. Send Thank You email to the User
                    $user_email    = sanitize_email($data['email']);
                    $user_name     = sanitize_text_field($data['full_name']);
                    $user_subject  = 'Thank you for your booking request!';
                    $user_headers  = ['Content-Type: text/html; charset=UTF-8'];
                    
                    ob_start(); ?>
                    <div style="background-color: #f8f9fa; padding: 30px; font-family: Arial, sans-serif; font-size: 15px; color: #333;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 620px; margin: auto; background-color: #ffffff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                            <tr>
                                <td style="background-color: #1e2a38; padding: 20px; text-align: center;">
                                    <h2 style="margin: 0; font-size: 22px; color: #ffffff;">🙏 Thank You, <?php echo esc_html($user_name); ?>!</h2>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 25px;">
                                    <p style="margin-bottom: 15px;">We’ve received your booking request for <strong><?php echo esc_html($boat['name']); ?></strong>. Our team will review it and get back to you shortly.</p>
                                    <p><strong>Booking Date:</strong> <?php echo esc_html($data['book_date']); ?></p>
                                    <p>If you have any questions, feel free to contact us anytime.</p>
                                    <div style="margin-top: 30px;">
                                        <a href="<?php echo esc_url(site_url()); ?>" style="background-color: #000000; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;">🌐 Visit Our Website</a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="background-color: #f1f3f5; text-align: center; padding: 15px; font-size: 12px; color: #666;">
                                    Thank you for choosing our service!
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php
                    $user_msg = ob_get_clean();

                    wp_mail($user_email, $user_subject, $user_msg, $user_headers);


                } else {
                    $wpdb->update(
                        $table,
                        ['mail_status' => 'send_failed'],
                        ['id' => $datas['id']],
                        ['%s'],
                        ['%d']
                    );
                }

            endforeach;
        }

    }



}

new Boat_Listing_Mail_Send();