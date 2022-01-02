# WHMCS Xendit Payment Gateway Module #

## Summary ##

Xendit Payment Gateway modules allow you to integrate payment solutions with the WHMCS
platform.

## Installation ##
- Clone this to your directory
- go to directory modules/gateways/xendit
- install dependency with `composer install`
- Upload & merged module folder that you have extracted into your WHMCS directory.

## Configuration ##
1. Access your WHMCS admin page.
2. Go to menu Setup -> Payments -> Payment Gateways.
3. There are will be `**Xendit Payment Gateway Module**`
4. Then choose Setup -> Payments -> Payment Gateways -> Manage Existing Gateways
5. Fill the input as instructed on the screen such us `secretKey` and `callbackToken` see [Xendit Setup](#xendit') below
6. Click Save Changes

## Xendit Setup ##
1. Open Xendit Dashboard > Settings > API Keys > Generate Secret Key > Download and save it to secure place
2. Scroll to Callback setting click on View Callback Verification Token and save it
3. Scroll to Callback URL and put your whmcs callback url e.g ``https://example.com/modules/gateways/callback/xendit.php`` to payment channels you want. Save  

For more information, please contact me at:
| - | -  |
| ------- | --- |
| :e-mail: | ahmadcahyana@outlook.com |
| :phone: | +628131676678 |
| :globe_with_meridians: | https://linkedin.com/in/ahmadcahyana |

## Minimum Requirements ##

For the latest WHMCS minimum system requirements, please refer to
https://docs.whmcs.com/System_Requirements

We recommend your module follows the same minimum requirements wherever
possible.
