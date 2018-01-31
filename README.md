MediaWiki Webhooks Extension
============================

**This extension is deprecated and no longer maintained in favor of the official [Extension:EventBus](https://mediawiki.org/wiki/Extension:EventBus), which can replace most purposes of this extension and has more features such as job delegation.**

----

An extension to send POST messages to custom webhooks when certain actions occurred in your MediaWiki sites.

## Requirements

Webhooks extension requires MediaWiki 1.26 or higher.

This extension also requires an endpoint server to receive messages. Please see the payload below to configure your webhooks.

## Installation

To install the extension, place the entire `Webhooks` directory within your MediaWiki `extensions` directory, then add the following line to your `LocalSettings.php` file:

```php
wfLoadExtension( 'Webhooks' );
$wgWebhooksEndpointUrl = 'http://your_webhook_host/endpoint_path/';
$wgWebhooksSecret = 'your_webhook_secret';
```

Similar to the [webhook payload](https://developer.github.com/webhooks/) of Github, there would be a `X-Hub-Signature` request header in the post message of this extension, which is the HMAC hex digest of the response body generated using the `sha1` hash function and the value of `$wgWebhooksSecret` as the HMAC key.

You should verify this signature in your webhooks endpoint. If you endpoint is using Node.js, please take a look at [koa-x-hub](https://github.com/mudkipme/koa-x-hub) or [express-x-hub](https://github.com/alexcurtis/express-x-hub).

## Configuration

### Events

```php
// New user added into MediaWiki
$wgWebhooksNewUser = true;
// User or IP blocked in MediaWiki
$wgWebhooksBlockedUser = true;
// Article added to MediaWiki
$wgWebhooksAddedArticle = true;
// Article removed from MediaWiki
$wgWebhooksRemovedArticle = true;
// Article moved under new title in MediaWiki
$wgWebhooksMovedArticle = true;
// Article edited in MediaWiki
$wgWebhooksEditedArticle = true;
// File uploaded
$wgWebhooksFileUpload = true;
// Article protection settings changed
$wgWebhooksProtectedArticle = true;
```

## Payload

Example delivery:

```
POST /endpoint_path HTTP/1.1

Host: your_webhook_host
Accept: */*
Content-Type: application/json
X-Hub-Signature: sha1=90a131410bec040bfc7ea1083452cb2656aa6c2b
User-Agent: wikimedia/multi-http-client v1.0
Content-Length: 157

{ "action": "EditedArticle",
  "data":
   { "articleId": 126121,
     "title": "测试",
     "namespace": "",
     "user": "Mudkip",
     "isMinor": 0,
     "revision": 1210165,
     "baseRevId": false } }
```

## License

[MIT](LICENSE)

## Acknowledgements

This project is inspired by [DiscordNotifications](https://github.com/kulttuuri/discord_mediawiki) extension.