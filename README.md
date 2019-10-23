<p># <strong>worldpay-payment-gateway-magento2.2x-2.3.x</strong><br />This module can be used for Magento2.2x, Magento2.3x EE &amp;amp; CE [With Web APIs].</p>
<p>Worldpay Online Payments Magento Module - Version 2.2.0</p>
<p><strong>Tested versions..</strong></p>
<p>Magento 2.2.2 - 2.3.2</p>
<p><br /><strong>Installation</strong></p>
<p>Take clone from https://github.com/zyxware-magento/worldpay-payment-gateway-magento2.2x-2.3.x.git</p>
<p><strong>Run below scripts to install the extension</strong></p>
<p>php bin/magento setup:upgrade<br />php bin/magento cache:clean</p>
<p><strong>How To use</strong></p>
<p>Login to your Magento Admin Panel. Go to Stores -&gt; Configuration-&gt; Sales -&gt; Worldpay</p>
<p>Add your keys, which you can find in your Worldpay dashboard (Settings -&gt; API keys). Change Enabled to Yes, set your title and payment descriptions to what you would like the user to see.</p>
<p>Changing www.mywebsite.com to your website URL. Visiting this URL should show OK is similar. Your URL most be externally accessible.</p>
<p>If you change your API keys in future, you may need to clear the Magento cache for it to take affect immediately.</p>
<p><br /><strong>Configuration options</strong><br />Enabled</p>
<p>Enable / Disable the module<br />Settlement Currency</p>
<p>Choose the settlement currency that you have setup in the Worldpay online dashboard.<br />Use 3D Secure</p>
<p>Process 3D secure payments for front end card orders<br />Payment Action</p>
<p>Setting to Authorize only; will require you to enable authorisations in your Worldpay online dashboard. You will then be able to capture the payment when you create an invoice in Magento. You can only capture once, of any amount up to the total of the order.</p>
<p>Setting to Authorize and Capture; will capture the order immediately.<br />Enable Debug</p>
<p>This should be set to 'no' in normal circumstances. If you need support we may ask you to enable this.<br />Store customers card on file</p>
<p>A reusable token will be generated for the customer which will then be stored. This will allow the customer to reuse cards they've used in the past. They simply need to re-enter their CVC to verify. They can view their stored cards in My Account.<br />New order status</p>
<p>Your Magento order status when payment has been successfully taken<br />Title</p>
<p>The title of the module, as appears in the payment methods list to your customer. You can set this to blank to show no title.<br />Payment Description</p>
<p>Payment description to send to Worldpay.<br />Environment Mode</p>
<p>Test Mode / Live Mode - Which set of service and client keys to use on the site<br />Test, Live - Service &amp; Client keys</p>
<p>Your keys, which can be found in your Worldpay dashboard.<br />Troubleshooting</p>
<p><strong>Design Your custom Payment Window and Use below APIs to connect with Worldpay.</strong></p>
<p>Sample Design :</p>
https://github.com/zyxware-magento/worldpay-payment-gateway-magento2.2x-2.3.x/blob/master/payemtn_worldpay_screenshot.png
<p>&nbsp;</p>
<p><strong>Web APIs to connect worldpay with Your Custom design</strong></p>
<p><strong>API to get payment Methodes</strong></p>
<p>Method : GET</p>
<p>For Guest User :<br />guest-carts/&lt;token&gt;/payment-methods</p>
<p>For Registered User :<br />carts/mine/payment-methods</p>
<p><strong>API to Place Order</strong></p>
<p>Method : POST</p>
<p><strong>For Guest User :</strong><br />guest-carts/&lt;token&gt;/order</p>
<p><strong>For Registered User :</strong><br />carts/mine/payment-information</p>
<p>Find any Issue or Support for any Magento2x Projects email to : nasihul.ameen@zyxware.com</p>
<p><br />##########################################################</p>
<p>&nbsp;</p>
