<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC WooCommerce class.
 * @author Webnus <info@webnus.biz>
 */
class MEC_feature_wc extends MEC_base
{
    /**
     * @var MEC_factory
     */
    public $factory;

    /**
     * @var MEC_main
     */
    public $main;

    /**
     * @var array
     */
    public $settings;

    /**
     * Constructor method
     * @author Webnus <info@webnus.biz>
     */
    public function __construct()
    {
        // Import MEC Factory
        $this->factory = $this->getFactory();
        
        // Import MEC Main
        $this->main = $this->getMain();

        // General Options
        $this->settings = $this->main->get_settings();
    }
    
    /**
     * Initialize
     * @author Webnus <info@webnus.biz>
     */
    public function init()
    {
        // Pro version is required
        if(!$this->getPRO()) return false;

        // WC Hooks
        $this->factory->action('init', [$this, 'hooks']);
    }

    public function hooks()
    {
        // WC System
        $WC_status = (isset($this->settings['wc_status']) and $this->settings['wc_status'] and class_exists('WooCommerce')) ? true : false;

        // WC system is disabled
        if(!$WC_status) return false;

        // WC library
        $wc = $this->getWC();

        // WooCommerce
        $this->factory->action('woocommerce_order_status_completed', [$wc, 'completed'], 10, 1);
        $this->factory->action('woocommerce_thankyou', [$wc, 'paid'], 10, 1);
        $this->factory->action('woocommerce_new_order_item', [$wc, 'meta'], 10, 2);
        $this->factory->action('woocommerce_order_status_cancelled', [$wc, 'cancelled'], 10, 1);
        $this->factory->action('woocommerce_order_status_refunded', [$wc, 'cancelled'], 10, 1);
        $this->factory->action('woocommerce_after_checkout_validation', [$this, 'validate'],10,2);

        $this->factory->filter('woocommerce_order_item_display_meta_key', [$this, 'display_key'], 10, 2);
        $this->factory->filter('woocommerce_order_item_display_meta_value', [$this, 'display_value'], 10, 2);
        $this->factory->filter('woocommerce_cart_item_name', [$this, 'display_name'], 10, 2);
        $this->factory->filter('woocommerce_cart_item_thumbnail', [$this, 'display_thumbnail'], 10, 2);
    }

    public function display_key($display_key, $meta)
    {
        if($meta->key == 'mec_event_id') $display_key = __('Event', 'modern-events-calendar-lite');
        elseif($meta->key == 'mec_date') $display_key = __('Date', 'modern-events-calendar-lite');
        elseif($meta->key == 'mec_transaction_id') $display_key = __('Transaction ID', 'modern-events-calendar-lite');

        return $display_key;
    }

    public function display_value($display_value, $meta)
    {
        if($meta->key == 'mec_event_id') $display_value = '<a href="'.get_permalink($meta->value).'">'.get_the_title($meta->value).'</a>';
        elseif($meta->key == 'mec_transaction_id') $display_value = $meta->value;
        elseif($meta->key == 'mec_date')
        {
            $date_format = (isset($this->settings['booking_date_format1']) and trim($this->settings['booking_date_format1'])) ? $this->settings['booking_date_format1'] : 'Y-m-d';
            $time_format = get_option('time_format');

            if(str_contains($date_format, 'h') or str_contains($date_format, 'H') or str_contains($date_format, 'g') or str_contains($date_format, 'G')) $datetime_format = $date_format;
            else $datetime_format = $date_format.' '.$time_format;

            $dates = explode(':', $meta->value);

            $start_datetime = date_i18n($datetime_format, $dates[0]);
            $end_datetime = date_i18n($datetime_format, $dates[1]);

            $display_value = sprintf(__('%s to %s', 'modern-events-calendar-lite'), $start_datetime, $end_datetime);
        }

        return $display_value;
    }

    public function display_name($name, $item)
    {
        if(!isset($item['mec_event_id']) or (isset($item['mec_event_id']) and !trim($item['mec_event_id']))) return $name;
        if(!isset($item['mec_date']) or (isset($item['mec_date']) and !trim($item['mec_date']))) return $name;

        $timestamps = explode(':', $item['mec_date']);

        $date_format = (isset($this->settings['booking_date_format1']) and trim($this->settings['booking_date_format1'])) ? $this->settings['booking_date_format1'] : get_option('date_format');
        $start_date = date_i18n($date_format, $timestamps[0]);

        $name .= ' ('.$start_date.')';
        return $name;
    }

