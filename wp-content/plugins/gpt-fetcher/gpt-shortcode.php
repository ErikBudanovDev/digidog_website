<?php
/**
 * Plugin Name: GPT Shortcode
 * Description: Adds a shortcode that renders an input and submit button to fetch and display GPT responses.
 * Version: 1.0
 * Author: Erik Budanov
 */

function gpt_shortcode() {
    // Enqueue a JavaScript file
// Enqueue a JavaScript file
wp_enqueue_script('gpt-ajax-script', plugins_url('/js/gpt-ajax.js', __FILE__), array());
wp_enqueue_style('gpt-style', plugins_url('/css/gpt-styles.css', __FILE__));

// Localize script for AJAX call
wp_localize_script('gpt-ajax-script', 'gptAjax', array('ajaxurl' => admin_url('admin-ajax.php')));


    // Return HTML for the shortcode
    return '<div><input type="text" id="gpt-input"/><button id="gpt-submit">Submit</button><div id="gpt-response"></div></div>';
}
add_shortcode('gpt_shortcode', 'gpt_shortcode');

function handle_gpt_request() {
    // Check if inputData is set in the POST request.
    if (!isset($_POST['inputData'])) {
        wp_send_json_error('Invalid request', 400); // Send an error if inputData isn't set.
        return;
    }

    $input = sanitize_text_field($_POST['inputData']); // Sanitize the inputData.
    $main_prompt= 'You are our sales consultant on the website,
    your main goal is to convince customer to book a free consultation with us.
    Customer is asking for help, he has some questions.
   Answer to that question:  '.$input;
    // Check if the OPENAI_API_KEY constant is defined and assign it to $api_key.
    $api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

    // If the API key is empty, send an error.
    if (empty($api_key)) {
        wp_send_json_error('API key is not set.', 500);
        return;
    }

    // Define the URL for the OpenAI API request.
    $url = 'https://api.openai.com/v1/chat/completions';

    // Set up the body of the request with the input and the number of tokens.
    $body = array(
        'max_tokens' => 150,
        'messages' => array(
            array('role' => 'user', 'content' => $main_prompt ),
        ),
        'model' => 'gpt-3.5-turbo'
    );
    

    // Set up the arguments for the wp_remote_post() function, including the body, headers, and method.
    $args = array(
        'body'        => json_encode($body),
        'headers'     => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ),
        'method'      => 'POST',
        'data_format' => 'body',
    );

    // Send the request to the OpenAI API.
    $response = wp_remote_post($url, $args);

    // Check for an error in the response.
    if (is_wp_error($response)) {
        wp_send_json_error($response->get_error_message(), 500);
        return;
    }

    // Decode the response body.
    $decoded_response = json_decode(wp_remote_retrieve_body($response), true);

    // Check if the expected 'text' element is set in the response and send it back.
    // Check if the expected 'content' element is set in the response and send it back.
if (isset($decoded_response['choices'][0]['message']['content'])) {
    wp_send_json_success($decoded_response['choices'][0]['message']['content']);
} else {
    // Send an error if the 'content' element is not present.
    wp_send_json_error('Unexpected response from the GPT API: ' . print_r($decoded_response), 500);
}

}

// Hook the above function into WordPress AJAX for both logged-in and non-logged-in users.
add_action('wp_ajax_gpt_request', 'handle_gpt_request');
add_action('wp_ajax_nopriv_gpt_request', 'handle_gpt_request');

?>
