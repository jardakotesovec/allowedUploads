<?php

/**
 * @file plugins/generic/allowedUploads/AllowedUploadsController.php
 *
 * @class AllowedUploadsController
 *
 * @brief API controller for AllowedUploads plugin settings
 */

namespace APP\plugins\generic\allowedUploads;

use APP\plugins\generic\allowedUploads\formRequests\EditAllowedUploadsSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PKP\core\PKPBaseController;
use PKP\plugins\PluginRegistry;
use PKP\security\Role;

class AllowedUploadsController extends PKPBaseController
{
    public function getHandlerPath(): string
    {
        return 'plugin/allowedUploads';
    }

    public function getRouteGroupMiddleware(): array
    {
        return [
            'has.user',
            'has.context',
            self::roleAuthorizer([
                Role::ROLE_ID_SITE_ADMIN,
                Role::ROLE_ID_MANAGER,
            ]),
        ];
    }

    public function getGroupRoutes(): void
    {
        Route::get('', $this->get(...))->name('plugin.allowedUploads.get');
        Route::put('', $this->edit(...))->name('plugin.allowedUploads.edit');
    }

    public function get(Request $illuminateRequest): JsonResponse
    {
        $plugin = $this->getPlugin();
        $contextId = $this->getRequest()->getContext()->getId();

        return response()->json(
            ['allowedExtensions' => $plugin->getSetting($contextId, 'allowedExtensions') ?? ''],
            Response::HTTP_OK
        );
    }

    public function edit(EditAllowedUploadsSettings $illuminateRequest): JsonResponse
    {
        $plugin = $this->getPlugin();
        $contextId = $this->getRequest()->getContext()->getId();
        $allowedExtensions = $illuminateRequest->validated()['allowedExtensions'];

        $plugin->updateSetting($contextId, 'allowedExtensions', $allowedExtensions);

        return response()->json(
            ['allowedExtensions' => $allowedExtensions],
            Response::HTTP_OK
        );
    }

    private function getPlugin(): AllowedUploadsPlugin
    {
        return PluginRegistry::getPlugin('generic', 'alloweduploadsplugin');
    }
}
