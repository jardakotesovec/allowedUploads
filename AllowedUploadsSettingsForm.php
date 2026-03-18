<?php

/**
 * @file plugins/generic/allowedUploads/AllowedUploadsSettingsForm.php
 *
 * Copyright (c) 2014-2026 Simon Fraser University
 * Copyright (c) 2003-2026 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AllowedUploadsSettingsForm
 * 
 * @ingroup plugins_generic_allowedUploads
 *
 * @brief Form for managers to modify Allowed Uploads plugin settings
 */

namespace APP\plugins\generic\allowedUploads;

use APP\template\TemplateManager;
use PKP\form\Form;

class AllowedUploadsSettingsForm extends Form
{

	/** @var int */
	public $_contextId;

	/** @var object */
	public $_plugin;

	/**
	 * Constructor
	 * 
	 * @param AllowedUploadsPlugin plugin
	 * @param int $contextId
	 */
	function __construct($plugin, $contextId) 
	{
		$this->_contextId = $contextId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));

	}

	/**
	 * Initialize form data.
	 */
	public function initData() 
	{
		$this->_data = array(
			'allowedExtensions' => $this->_plugin->getSetting($this->_contextId, 'allowedExtensions'),
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	public function readInputData()
	{
		$this->readUserVars(array('allowedExtensions'));
	}

    /**
     * @copydoc Form::fetch()
     *
     * @param null|mixed $template
     */
	public function fetch($request, $template = null, $display = false)
	{
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request, $template, $display);
	}

    /**
     * @copydoc Form::execute()
     */
	public function execute(...$functionArgs)
	{
		$this->_plugin->updateSetting($this->_contextId, 'allowedExtensions', $this->getData('allowedExtensions'), 'string');
		parent::execute(...$functionArgs);
	}

}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\allowedUploads\AllowedUploadsSettingsForm', '\AllowedUploadsSettingsForm');
}
