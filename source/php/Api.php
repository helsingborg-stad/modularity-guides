<?php

namespace ModularityGuides;

use \ComponentLibrary\Init as ComponentLibraryInit;
use ModularityGuides\Helper\Lang;
use \Municipio\Helper\Color;
use WpService\Contracts\AddAction;
use WpService\Contracts\RegisterRestRoute;
use WpService\Contracts\GetPost;
use AcfService\Contracts\GetFields;

class Api extends \WP_REST_Controller
{
    public function __construct(
        private GetPost&AddAction&RegisterRestRoute $wpService,
        private GetFields $acfService
    ) {
        $this->wpService->addAction('rest_api_init', array($this, 'registerRoutes'));
    }

    public function registerRoutes()
    {
        $this->wpService->registerRestRoute(MODULARITYGUIDES_API_NAMESPACE, '/modularity-guides/(?P<id>\d+)', array(
            array(
                'methods' => \WP_REST_Server::CREATABLE,
                'callback' => array($this, 'handlePost'),
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
            'schema' => array($this, 'getItemSchema'),
        ));
    }

    public function getItemSchema()
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

    public function handlePost(\WP_REST_Request $request)
    {
        $parameters = array_merge(
            $request->get_url_params(),
            $request->get_json_params()
        );

        // Check post type
        $post = $this->wpService->getPost($parameters['id']);

        if (!$post || $post->post_type !== 'mod-guide') {
            return new \WP_REST_Response(['error' => 'Invalid guide ID'], 404);
        }

        // Get fields from the given ID
        $fields = $this->acfService->getFields($parameters['id']);

        if (!$fields) {
            return new \WP_REST_Response(['error' => 'No fields found for the given ID'], 404);
        }

        // Transform fields and filter todo list based on provided checklist keys
        $fields = (new Helper\FieldTransform($fields))->filterTodo($parameters['checklist']);

        // Render email content
        $result = $this->sendMail($this->renderMailContent($fields), $parameters['email']);

        return new \WP_REST_Response(["status" => $result ? "success" : "error"], 200);
    }

    protected function renderMailContent($fields): string
    {
        $bladeEngine = (new ComponentLibraryInit([]))->getEngine();

        return $bladeEngine->makeView('email', [
            'color' => Color::getPalettes(['color_palette_primary'])['color_palette_primary']['base'],
            'content' => $fields,
            'lang' => Lang::getLang()
        ], [], [MODULARITYGUIDES_MODULE_VIEW_PATH])->render();
    }

    protected function sendMail(string $markup, string $to): bool
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
