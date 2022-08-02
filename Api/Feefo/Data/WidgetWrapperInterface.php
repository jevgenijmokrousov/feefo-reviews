<?php

namespace Feefo\Reviews\Api\Feefo\Data;

use Feefo\Reviews\Api\Feefo\Data\WidgetConfigInterface;
use Feefo\Reviews\Api\Feefo\Data\WidgetSnippetInterface;

/**
 * Interface WidgetWrapperInterface
 */
interface WidgetWrapperInterface
{
    public const DATA_WIDGET_SETTINGS = 'widgetSettings';

    public const DATA_SNIPPET_PREVIEW = 'snippetsPreview';

    public const DATA_REDIRECT_URL = 'redirectUrl';

    public const DATA_ACCESS_TOKEN = 'accessToken';

    /**
     * Retrieve widget settings
     *
     * @return WidgetConfigInterface
     */
    public function getWidgetSettings();

    /**
     * Retrieve snippets for the widgets
     *
     * @return WidgetSnippetInterface
     */
    public function getSnippetsPreview();

    /**
     * Retrieve redirect URL
     *
     * @return string
     */
    public function getRedirectUrl();

    /**
     * Retrieve access token
     *
     * @return string
     */
    public function getAccessToken();
}
