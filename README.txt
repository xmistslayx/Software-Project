In case autoload fails:
1. Install composer
2. Install all dependencies by using the following command:



How to setup payments_page.php:
1. Make a .env file in webroot (.htaccess will block access to it via the url and .gitignore will block it from being uploaded onto github)
2. Insert stripe secret keys or stripe restricted keys, each with a reference assigned with one key assigned as a fallback key.
e.g. stripe_first_key = rk_test_...
     stripe_second_key = rk_test_...

     stripe_fallback_key = rk_test_...