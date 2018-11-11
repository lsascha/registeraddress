Extension Manual
=================

Description:
-----------
newsletter registration extension to tt_address made in Extbase + Fluid for Typo3.
Similar to direct_mail_subscription except based on Extbase.

Features:
-----------
- double opt-in
- user can edit its own data
- unsubscribing

Installation:
-----------
1. Have tt_address and for example direct mail installed and ready
2. Install registeraddress extension like all others.
3. Use the update script in the Extension-Manager of registeraddress
   to add a generated hash to all already existing tt_address entries.
   
   _The update script is not shown when no entries with a missing hash are found._

Setup:
-----------
1. Include the Static template "registerttaddress (registeraddress)" in the root-template.
2. Create new Page for the Newsletter Registration form and add the Plug-In "Registration Form" on it.
3. Configure the values in the Constant-Editor of the root-page.
    1. Set "Default storage PID" to the Page-ID where the tt_address entries will be saved.
    2. Set "Page id with form" to the previously created page with the form.
    3. Set "Mail address from which mails are send" to the mail-address from which address the mails are send + the other settings for send mails.
    4. Set "format of send e-mails (txt, html or both)" to the format the send mails should have.

if a newsletter registration form is needed on all pages, you need to have the following configuration in your TypoScript:
```
plugin.tx_registeraddress {
    mvc.callDefaultActionIfActionCantBeResolved = 1
}

# then create an user object for the footer form

lib.footernewsletter = USER
lib.footernewsletter {
    userFunc = TYPO3\CMS\Extbase\Core\Bootstrap->run
    extensionName = Registeraddress
    pluginName = RegisterformRedirect
    vendorName = AFM
    controller = Address
    action = new
    switchableControllerActions {
        Address {
            1 = new
        }
    }
    
    view < plugin.tx_registeraddress.view
    view {
        layoutRootPaths {
            100 = EXT:sitebootstrap/Resources/Private/Layouts/Registeraddress/
        }
        
        partialRootPath {
            100 = EXT:sitebootstrap/Resources/Private/Partials/Registeraddress/
        }
        
        templateRootPaths {
            100 = EXT:sitebootstrap/Resources/Private/Templates/Registeraddress/
        }
    }
    
    persistence < plugin.tx_registeraddress.persistence
    
    settings < plugin.tx_registeraddress.settings
    
    settings {
        mainformpageuid = 34
    }
}
```
for setting your own translations:
```
plugin.tx_registeraddress {
    _LOCAL_LANG.de {
        form.new.title = NEWSLETTER
        form.create.approvetext (
            Vielen Dank für Ihren Anmeldung.<br />
            Bitte bestätigen Sie die Newsletter-Anmeldung in der soeben an Sie versendeten E-Mail.
        )
        
        form.create.alreadyexists (
            Vielen Dank.<br />
            Sie sind bereits für unseren Newsletter angemeldet.
        )
        
        mail.info.subjectsuffix = NEWSLETTER
        mail.registration.subjectsuffix = Newsletter-Registrierung
        mail.unsubscribe.subjectsuffix = Abmeldung
        mail.deletesuccess.subjectsuffix = Abmeldung abgeschlossen
        mail.approvesuccess.subjectsuffix = Anmeldung erfolgreich
    }
}
```

To add unsubscribe link to direct_mail:

add registeraddresshash to field `basic.addRecipFields` of direct_mail in extension manager in settings of direct_mail
After that a link can be created with TypoScript:

```
typolink.parameter = 123
typolink.additionalParams = &tx_registeraddress_registerform[hash]=###USER_registeraddresshash###&tx_registeraddress_registerform[action]=delete&tx_registeraddress_registerform[controller]=Address
```

## DSGVO / GDPR
In version 1.0.13 you can add a consent message to the plugin.
Additionally for logging when someone subscribed / unsubscribed you should use the Extension
[_registeraddress_logger_](https://github.com/undkonsorten/registeraddress_logger)

`composer require undkonsorten/registeraddress-logger ^1.0`

Usage with other extensions than tt_address:
-----------
An example extension that connects *registeraddress* with fe_users can be found here https://github.com/lsascha/feusers_for_registeraddress
