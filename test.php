<?php
use PHPUnit\Framework\TestCase;

class BfxCryptoMapTest extends TestCase {

    private $plugin;

    protected function setUp(): void {
        // Mock global WordPress functions
        \WP_Mock::setUp();

        // Include the plugin file
        require_once('path/to/plugin/file.php');

        // Initialize the class to be tested
        $this->plugin = new BfxCryptoMap();
    }

    protected function tearDown(): void {
        // Tear down for WP_Mock
        \WP_Mock::tearDown();
    }

    public function test_bfx_crypto_map_handler() {
        // Mock required WP functions
        \WP_Mock::userFunction('shortcode_atts', [
            'times' => 1,
            'return' => [
                'width' => '500px',
                'height' => '500px',
                'mobile_width' => '100%',
                'mobile_height' => 'calc(100vh - 100px)',
                'lang' => 'en'
            ]
        ]);
        
        \WP_Mock::userFunction('plugin_dir_url', [
            'times' => 1,
            'return' => 'mocked_plugin_dir_url/'
        ]);

        $atts = ['width' => '100%'];
        $output = bfx_crypto_map_handler($atts);
        $this->assertStringContainsString('<div class="bfx-crypto-container">', $output);
    }

    public function test_bfx_crypto_map_shortcode_scripts() {
        // Mock required WP functions for the test
        \WP_Mock::userFunction('has_shortcode', [
            'times' => 1,
            'return' => true
        ]);

        \WP_Mock::userFunction('wp_enqueue_script', [
            'times' => 2
        ]);
        
        \WP_Mock::userFunction('wp_enqueue_style', [
            'times' => 4
        ]);
        
        \WP_Mock::userFunction('plugin_dir_url', [
            'times' => 1,
            'return' => 'mocked_plugin_dir_url/'
        ]);

        $post_mock = new \stdClass;
        $post_mock->post_content = "[bfx_crypto_map]";
        $GLOBALS['post'] = $post_mock;

        bfx_crypto_map_shortcode_scripts();
    }
}
