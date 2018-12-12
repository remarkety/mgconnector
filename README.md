# Remarkety connector for Magento 2

## Installation
To install the connector using composer, run the following commands:
- cd < Magento 2 installation path >

For Magento 2.1.x or later:

`composer require remarkety/mgconnector:^2.1.0`

For Magento 2.0.x:

`composer require remarkety/mgconnector:2.0.*`

On the web server user, run the following command:
- `bin/magento setup:upgrade`
- `bin/magento setup:db-schema:upgrade`
- `bin/magento setup:di:compile`

Make sure to install Magento's Cron Job to run every minute. For more info please read [Magento 2 Docs](https://devdocs.magento.com/guides/v2.2/config-guide/cli/config-cli-subcommands-cron.html).
## Activation
- Login to Magento's backend
- Go to Remarkety > Remarkety Installation
