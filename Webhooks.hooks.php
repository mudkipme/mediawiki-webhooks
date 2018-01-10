<?php
/**
 * Webhooks extension
 */

class Webhooks {
    /**
     * Occurs after the save page request has been processed
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentSaveComplete
     */
    public static function onPageContentSaveComplete( $wikiPage, $user, $content, $summary, $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId, $undidRevId ) {
        global $wgWebhooksEditedArticle;
        if (!$wgWebhooksEditedArticle) {
            return;
        }
        // Skip new articles
        $isNew = $status->value['new'];
		if ($isNew == 1) {
			return;
        }
        
        // Skip no edits
		if ($wikiPage->getRevision()->getPrevious() === null) {
			return;
        }
        
        self::sendMessage('EditedArticle', [
            'articleId' => $wikiPage->getTitle()->getArticleID(),
            'title'     => $wikiPage->getTitle()->getFullText(),
            'namespace' => $wikiPage->getTitle()->getNsText(),
            'user'      => (string) $user,
            'isMinor'   => $isMinor,
            'revision'  => $revision->getId(),
            'baseRevId' => $baseRevId
        ]);
    }

    /**
     * Occurs after a new article is created
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentInsertComplete
     */
    public static function onPageContentInsertComplete( $wikiPage, User $user, $content, $summary, $isMinor, $isWatch, $section, $flags, Revision $revision ) {
        global $wgWebhooksAddedArticle;
        if (!$wgWebhooksAddedArticle) {
            return;
        }

        // Skip files
        if ($wikiPage->getTitle()->getNamespace() === NS_FILE) {
            return;
        }

        self::sendMessage('AddedArticle', [
            'articleId' => $wikiPage->getTitle()->getArticleID(),
            'title'     => $wikiPage->getTitle()->getFullText(),
            'namespace' => $wikiPage->getTitle()->getNsText(),
            'user'      => (string) $user,
            'isMinor'   => $isMinor,
            'revision'  => $revision->getId()
        ]);
    }

    /**
     * Occurs after the delete article request has been processed
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleDeleteComplete
     */
    public static function onArticleDeleteComplete( $article, User $user, $reason, $id, Content $content = null, LogEntry $logEntry ) {
        global $wgWebhooksRemovedArticle;
        if (!$wgWebhooksRemovedArticle) {
            return;
        }

        self::sendMessage('RemovedArticle', [
            'articleId' => $article->getTitle()->getArticleID(),
            'title'     => $article->getTitle()->getFullText(),
            'namespace' => $article->getTitle()->getNsText(),
            'user'      => (string) $user,
            'reason'    => $reason
        ]);
    }

    /**
     * Occurs whenever a request to move an article is completed, after the database transaction commits.
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/TitleMoveComplete
     */
    public static function onTitleMoveComplete( Title $title, Title $newTitle, User $user, $oldid, $newid, $reason, Revision $revision ) {
        global $wgWebhooksMovedArticle;
        if (!$wgWebhooksMovedArticle) {
            return;
        }

        self::sendMessage('MovedArticle', [
            'title'         => $title->getFullText(),
            'namespace'     => $title->getNsText(),
            'newTitle'      => $newTitle->getFullText(),
            'newNamespace'  => $newTitle->getNsText(),
            'user'          => (string) $user,
            'reason'        => $reason,
            'oldId'         => $oldid,
            'newId'         => $newid,
            'revision'      => $revision->getId()
        ]);
    }

    /**
     * Called immediately after a local user has been created and saved to the database
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/LocalUserCreated
     */
    public static function onLocalUserCreated( $user, $autocreated ) {
        global $wgWebhooksNewUser;
        if (!$wgWebhooksNewUser) {
            return;
        }

        $email = "";
		$realname = "";
		$ipaddress = "";
		try { $email = $user->getEmail(); } catch (Exception $e) {}
		try { $realname = $user->getRealName(); } catch (Exception $e) {}
		try { $ipaddress = $user->getRequest()->getIP(); } catch (Exception $e) {}

        self::sendMessage('NewUser', [
            'user'          => (string) $user,
            'email'         => $email,
            'realname'      => $realname,
            'ip'            => $ipaddress,
            'autocreated'   => $autocreated
        ]);
    }

    /**
     * Occurs after the request to block an IP or user has been processed
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/BlockIpComplete
     */
    public static function onBlockIpComplete( Block $block, User $user ) {
        global $wgWebhooksBlockedUser;
        if (!$wgWebhooksBlockedUser) {
            return;
        }

        self::sendMessage('BlockedUser', [
            'user'          => (string) ($block->getTarget()),
            'operator'      => (string) $user
        ]);
    }

    /**
     * Called when a file upload has completed.
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/UploadComplete
     */
    public static function onUploadComplete( $image ) {
        global $wgWebhooksFileUpload;
        if (!$wgWebhooksFileUpload) {
            return;
        }

        self::sendMessage('FileUpload', [
            'name'          => (string) ($image->getLocalFile()->getTitle()),
            'mimeType'      => $image->getLocalFile()->getMimeType(),
            'size'          => $image->getLocalFile()->getSize(),
            'description'   => $image->getLocalFile()->getDescription(),
            'user'          => $image->getLocalFile()->getUser(),
            'width'         => $image->getLocalFile()->getWidth(),
            'height'        => $image->getLocalFile()->getHeight()
        ]);
    }

    /**
     * Occurs after the protect article request has been processed
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleProtectComplete
     */
    public static function onArticleProtectComplete( $article, $user, $protect, $reason, $moveonly ) {
        global $wgWebhooksProtectedArticle;
        if (!$wgWebhooksProtectedArticle) {
            return;
        }

        self::sendMessage('ProtectedArticle', [
            'articleId' => $article->getTitle()->getArticleID(),
            'title'     => $article->getTitle()->getFullText(),
            'namespace' => $article->getTitle()->getNsText(),
            'user'      => (string) $user,
            'protect'   => $protect,
            'reason'    => $reason,
            'moveonly'  => $moveonly
        ]);
    }

    /**
     * Send the post message
     */
    private static function sendMessage( $action, $data ) {
        global $wgWebhooksEndpointUrl, $wgWebhooksSecret;

        $body = json_encode([
            'action'    => $action,
            'data'      => $data
        ]);

        $signature = hash_hmac('sha1', $body, $wgWebhooksSecret);

        $http = new MultiHttpClient([]);
        $http->run([
            'method'    => 'POST',
            'url'       => $wgWebhooksEndpointUrl,
            'headers'   => [
                'Content-Type'      => 'application/json',
                'X-Hub-Signature'   => 'sha1=' . $signature
            ],
            'body'      => $body
        ]);
    }
}