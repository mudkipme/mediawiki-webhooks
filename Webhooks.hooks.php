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
        
        self::sendMessage('EditedArticle', array(
            'articleId' => $wikiPage->getTitle()->getArticleID(),
            'title'     => $wikiPage->getTitle()->getFullText(),
            'namespace' => $wikiPage->getTitle()->getNsText(),
            'user'      => $user->getName(),
            'isMinor'   => $isMinor,
            'revision'  => $revision->getId(),
            'baseRevId' => $baseRevId,
        ));
    }

    /**
     * Occurs after a new article is created
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/PageContentInsertComplete
     */
    public static function onPageContentInsertComplete( $wikiPage, User $user, $content, $summary, $isMinor, $isWatch, $section, $flags, Revision $revision ) {
        global $wgWebhooksAddedArticle;
        if (!$wgWebhooksEditedArticle) {
            return;
        }

        // Skip files
        if ($wikiPage->getTitle()->getNamespace() === NS_FILE) {
            return;
        }

        self::sendMessage('AddedArticle', array(
            'articleId' => $wikiPage->getTitle()->getArticleID(),
            'title'     => $wikiPage->getTitle()->getFullText(),
            'namespace' => $wikiPage->getTitle()->getNsText(),
            'user'      => $user->getName(),
            'isMinor'   => $isMinor,
            'revision'  => $revision->getId(),
        ));
    }

    /**
     * Occurs after the delete article request has been processed
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleDeleteComplete
     */
    public static function onArticleDeleteComplete( &$article, User &$user, $reason, $id, Content $content = null, LogEntry $logEntry ) {
        global $wgWebhooksRemovedArticle;
        if (!$WebhooksRemovedArticle) {
            return;
        }

        self::sendMessage('RemovedArticle', array(
            'articleId' => $wikiPage->getTitle()->getArticleID(),
            'title'     => $wikiPage->getTitle()->getFullText(),
            'namespace' => $wikiPage->getTitle()->getNsText(),
            'user'      => $user->getName(),
            'reason'    => $reason,
        ));
    }

    /**
     * Occurs whenever a request to move an article is completed, after the database transaction commits.
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/TitleMoveComplete
     */
    public static function onTitleMoveComplete( Title &$title, Title &$newTitle, User $user, $oldid, $newid, $reason, Revision $revision ) {
        global $wgWebhooksMovedArticle;
        if (!$wgWebhooksMovedArticle) {
            return;
        }

        self::sendMessage('MovedArticle', array(
            'title'         => $title->getFullText(),
            'namespace'     => $title->getNsText(),
            'newTitle'      => $newTitle->getFullText(),
            'newNamespace'  => $newTitle->getNsText(),
            'user'          => $user->getName(),
            'reason'        => $reason,
            'oldId'         => $oldid,
            'newId'         => $newid,
            'revision'      => $revision->getId(),
        ));
    }

    /**
     * Called immediately after a local user has been created and saved to the database
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/LocalUserCreated
     */
    public static function onLocalUserCreated( $user, $autocreated ) {

    }

    /**
     * Occurs after the request to block an IP or user has been processed
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/BlockIpComplete
     */
    public static function onBlockIpComplete( Block $block, User $user ) {

    }

    /**
     * Called when a file upload has completed.
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/UploadComplete
     */
    public static function onUploadComplete( &$image ) {

    }

    /**
     * Occurs after the protect article request has been processed
     * @see https://www.mediawiki.org/wiki/Manual:Hooks/ArticleProtectComplete
     */
    public static function onArticleProtectComplete( &$article, &$user, $protect, $reason, $moveonly ) {

    }

    /**
     * Send the post message
     */
    private static function sendMessage( $action, $data ) {
        global $wgWebhooksEndpointUrl, $wgWebhooksSecret;

        $body = json_encode(array(
            'action'    => $action,
            'data'      => $data,
        ));

        $signature = hash_hmac('sha1', $body, $wgWebhooksSecret);

        $context = stream_context_create(array(
            'http' => array(
                'header'  => implode("\r\n", array(
                    'Content-type: application/json',
                    'X-Hub-Signature: sha1=' . $signature,
                )),
                'method'  => 'POST',
                'content' => $post,
            ),
        ));

        file_get_contents($wgWebhooksEndpointUrl, false, $context);
    }
}