<?php
class Woowhatspowers_wooreminder_Front {

	public $enable = false;
	public $whatsapp = false;

	function __construct() {
		$this->enable = get_option( 'wpp_enable_wooreminders', false );
		$this->whatsapp = new Woowhatspowers_Whastapp;
	}

	function wwp_send_cron(){
		global $wpdb;

        $wr_list_table = $wpdb->prefix . "woo_reminder_list";

        //take only active reminders
        $blog_time_now = current_time('mysql');

        $reminder_list_sql = "SELECT r.ID,r.order_id,r.prod_id,r.email,r.mail_date,r.mail_sents,r.rmdr_logs FROM $wr_list_table AS r WHERE r.rmdr_status = 1 AND r.mail_date <= '$blog_time_now'";

        $reminders_result = $wpdb->get_results($reminder_list_sql);
        if (!empty($reminders_result)) {
            $m_mail_sub = get_option('wr_email_subject');
            $m_mail_msg = get_option('wr_email_message');
            $inherit_woo_mail_styles = get_option('inherit_woo_mail_styles');
            $wrmdr_email_heading = get_option('wrmdr_email_heading');
            $femail_temps = WooRemindersMagic::get_follow_up_temps();


            foreach ($reminders_result as $reminder) {
                // only allow if the order present
                $order = wc_get_order( $reminder->order_id );
                if( $order ){
	                //each reminder should be checked with all emails
	                $sent_emails = json_decode($reminder->mail_sents);

                	$rmdr_logs = json_decode($reminder->rmdr_logs);

                	if (is_array($sent_emails)) {

                   		if (!in_array('main', $sent_emails) and $this->enable) {
	                        //send the default mail and mark it
	                        //replace shortcodes in content
	                        $m_mail_msg_bdy = WooRemindersMagic::replace_shortcode_in_contents($m_mail_msg, $reminder->order_id, $reminder->prod_id, $reminder->ID, "main");
	                        $m_mail_msg_bdy = do_shortcode($m_mail_msg_bdy);
	                        
	                        $m_mail_sub = WooRemindersMagic::replace_shortcode_in_contents($m_mail_sub, $reminder->order_id, $reminder->prod_id, $reminder->ID, "main", false);
	                        $m_mail_sub = do_shortcode($m_mail_sub);

	                      
	                        $target_phone = $order->get_billing_phone();
	                        $target_phone = $this->whatsapp->phone_validation($target_phone);
	                        
	                        $this->whatsapp->sendMessage($target_phone,strip_tags($m_mail_msg_bdy));
                    	}
                    	//check in all follow up
	                    if (is_array($femail_temps) && !empty($femail_temps)) {

	                        foreach ($femail_temps as $femail_temp) {
	                            if ($femail_temp->status == 1) {
	                                if (!in_array($femail_temp->ID, $sent_emails)) {
	                                	
	                                    $days = $femail_temp->followup_days;
	                                    $mail_date = strtotime($reminder->mail_date);
	                                    $followup_time = strtotime("+$days days", $mail_date);

	                                    $blog_time_now = current_time('timestamp'); //making to timestamp type for compare
	                                    //send mail and mark it
	                                    if ($blog_time_now >= $followup_time) {
	                                    	
	                                        //replace shortcode in content
	                                        //replace shortcodes in content
	                                        $f_mail_msg = WooRemindersMagic::replace_shortcode_in_contents($femail_temp->message, $reminder->order_id, $reminder->prod_id, $reminder->ID, $femail_temp->ID);
	                                        $f_mail_msg = do_shortcode($f_mail_msg);

	                                        $f_mail_sub = WooRemindersMagic::replace_shortcode_in_contents($femail_temp->subject, $reminder->order_id, $reminder->prod_id, $reminder->ID, $femail_temp->ID, false);
	                                        $f_mail_sub = do_shortcode($femail_temp->subject);

	                                        $target_phone = $order->get_billing_phone();
		                        			$target_phone = $this->whatsapp->phone_validation($target_phone);
		                        			$this->whatsapp->sendMessage($target_phone,strip_tags($f_mail_msg));
	                                    }
	                                }
	                            }
	                        }
	                    }
                	}
            	}
        	}
        }
	}
}