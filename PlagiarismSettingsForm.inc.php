<?php 
import('lib.pkp.classes.form.Form');

class PlagiarismSettingsForm extends Form {

	/** @var int */
	var $_journalId;

	/** @var object */
	var $_plugin;

	/**
	 * Constructor
	 * @param $plugin CitedByPlugin
	 * @param $journalId int
	 */
	function __construct($plugin, $journalId) {
		$this->_journalId = $journalId;
		$this->_plugin = $plugin;

		parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
                
                $this->addCheck(new FormValidator($this, 'ithenticate_user', 'required', 'plugins.generic.plagiarism.manager.settings.usernameRequired'));
		$this->addCheck(new FormValidator($this, 'ithenticate_pass', 'required', 'plugins.generic.plagiarism.manager.settings.passwordRequired'));

		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	 * Initialize form data.
	 */
	function initData() {
		$this->_data = array(
                        'ithenticate_user' => $this->_plugin->getSetting($this->_journalId, 'ithenticate_user'),
			'ithenticate_pass' => $this->_plugin->getSetting($this->_journalId, 'ithenticate_pass'),
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
                $this->readUserVars(array('ithenticate_user'));
		$this->readUserVars(array('ithenticate_pass'));
	}

	/**
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = null, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		return parent::fetch($request, $template, $display);
	}

	/**
	 * @copydoc Form::execute()
	 */
	function execute(...$functionArgs) {
                $this->_plugin->updateSetting($this->_journalId, 'ithenticate_user', trim($this->getData('ithenticate_user'), "\"\';"), 'string');
		$this->_plugin->updateSetting($this->_journalId, 'ithenticate_pass', trim($this->getData('ithenticate_pass'), "\"\';"), 'string');
		parent::execute(...$functionArgs);
	}
}
?>