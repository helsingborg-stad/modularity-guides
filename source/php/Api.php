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
            array(
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => array($this, 'handle_post'),
                'permission_callback' => '__return_true',
                'args' => array(
                    'id' => array(
                        'required' => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return rest_validate_value_from_schema($param, $this->get_item_schema()['properties']['id'], $key);
                        }
                    ),
                    'email' => array(
                        'required' => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return rest_validate_value_from_schema($param, $this->get_item_schema()['properties']['email'], $key);
                        }
                    ),
                    'checklist' => array(
                        'required' => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return rest_validate_value_from_schema($param, $this->get_item_schema()['properties']['checklist'], $key);
                        }
                    ),
                ),
            ),
            'schema' => array($this, 'get_item_schema'),
        ));
    }

    public function get_item_schema()
    {
        return array(
            '$schema'              => 'http://json-schema.org/draft-04/schema#',
            'type'                 => 'object',
            'properties'           => array(
                'id' => array(
                    'type'         => 'integer',
                    'required'     => true,
                ),
                'email' => array(
                    'type'         => 'string',
                    'format'      => 'email',
                    'required'     => true,
                ),
                'checklist' => array(
                    'type'         => 'array',
                    'required'     => true,
                    'items'       => array(
                        'type' => 'string',
                    ),
                ),
            ),
        );
    }

    public function handle_post(\WP_REST_Request $request)
    {
        $parameters = array_merge(
            $request->get_url_params(),
            $request->get_json_params()
        );

        // Get fields from the given ID
        $fields = get_fields($parameters['id']);

        if (!$fields) {
            return new \WP_REST_Response(['error' => 'No fields found for the given ID'], 404);
        }

        // Transform fields and filter todo list based on provided checklist keys
        $fields = (new Helper\FieldTransform($fields))->filterTodo($parameters['checklist']);

        // Render email content
        $result = $this->send_mail($this->render_mail_content($fields), $parameters['email']);

        return new \WP_REST_Response(["status" => $result ? "success" : "error"], 200);
    }

    protected function render_mail_content($fields): string
    {
        $bladeEngine = (new ComponentLibraryInit([]))->getEngine();

        return $bladeEngine->makeView('email', [
            'content' => $fields,
            'lang' => Lang::getLang()
        ], [], [MODULARITYGUIDES_MODULE_VIEW_PATH])->render();
    }

    protected function send_mail(string $markup, string $to): bool
    {
        return wp_mail(
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
