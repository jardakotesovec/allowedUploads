<?php

/**
 * @file plugins/generic/allowedUploads/AllowedUploadsSettingsController.php
 *
 * @class AllowedUploadsSettingsController
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
use PKP\security\Role;

class AllowedUploadsSettingsController extends PKPBaseController
{
    public function __construct(
        private AllowedUploadsPlugin $plugin
    ) {}

    public function getHandlerPath(): string
    {
        return 'plugins/' . $this->plugin->getName() . '/settings';
    }

    public function getRouteGroupMiddleware(): array
    {
        $roles = [Role::ROLE_ID_SITE_ADMIN];

        if (!$this->plugin->isSitePlugin()) {
            $roles[] = Role::ROLE_ID_MANAGER;
        }

        return [
            'has.user',
            'has.context',
            self::roleAuthorizer($roles),
        ];
    }

    public function getGroupRoutes(): void
    {
        Route::get('', $this->get(...))->name('plugin.allowedUploads.get');
        Route::put('', $this->edit(...))->name('plugin.allowedUploads.edit');
    }

    public function get(Request $illuminateRequest): JsonResponse
    {
        $contextId = $this->getRequest()->getContext()->getId();

        return response()->json(
            ['allowedExtensions' => $this->plugin->getSetting($contextId, 'allowedExtensions') ?? ''],
            Response::HTTP_OK
        );
    }

    public function edit(EditAllowedUploadsSettings $illuminateRequest): JsonResponse
    {
        $contextId = $this->getRequest()->getContext()->getId();
        $allowedExtensions = $illuminateRequest->validated()['allowedExtensions'];

        $this->plugin->updateSetting($contextId, 'allowedExtensions', $allowedExtensions);

        return response()->json(
            ['allowedExtensions' => $allowedExtensions],
            Response::HTTP_OK
        );
    }

}
