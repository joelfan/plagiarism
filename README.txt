This plugin is a fork from PKP Plagiarism (from asmecher, find it here: https://github.com/pkp/plagiarism).

This is a fork to be used exclusively with OJS/OMP 3.2.
It will not work with OJS/OMP 3.3. 
The plugin starts from PKP plagiarism 3.2, adds Radek (https://github.com/pkp/plagiarism/pull/31) wonderful support for settings, and adds some more functionality, among which:
+ change of bsobbe/ithenticate library, adding support for user management, php8, proxy. This is also a fork, find it here https://github.com/joelfan/iThenticate;
+ change of pkp/plagiarism 1.0.3 (old version for OJS 3.2, as of today they are at 1.0.5) including radek setting form;
+ the plugin will define a new user when not existent;

If there is interest, we can be porting this fork to work with OJS/OMP 3.3, applying the fork to the last release of PKP Plagiarism (https://github.com/pkp/plagiarism).


For this plugin to work, the following must be added to your config.inc.php file, anyway:

;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;
; iThenticate Plugin Settings ;
;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;

[ithenticate]

; Enable iThenticate to submit manuscripts after submit step 4
ithenticate = On

; The username to access the API (usually an email address)
username = "user@email.com"

; The password to access the API
password = "password"