    public function display_thumbnail($image, $item)
    {
        if(!isset($item['mec_event_id']) or (isset($item['mec_event_id']) and !trim($item['mec_event_id']))) return $image;
        if(!isset($item['product_id']) or (isset($item['product_id']) and !trim($item['product_id']))) return $image;

        $product_id = $item['product_id'];
        if(has_post_thumbnail($product_id)) return $image;

        $event_id = $item['mec_event_id'];
        if(has_post_thumbnail($event_id)) return get_the_post_thumbnail($event_id);

        return $image;
    }

    public function validate($data, $errors)
    {
        // Cart Items
        $items = WC()->cart->get_cart();

        // Book
        $book = $this->getBook();

        $printed = false;
        $all_items = [];
        foreach($items as $key => $item)
        {
            $event_id = ($item['mec_event_id'] ?? NULL);
            if(!$event_id) continue;

            $product_id = ($item['product_id'] ?? NULL);
            $mec_ticket = get_post_meta($product_id, 'mec_ticket', true);

            $ex = explode(':', $mec_ticket);
            $ticket_id = ($ex[1] ?? NULL);
            if(!$ticket_id) continue;

            $quantity = ($item['quantity'] ?? 1);

            $date = ($item['mec_date'] ?? NULL);
            $timestamps = explode(':', $date);
            $timestamp = $timestamps[0];

            $all_items[$event_id] ??= [];
            $all_items[$event_id][$ticket_id] ??= [];

            if(!isset($all_items[$event_id][$ticket_id][$timestamp])) $all_items[$event_id][$ticket_id][$timestamp] = $quantity;
            else $all_items[$event_id][$ticket_id][$timestamp] += $quantity;

            $availability = $book->get_tickets_availability($event_id, $timestamp);
            $tickets = get_post_meta($event_id, 'mec_tickets', true);

            // Ticket is not available
            if(!isset($availability[$ticket_id]) or (isset($availability[$ticket_id]) and $availability[$ticket_id] != -1 and $availability[$ticket_id] < $quantity))
            {
                $printed = true;
                if($availability[$ticket_id] == '0') $errors->add('validation', sprintf(__('%s ticket is sold out!', 'modern-events-calendar-lite'), $tickets[$ticket_id]['name']));
                else $errors->add('validation', sprintf(__('Only %s slots remained for %s ticket so you cannot book %s ones.', 'modern-events-calendar-lite'), $availability[$ticket_id], $tickets[$ticket_id]['name'], $quantity));
            }
        }

        // Error already printed
        if($printed) return;

        foreach($all_items as $event_id => $tickets)
        {
            // User Booking Limits
            [$limit, $unlimited] = $book->get_user_booking_limit($event_id);

            $total_quantity = 0;
            foreach($tickets as $ticket_id => $timestamps)
            {
                foreach($timestamps as $timestamp => $quantity)
                {
                    $availability = $book->get_tickets_availability($event_id, $timestamp);
                    $tickets = get_post_meta($event_id, 'mec_tickets', true);

                    $total_quantity += $quantity;

                    // Ticket is not available
                    if(!isset($availability[$ticket_id]) or (isset($availability[$ticket_id]) and $availability[$ticket_id] != -1 and $availability[$ticket_id] < $quantity))
                    {
                        if($availability[$ticket_id] == '0') $errors->add('validation', sprintf(__('%s ticket is sold out!', 'modern-events-calendar-lite'), $tickets[$ticket_id]['name']));
                        else $errors->add('validation', sprintf(__('Only %s slots remained for %s ticket so you cannot book %s ones.', 'modern-events-calendar-lite'), $availability[$ticket_id], $tickets[$ticket_id]['name'], $quantity));
                    }
                }
            }

            // Take Care of User Limit
            if(!$unlimited and $total_quantity > $limit)
            {
                $errors->add('validation', sprintf($this->main->m('booking_restriction_message3', __("Maximum allowed number of tickets that you can book is %s.", 'modern-events-calendar-lite')), $limit));
            }
        }
    }
}