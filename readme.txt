=== XpressRun-Local-Delivery ===
Tags: shipping, woocommerce, delivery, cost, price, quotes, taxes, same day delivery, next day delivey, locale delivery, dynamic shipping, automatic shipping, shipping calculator, calculate shipping cost, shipping discount, free delivery, variable rate delivery, Tracking delivery, multiple shipping rates, shipping api,
Requires at least: 5.0.18
Tested up to: 6.1.1
Requires PHP: 5.6.40
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Xpressrun\'s XpressRun-Local-Delivery allows you to automate your order deliveries while providing the best delivery services.

== Description ==
XpressRun provides the best same-day delivery experience at ground-shipping rates while giving brands end to end ownership of the delivery process (with customizable brand identity & data).
With XpressRun last-mile delivery services, you get to keep your customers happy by giving them what they want when they want it.
Integrate Same Day Delivery with your Woocommerce store to experience the following advantages.
1- Automated delivery:
For each of your orders Xpressrun offers the service in terms of cost and delivery time most appropriate to your customers. 
delivery can be: same day delivery, next day delivery or other according to the opening hours, closing hours and availability of your store.
2- Order management:
You will be able to view and track your orders in the Xpressrun dashboard, you can also create new orders directly.
Finally, you can see the statistics of your activities in the Xpressrun dashboard.

== Installation ==
1- Minimum requirements for installation

WordPress version 5.0.18 or greater
Woocommerce version 4.0 or greater
PHP version 5.6.40
PHP CURL library is required
The permalink must be in the format \"/year/monthnum/day/postname/\"
	
2- How to install

Before installing xpressrun Shipping Method, please ensure that the minimum requirements listed above are met.

The installation process is as follows:

2.1. Download and install the plugin:

- In the WordPress administration dashboard, navigate to the Extensions menu => add: in the search bar type XpressRun-Local-Delivery and install it. 

- After installation, click on Activate to enable the plugin.

2.2. Registration and creation of the store:

- Now, in Extensions => Installed Extensions, you should see XpressRun-Local-Delivery in the list of installed extensions.

- Under XpressRun-Local-Delivery, click on Settings, in the window that appears you should see a button that will allow you to register your store on xpressrun, click on it (click on the Enable button).

- Now you should see another button to create your store on xpressrun, click on it (click on the Create button). You will be redirected to the xpressrun login page (if you don\'t have an xpressrun account, create one), add your username and password and login.

- You will notice that your xpressrun store has been automatically created for you and you can click on it to configure it.

- After creating the store, if you return to the Settings option of XpressRun-Local-Delivery on the WordPress administration dashboard, you should no longer see the Create or Enable buttons

 * Store Synchronization

For xpressrun-shipping to offer its service to your customers, you must synchronize your two stores (Woocommerce store and xpressrun store).

. To synchronize the stores, go to xpressrun.com

- Before synchronizing the two stores for the first time, you must provide the default dimensions of the products in the store (when importing products into xpressrun, all products whose dimensions are not provided in Woocommerce will take these default values).
- In the navigation menu, click on products => import then the store. After a few seconds of loading, you will notice that all products that did not exist in the xpressrun store have been properly imported.

* Store Configuration =

For our shipping service to function properly, the xpressrun store must be properly configured. 

Essential configurations include:

- Default package dimension: during the import of products from woocommerce, these dimensions will be used for products that do not have dimensions in woocommerce.

- Preparation time: necessary to automatically send a driver when an order is placed in your store.

- Business hours: the days and hours the store is open, necessary to offer the appropriate service to the customer.

- Delivery & service fees: offers different options for shipping fees, for example, you can offer customers to pay only 50% of the delivery fees (please consult for all available options). By default, the \"Customer pays the full delivery\" option is enabled.


== Frequently Asked Questions ==
1- For versions of WordPress prior to 5.3, replace the contents of \"/wp-includes/certificates/ca-bundle.crt\" with that of the \"cacert.pem\" file to avoid the \"cURL error 60: SSL certificate problem: certificate has expired wordpress\" error or update WordPress.

2- You must keep the xpressrun store up to date by regularly synchronizing the stores when you add new products in Woocommerce (if one of your customers chooses a product that is not imported into xpressrun, they will not be offered our shipping service: only products imported into the xpressrun store are affected by xpressrun-shipping)