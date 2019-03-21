# Magento 2 LiteGo.io extension

**LiteGo.io** for Magento 2 is a extension that allows to accept Bitcoin Lightning Payments on your website.

## Requirements
This extension requires:
- Magento 2 version 2.3 and higher
- <a href="https://github.com/litegoio/litego-php" target="_blank">Litego.io API PHP Class</a> version 1.1.1 and higher

## How to install

### Install via composer (recommended)

Preferred way to install is with <a href="https://getcomposer.org/" rel="nofollow">Composer</a> as external library is used.

Run the following command in Magento 2 root folder

```
composer require litegoio/magento2-lightningpayments
php bin/magento setup:upgrade

```

### Install extension from copy-paste package

- Download the latest version at [LiteGo.io for Magento 2](https://github.com/litegoio/magento2-lightningpayments/archive/master.zip)

- Upload files to server in folder `./app/code/LiteGoio/LightningPayments`

- Run the following command in Magento 2 root folder

```
php bin/magento setup:upgrade

```

## Configuration
After installation has completed go to:

- in Administration panel go to `Stores->Configuration->General->Currency Setup`
in block `Currency Options`  in field `Allowed Currencies` select `Bitcoin`. (with ctrl, don't deselect `US Dollar`)

- go to `Stores->Currency Rates` and set Rate for BTC

- go to `Stores->Configuration->Sales->Payment Methods` in block `LiteGo.io Payments` **Enable** method and set Merchant ID and Secret Key Settings you can find on site <a href="https:://litego.io/" target="_blank">LiteGo.io</a>  on page `Dashboard->Settings->Api Key`

## Technical details

This extension modifies some DB fields in standard Magento 2 tables.
This is required in order to display price in Bitcoin more accurately.

Modified tables:
-`quote`
-`quote_item`
-`quote_address`
-`quote_address_item`
-`sales_order`
-`sales_order_item`
-`sales_order_grid`
-`sales_order_payment`

Additional information about changes you can find in file `./etc/db_schema.xml`
