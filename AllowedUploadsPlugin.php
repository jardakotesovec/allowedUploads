<?php

/**
 * @file plugins/generic/allowedUploads/AllowedUploadsPlugin.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AllowedUploadsPlugin
 * 
 * @ingroup plugins_generic_allowedUploads
 *
 * @brief Allowed Uploads plugin class
 */

namespace APP\plugins\generic\allowedUploads;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class AllowedUploadsPlugin extends GenericPlugin
{
    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
	public function register($category, $path, $mainContextId = null)
	{
        $success = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return true;
        }
        if ($success && $this->getEnabled($mainContextId)) {

			Hook::add('SubmissionFile::validate', $this->checkUploadWizard(...));			
			Hook::add('submissionfilesuploadform::validate', $this->checkUpload(...));

        }
        return $success;
	}

    /**
     * @copydoc Plugin::getDisplayName()
     */
	public function getDisplayName()
	{
		return __('plugins.generic.allowedUploads.displayName');
	}

    /**
     * @copydoc Plugin::getDescription()
     */
	public function getDescription()
	{
		return __('plugins.generic.allowedUploads.description');
	}

	/**
	 * @copydoc Plugin::getActions()
	 */
	public function getActions($request, $verb)
	{
		$router = $request->getRouter();
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'settings',
					new AjaxModal(
						$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
						$this->getDisplayName()
					),
					__('manager.plugins.settings'),
					null
				),
			):array(),
			parent::getActions($request, $verb)
		);
	}

 	/**
	 * @copydoc Plugin::manage()
	 */
	public function manage($args, $request)
	{
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$context = $request->getContext();
				$templateMgr = TemplateManager::getManager($request);
                $templateMgr->registerPlugin('function', 'plugin_url', $this->smartyPluginUrl(...));

				$form = new AllowedUploadsSettingsForm($this, $context->getId());

				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

	/**
     * Check the uploaded file in wizard
     *
     * @param string $hookName
     * @param array $params
     */
	public function checkUploadWizard($hookName, $params)
	{
		$props = $params[2];
		$locale = $params[4];

		if ($fileName = $props['name'][$locale]){
			$errors =& $params[0];
			$request = Application::get()->getRequest();
			$context = $request->getContext();
			$fileName = $props['name'][$locale];
			$tmp = explode('.',$fileName);
			$extension = strtolower(end($tmp));

			$allowedExtensions = $this->getSetting($context->getId(), 'allowedExtensions');

			if ($allowedExtensions){
				$allowedExtensionsArray = array_filter(array_map('trim', explode(';', $allowedExtensions )), 'strlen');
				if (!in_array($extension, $allowedExtensionsArray)){
					$errors[] = __('plugins.generic.allowedUploads.error', array('allowedExtensions' => $allowedExtensions));
				}
			}


		}
	}

	/**
     * Check the uploaded file
     *
     * @param string $hookName
     * @param array $params
     */	
	public function checkUpload($hookName, $params)
	{
		$form = $params[0];
		$request = Application::get()->getRequest();
		$context = $request->getContext();
		$userVars = $request->getUserVars();
		$fileName = $userVars['name'];
		$tmp = explode('.',$fileName);
		$extension = strtolower(end($tmp));

		$allowedExtensions = $this->getSetting($context->getId(), 'allowedExtensions');

		if ($allowedExtensions){

			$allowedExtensionsArray = array_filter(array_map('trim', explode(';', $allowedExtensions )), 'strlen');

			if (!in_array($extension, $allowedExtensionsArray)){
				$form->addError('allowedFileType', __('plugins.generic.allowedUploads.error', ['allowedExtensions' => $allowedExtensions]));
			}

		}
		return false;
	}
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\allowedUploads\AllowedUploadsPlugin', '\AllowedUploadsPlugin');
}