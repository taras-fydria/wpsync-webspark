<?php

namespace WpsyncWebspark\Inc;

class TaskSchedule extends Singleton
{
    const  ACTION_NAME = 'wpsync_webspark_sync';
    static SyncProducts $sync_product;

    protected function __construct()
    {
        parent::__construct();
        self::$sync_product = SyncProducts::get_instance();

        add_action('wp_loaded', [__CLASS__, 'scheduled_sync']);
        add_action(self::ACTION_NAME, [self::$sync_product, 'sync_products']);
    }


    public static function scheduled_sync(): void
    {
        if (!wp_next_scheduled(self::ACTION_NAME)) {
            wp_schedule_event(time(), 'hourly', self::ACTION_NAME); // Schedule the sync to run every hour
        }
    }

    public function unscheduled_sync(): void
    {
        $timestamp = wp_next_scheduled(self::ACTION_NAME);
        if (!$timestamp) {
            return;
        }
        wp_unschedule_event($timestamp, self::ACTION_NAME);
    }
}