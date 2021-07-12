# Remarkety connector for Magento 2

## Installation
To install the connector using composer, run the following commands:
- cd < Magento 2 installation path >
- composer require remarkety/mgconnector

On the web server user, run the following command:
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile

Make sure to install Magento's Cron Job to run every minute. For more info please read [Magento 2 Docs](https://devdocs.magento.com/guides/v2.2/config-guide/cli/config-cli-subcommands-cron.html).
## Activation
- Login to Magento's backend
- Go to Remarkety > Remarkety Installation
