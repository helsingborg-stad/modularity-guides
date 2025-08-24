<?php

namespace ModularityGuides;

use \ComponentLibrary\Init as ComponentLibraryInit;
use ModularityGuides\Helper\Lang;

class Api extends \WP_REST_Controller
{
    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route(MODULARITYGUIDES_API_NAMESPACE, '/modularity-guides/(?P<id>\d+)', array(
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => array($this, 'handle_post'),
            'permission_callback' => '__return_true',
        ));
    }

    public function handle_post(\WP_REST_Request $request)
    {
        // Require a valid numeric ID as path parameter
        $parameters = $request->get_url_params();

        if (!isset($parameters['id']) || !is_numeric($parameters['id'])) {
            return new \WP_REST_Response(['error' => 'Invalid ID'], 400);
        }

        // Require email and checklist in the request body
        $body = $request->get_json_params();

        if (!isset($body['email']) || !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            return new \WP_REST_Response(['error' => 'Invalid email address'], 400);
        }

        if (!isset($body['checklist']) || !is_array($body['checklist'])) {
            return new \WP_REST_Response(['error' => 'Invalid checklist data'], 400);
        }

        // Get fields from the given ID
        $fields = get_fields($parameters['id']);

        if (!$fields) {
            return new \WP_REST_Response(['error' => 'No fields found for the given ID'], 404);
        }

        // Transform fields and filter todo list based on provided checklist keys
        $fields = (new Helper\FieldTransform($fields))->filterTodo($body['checklist']);

        // Render email content
        $this->sendMail($this->renderMailContent($fields), $body['email']);

        return new \WP_REST_Response([], 200);
    }

    protected function renderMailContent($fields): string
    {
        $componentLibrary = new ComponentLibraryInit([]);
        $bladeEngine = $componentLibrary->getEngine();

        return $bladeEngine->makeView('email', [
            'content' => $fields,
            'lang' => Lang::getLang()
        ], [], [MODULARITYGUIDES_MODULE_VIEW_PATH])->render();
    }

    protected function sendMail(string $markup, string $to)
    {
        // Send the email
        wp_mail(
            $to,
            Lang::getLang()['your_checklist'],
            $markup,
            array(
                'From: no-reply@helsingborg.se',
                'Content-Type: text/html; charset=UTF-8'
            )
        );
    }
}
