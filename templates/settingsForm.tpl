<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#plagiarismSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form 
	class="pkp_form" 
	id="plagiarismSettingsForm" 
	method="post" 
	action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="plagiarismSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.plagiarism.manager.settings.description"}</div>

	{fbvFormArea id="webFeedSettingsFormArea"}
            {fbvElement type="text" id="ithenticate_user" value=$ithenticate_user label="plugins.generic.plagiarism.manager.settings.username"}
            {fbvElement type="text" id="ithenticate_pass" value=$ithenticate_pass label="plugins.generic.plagiarism.manager.settings.password"}
                
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>