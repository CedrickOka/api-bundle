**Getting Started With OkaApiBundle**
=========================================

This bundle provides a flexible management of the api.

##Prerequisites

The OkaApiBundle has the following requirements:
 - PHP 5.5
 - Symfony 2.5+
 - JMSSerializerBundle @stable

##Installation

Installation is a quick (I promise!) 6 step process:

1. Download OkaApiBundle
2. Enable the Bundle
3. Create your WsseUser class
4. Configure the OkaApiBundle
5. Import OkaApiBundle routing
6. Update your database schema

###Step 1: Download OkaApiBundle

You must procured one copy of this bundle. Install the bundle to your project's 'vendor/oka/api-bundle' directory.

###Step 2: Enable the Bundle

After the copy of the files, register the namespace in app/autoload.php:

``` php
<?php
// app/autoload.php
// ...

$loader->setPsr4('Oka\\ApiBundle\\', __DIR__.'/../vendor/oka/api-bundle');
```

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php
// ...

class AppKernel extends Kernel
{
	public function registerBundles()
	{
		$bundles = array(
			// ...
			
			new Oka\ApiBundle\OkaApiBundle(),
		);
		
		// ...
	}
	
	// ...
}
```

###Step 3: Create your WsseUser class

The goal of this bundle is to  persist some `WsseUser` class to a database (MySql). 
Your first job, then, is to create the `WsseUser` class for you application. 
This class can look and act however you want: add any
properties or methods you find useful. This is *your* `WsseUser` class.

The bundle provides base classes which are already mapped for most fields
to make it easier to create your entity. Here is how you use it:

1. Extend the base `WsseUser` class (from the ``Model`` folder)
2. Map the `id` field. It must be protected as it is inherited from the parent class.

**Warning:**

> When you extend from the mapped superclass provided by the bundle, don't
> redefine the mapping for the other fields as it is provided by the bundle.

Your `WsseUser` class can live inside any bundle in your application. For example,
if you work at "Acme" company, then you might create a bundle called `AcmeApiBundle`
and place your `WsseUser` class in it.

In the following sections, you'll see examples of how your `WsseUser` class should
look, depending on how you're storing your pictures.

**Note:**

> The doc uses a bundle named `AcmeApiBundle`. If you want to use the same
> name, you need to register it in your kernel. But you can of course place
> your `WsseUser` class in the bundle you want.

**Warning:**

> If you override the __construct() method in your Picture  class, be sure
> to call parent::__construct(), as the base User class depends on
> this to initialize some fields.

####Doctrine ORM Picture class

you must persisting your pictures via the Doctrine ORM, then your `WsseUser` class
should live in the `Entity` namespace of your bundle and look like this to
start:

#####Annotations

```php
<?php
// src/Acme/ApiBundle/Entity/WsseUser.php

namespace Acme\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oka\ApiBundle\Model\WsseUser as BaseWsseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="oka_api_wsse_user")
 */
class WsseUser extends BaseWsseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
```

#####YAML

If you use yml to configure Doctrine you must add two files. The Entity and the orm.yml:

```php
<?php
// src/Acme/ApiBundle/Entity/WsseUser.php

namespace Acme\ApiBundle\Entity;

use Oka\ApiBundle\Model\WsseUser as BaseWsseUser;

/**
 * Picture
 */
class WsseUser extends BaseWsseUser
{
	public function __construct()
	{
		parent::__construct();
		// your own logic
	}
}
```

```yaml
#####src/Acme/ApiBundle/Resources/config/doctrine/WsseUser.orm.yml
Acme\ApiBundle\Entity\WsseUser:
    type:  entity
    table: oka_api_wsse_user
    id:
        id:
            type: integer
            generator:
                strategy: AUTO
```

###Step 4: Configure the OkaApiBundle

Add the following configuration to your `config.yml`.

``` yaml
# app/config/config.yml
oka_api:
    user_class: Acme\ApiBundle\Entity\User
    wsse_user_class: Acme\ApiBundle\Entity\WsseUser
    host: "%web_host.api%"
    log_channel: "api"
    cors:
        allowed_origins: [ "http://%web_host%" ]
        expose_headers: []
    firewalls:
        wsse:
            log_channel: "wsse"
        jwt:
            route_user_param: "id"
            log_channel: "jwt"
```

### Step 5: Import OkaApiBundle routing

Now that you have activated and configured the bundle, all that is left to do is
import the OkaFileManagerBundle routing files.

By importing the routing files you will have ready made pages for things such as
uploading, cropping pictures, etc.

In YAML:

``` yaml
# app/config/routing.yml
oka_api:
    resource: "@OkaApiBundle/Resources/config/routing.yml"
```

### Step 6: Update your database schema

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `WsseUser` class which you
created in Step 4.

Run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```

You now can access at the index page `http://app.com/app_dev.php/`!