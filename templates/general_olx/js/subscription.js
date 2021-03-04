
/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.8.1
 *	LICENSE: FL7YNR66E9FU - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Classified Ads Script
 *	DOMAIN: svejetu.me
 *	FILE: SUBSCRIPTION.JS
 *
 *	The software is a commercial product delivered under single, non-exclusive,
 *	non-transferable license for one domain or IP address. Therefore distribution,
 *	sale or transfer of the file in whole or in part without permission of Flynax
 *	respective owners is considered to be illegal and breach of Flynax License End
 *	User Agreement.
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2020 |  All copyrights reserved.
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

var flSubscriptionClass = function(){
    this.cancelSubscription = function(service, itemID, subscriptionID, isPage) {
        var data = {
            mode: 'cancelSubscription',
            service: service,
            itemID: itemID,
            subscriptionID: subscriptionID,
            isPage: isPage
        };

        flUtil.ajax(data, function(response) {
            if (response.status == 'OK') {
                if (response.redirect) {
                    location.href = response.redirect;
                    return;
                }
                $('#unsubscription-' + itemID).parent('li').remove();
                printMessage('notice', response.message);
            } else {
                printMessage('error', response.message);
            }
        });
    };
}

var flSubscription = new flSubscriptionClass();
