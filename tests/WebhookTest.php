<?php

namespace AtlassianConnectCore\Tests;

class WebhookTest extends TestCase
{
    /**
     * @var \AtlassianConnectCore\Webhook
     */
    protected $webhook;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        parent::setUp();

        $this->webhook = app(\AtlassianConnectCore\Webhook::class);
    }

    /**
     * @covers Webhook::listen
     * @covers Webhook::getListeners
     */
    public function testListen()
    {
        $this->webhook->listen('jira:issue_created', function(\Illuminate\Http\Request $request) {});
        $this->webhook->listen('jira:issue_created', '\App\Listeners\IssueCreatedListener');

        static::assertCount(2, $this->webhook->getListeners('jira:issue_created'));
    }

    public function testUrl()
    {
        static::assertEquals(
            '/webhook/jira:issue_deleted',
            $this->webhook->url('jira:issue_deleted')
        );

        static::assertEquals(
            'http://localhost/webhook/jira:issue_deleted',
            $this->webhook->url('jira:issue_deleted', true)
        );
    }
}