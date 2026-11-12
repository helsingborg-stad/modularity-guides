<?php

declare(strict_types=1);

namespace {
    class WP_REST_Controller {}
    class WP_REST_Server
    {
        public const CREATABLE = 'POST';
    }
    class WP_Post
    {
        public string $post_type = 'mod-guide';
    }
    class WP_REST_Request
    {
        public function get_url_params(): array
        {
            return ['id' => 42];
        }

        public function get_json_params(): array
        {
            return ['email' => 'test@example.com', 'checklist' => ['selected']];
        }
    }
    class WP_REST_Response
    {
        public function __construct(public array $data, public int $status) {}
    }
    class ApiTestWordPress
    {
        public static array $action = [];
        public static array $route = [];
        public static array $getPost = [];
        public static ?WP_Post $post = null;
    }

    define('OBJECT', 'OBJECT');
    define('MODULARITYGUIDES_API_NAMESPACE', 'wp/v2');

    function add_action($hook, $callback, $priority, $acceptedArgs): true
    {
        ApiTestWordPress::$action = [$hook, $callback, $priority, $acceptedArgs];
        return true;
    }

    function register_rest_route($namespace, $route, $args, $override): bool
    {
        ApiTestWordPress::$route = [$namespace, $route, $args, $override];
        return true;
    }

    function get_post($post, $output, $filter): ?WP_Post
    {
        ApiTestWordPress::$getPost = [$post, $output, $filter];
        return ApiTestWordPress::$post;
    }
}

namespace ModularityGuides {
    use AcfService\Contracts\GetFields;
    use PHPUnit\Framework\TestCase;
    use WpService\Implementations\NativeWpService;

    class TestApi extends Api
    {
        public array $mail = [];

        protected function renderMailContent($fields): string
        {
            $this->mail['fields'] = $fields;
            return 'rendered';
        }

        protected function sendMail(string $markup, string $to): bool
        {
            $this->mail['message'] = [$markup, $to];
            return true;
        }
    }

    final class ApiTest extends TestCase
    {
        public function testNativeServiceRegistersRouteAndHandlesGuide(): void
        {
            $acf = $this->createMock(GetFields::class);
            $acf->expects($this->once())->method('getFields')->with(42)->willReturn([
                'steps' => [['content' => [[
                    'acf_fc_layout' => 'todo',
                    'list_items' => [
                        ['title' => 'Group', 'link_text' => 'Keep', 'link_url' => '/keep', 'toggle_key' => 'selected'],
                        ['title' => 'Group', 'link_text' => 'Skip', 'link_url' => '/skip', 'toggle_key' => 'other'],
                    ],
                ]]]],
            ]);
            \ApiTestWordPress::$post = new \WP_Post();
            $api = new TestApi(new NativeWpService(), $acf);

            static::assertSame(['rest_api_init', [$api, 'registerRoutes'], 10, 1], \ApiTestWordPress::$action);
            $api->registerRoutes();
            [$namespace, $route, $args, $override] = \ApiTestWordPress::$route;
            static::assertSame('wp/v2', $namespace);
            static::assertSame('/modularity-guides/(?P<id>\d+)', $route);
            static::assertSame([$api, 'handlePost'], $args[0]['callback']);
            static::assertFalse($override);

            $response = $api->handlePost(new \WP_REST_Request());
            static::assertSame([42, OBJECT, 'raw'], \ApiTestWordPress::$getPost);
            static::assertSame(200, $response->status);
            static::assertSame(['status' => 'success'], $response->data);
            static::assertSame(['rendered', 'test@example.com'], $api->mail['message']);
            static::assertSame('Keep', $api->mail['fields']['Group'][0]['link_text']);
            static::assertCount(1, $api->mail['fields']['Group']);
        }

        public function testMissingGuideReturnsNotFoundWithoutLoadingFields(): void
        {
            $acf = $this->createMock(GetFields::class);
            $acf->expects($this->never())->method('getFields');
            \ApiTestWordPress::$post = null;
            $api = new TestApi(new NativeWpService(), $acf);

            $response = $api->handlePost(new \WP_REST_Request());
            static::assertSame(404, $response->status);
            static::assertSame(['error' => 'Invalid guide ID'], $response->data);
        }
    }
}
