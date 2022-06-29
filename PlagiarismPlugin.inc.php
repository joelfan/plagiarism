<?php

/**
 * @file PlagiarismPlugin.inc.php
 *
 * Copyright (c) 2003-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @brief Plagiarism plugin
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PlagiarismPlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	public function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		$this->addLocaleData();

		if ($success && Config::getVar('ithenticate', 'ithenticate') && $this->getEnabled()) {
			HookRegistry::register('submissionsubmitstep4form::display', array($this, 'callback'));
		}
		return $success;
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	public function getDisplayName() {
		return __('plugins.generic.plagiarism.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	public function getDescription() {
		return Config::getVar('ithenticate', 'ithenticate')?__('plugins.generic.plagiarism.description'):__('plugins.generic.plagiarism.description.seeReadme');
	}

	/**
	 * @copydoc LazyLoadPlugin::getCanEnable()
	 */
	function getCanEnable() {
		if (!parent::getCanEnable()) return false;
		return Config::getVar('ithenticate', 'ithenticate');
	}

	/**
	 * @copydoc LazyLoadPlugin::getEnabled()
	 */
	function getEnabled($contextId = null) {
		if (!parent::getEnabled($contextId)) return false;
		return Config::getVar('ithenticate', 'ithenticate');
	}

	/**
	 * Send submission files to iThenticate.
	 * @param $hookName string
	 * @param $args array
	 */
	public function callback($hookName, $args) {
		$request = Application::getRequest();
		$context = $request->getContext();
		$submissionDao = Application::getSubmissionDAO();
		$submission = $submissionDao->getById($request->getUserVar('submissionId'));
		$publication = $submission->getCurrentPublication();
		$journal = & $request->getJournal();
	        $journalId = $journal->getId();

		require_once(dirname(__FILE__) . '/vendor/autoload.php');

		$proxyName = Config::getVar('proxy', 'http_host');
		$proxyPort = Config::getVar('proxy', 'http_port');
		//error_log("NewPlagiarism: We have proxy $proxyName:$proxyPort:");
		$user = $this->getSetting($journalId, 'ithenticate_user');
        $pass = $this->getSetting($journalId, 'ithenticate_pass');
		$superUser = Config::getVar('ithenticate', 'username');
		$superPass = Config::getVar('ithenticate', 'password');
		if ((!isset($user)) || ($user == "")) {
			$user = $superUser;
			$pass = $superPass;
		}
		$pieces1 = explode('@', $user);
		$pieces2 = explode('.', $pieces1[0]);
		$firstName = ucfirst($pieces2[0]);
		$lastName = ucfirst($pieces2[1]) ?? $firstName;
		$contextName = $firstName.' '.$lastName;
		
		// Connect with superuser credentials to check if user $user is already defined
		if ((isset($proxyName)) && (isset($proxyPort)) && ($proxyName != "") && ($proxyPort != "")) {
			//error_log("NewPlagiarism: We are trying with proxy $proxyName:$proxyPort:");
			$ithenticate = new \joelfan\ithenticate\Ithenticate($superUser, $superPass, (object) array("proxyName" => $proxyName, "proxyPort" => $proxyPort ));			
		}
		else {
			//error_log("NewPlagiarism: We are trying without proxy ");
			$ithenticate = new \joelfan\ithenticate\Ithenticate($superUser, $superPass);
		}

		// get the list of users
		$userList = $ithenticate->fetchUserList();
		if (!($userId = array_search($user, $userList))) {
			//error_log("NewPlagiarism: Define user $user $firstName $lastName");
			$userId = $ithenticate->addUser($user, $pass, $firstName, $lastName);
		}
		unset($ithenticate); // frees the object
		
		// Connect with normal user credentials
		if ((isset($proxyName))&& (isset($proxyPort))) 
			$ithenticate = new \joelfan\ithenticate\Ithenticate($user, $pass, (object) array("proxyName" => $proxyName, "proxyPort" => $proxyPort ));
		else 
			$ithenticate = new \joelfan\ithenticate\Ithenticate($user, $pass);

		// Make sure there's a group list for this context, creating if necessary.
		$groupList = $ithenticate->fetchGroupList();
		$contextName = $context->getLocalizedName($context->getPrimaryLocale());
		if (!($groupId = array_search($contextName, $groupList))) {
			// No folder group found for the context; create one.
			$groupId = $ithenticate->createGroup($contextName);
            if (!$groupId) {
				error_log('Could not create folder group for context ' . $contextName . ' on iThenticate.');
				return false;
			}
		}

		// Create a folder for this submission.
		if (!($folderId = $ithenticate->createFolder(
			'Submission_' . $submission->getId(),
			'Submission_' . $submission->getId() . ': ' . $publication->getLocalizedTitle($publication->getData('locale')),
			$groupId,
			1
		))) {
			error_log('Could not create folder for submission ID ' . $submission->getId() . ' on iThenticate.');
			return false;
		}

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$submissionFiles = $submissionFileDao->getBySubmissionId($submission->getId());
		$authors = $publication->getData('authors');
		$author = array_shift($authors);
		foreach ($submissionFiles as $submissionFile) {
			if (!$ithenticate->submitDocument(
				$submissionFile->getLocalizedName(),
				$author->getLocalizedGivenName(),
				$author->getLocalizedFamilyName(),
				$submissionFile->getOriginalFileName(),
				file_get_contents($submissionFile->getFilePath()),
				$folderId
			)) {
				error_log('Could not submit ' . $submissionFile->getFilePath() . ' to iThenticate.');
			}
		}

		return false;
	}
	
	/*********** DB *******************/
	/**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $verb) {
        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
		
        return array_merge(
            $this->getEnabled() ? array(
				new LinkAction(
					'settings',
					new AjaxModal(
							$router->url($request, null, null, 'manage', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic')),
							$this->getDisplayName()
					),
				__('manager.plugins.settings'),
				null
				),
            ) : array(),
            parent::getActions($request, $verb) 
        );
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request) {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $context = $request->getContext();

                AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON, LOCALE_COMPONENT_PKP_MANAGER);
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->registerPlugin('function', 'plugin_url', array($this, 'smartyPluginUrl'));

                $this->import('PlagiarismSettingsForm');
                $form = new PlagiarismSettingsForm($this, $context->getId());

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
}

/**
 * Low-budget mock class for \bsobbe\ithenticate\Ithenticate -- Replace the
 * constructor above with this class name to log API usage instead of
 * interacting with the iThenticate service.
 */
class TestIthenticate {
	public function __construct($username, $password) {
		error_log("Constructing iThenticate: $username $password");
	}

	public function fetchGroupList() {
		error_log('Fetching iThenticate group list');
		return array();
	}

	public function createGroup($group_name) {
		error_log("Creating group named \"$group_name\"");
		return 1;
	}

	public function createFolder($folder_name, $folder_description, $group_id, $exclude_quotes) {
		error_log("Creating folder:\n\t$folder_name\n\t$folder_description\n\t$group_id\n\t$exclude_quotes");
		return true;
	}

	public function submitDocument($essay_title, $author_firstname, $author_lastname, $filename, $document_content, $folder_number) {
		error_log("Submitting document:\n\t$essay_title\n\t$author_firstname\n\t$author_lastname\n\t$filename\n\t" . strlen($document_content) . " bytes of content\n\t$folder_number");
		return true;
	}
}
