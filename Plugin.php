<?php

namespace WpsyncWebspark;

use WpsyncWebspark\Inc\Singleton;
use WpsyncWebspark\Inc\SyncProducts;
use WpsyncWebspark\Inc\TaskSchedule;


class Plugin extends Singleton
{
    public static TaskSchedule $task_schedule;

    protected function __construct()
    {
        parent::__construct();
        $this->include_files();

        self::$task_schedule = TaskSchedule::get_instance();
        self::register_hooks();
        echo '<pre>';
        SyncProducts::sync_products();
        echo '</pre>';
    }


    protected function include_files(): void
    {
        require_once plugin_dir_path(__FILE__) . 'inc/TaskSchedule.php';
        require_once plugin_dir_path(__FILE__) . 'inc/AdminPage.php';
        require_once plugin_dir_path(__FILE__) . 'inc/SyncProducts.php';
        require_once plugin_dir_path(__FILE__) . 'inc/ProductInput.php';
    }

    public static function register_hooks(): void
    {
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
        add_action('admin_menu', ['WpsyncWebspark\Inc\AdminPage', 'register_page']);
    }

    public static function activate(): void
    {
    }


    public static function deactivate(): void
    {
        self::$task_schedule->unscheduled_sync();
    }
}