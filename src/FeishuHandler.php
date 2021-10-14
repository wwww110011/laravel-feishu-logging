<?php

/*
 * This file is part of the wwww11001/laravel-feishu-logging.
 *
 * (c) wwww11001 <wwww11001@qq.com>
 *
 * This source file is subject to the MIT license that is bundled.
 */

namespace Wwww11001\LaravelFeishuLogging;

use GuzzleHttp\Client;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Class FeishuHandler.
 */
class FeishuHandler extends AbstractProcessingHandler
{
    protected $webhook;

    public function setWebhook(string $webhook): void
    {
        $this->webhook = $webhook;
    }

    protected function write(array $record): void
    {
        $title = $record['message'];
        unset($record['message'], $record['formatted']);

        $traces = $record['context']['exception']->getTrace();
        $contents = [];
        foreach ($traces as $item) {
            $contents[] = [
                'tag' => 'text',
                'text' => json_encode($item, JSON_PRETTY_PRINT + JSON_UNESCAPED_UNICODE + JSON_UNESCAPED_SLASHES),
            ];
        }
        $data = [
            'msg_type' => 'post',
            'content' => [
                'post' => [
                    'zh_cn' => [
                        'title' => $title,
                        'content' => [
                            $contents,
                        ],
                    ],
                ],
            ],
        ];

        $res = (new Client())->post($this->webhook, [
            'http_errors' => false,
            'headers' => ['Content-Type: application/json'],
            'body' => json_encode($data),
        ]);
    }
}
