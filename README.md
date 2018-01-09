MediaWiki Webhooks Extension

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

## License

[MIT](LICENSE)

## Acknowledgements

This project is inspired by [DiscordNotifications](https://github.com/kulttuuri/discord_mediawiki) extension.