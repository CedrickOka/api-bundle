**Getting Started With OkaApiBundle**
=====================================

This bundle provides a set of components for the design of a REST API.

Prerequisites
=============

The OkaApiBundle has the following requirements:

 - PHP 5.6+
 - Symfony 3.4+

Installation
============

Installation is a quick (I promise!) 5 step process:

1. Download OkaApiBundle
2. Enable the Bundle
3. Create your WsseUser class
4. Configure the Bundle
5. Update your database schema

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require coka/api-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Oka\ApiBundle\OkaApiBundle(),
        ];

        // ...
    }

    // ...
}
```

Step 3: Create your WsseUser class
----------------------------------

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
look, depending on how you're storing your entities.

**Note:**

> The doc uses a bundle named `AcmeApiBundle`. If you want to use the same
> name, you need to register it in your kernel. But you can of course place
> your `WsseUser` class in the bundle you want.

**Warning:**

> If you override the __construct() method in your WsseUser class, be sure
> to call parent::__construct(), as the base WsseUser class depends on
> this to initialize some fields.

#### Doctrine ORM WsseUser class

you must persisting your entity via the Doctrine ORM, then your `WsseUser` class
should live in the `Entity` namespace of your bundle and look like this to
start:

##### Annotations

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

##### YAML

If you use yml to configure Doctrine you must add two files. The Entity and the orm.yml:

```php
<?php
// src/Acme/ApiBundle/Entity/WsseUser.php

namespace Acme\ApiBundle\Entity;

use Oka\ApiBundle\Model\WsseUser as BaseWsseUser;

/**
 * WsseUser
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
# src/Acme/ApiBundle/Resources/config/doctrine/WsseUser.orm.yml
Acme\ApiBundle\Entity\WsseUser:
    type:  entity
    table: oka_api_wsse_user
    id:
        id:
            type: integer
            generator:
                strategy: AUTO
```

Step 4: Configure the Bundle
----------------------------

Add the following configuration to your `config.yml`.

``` yaml
# app/config/config.yml
oka_api:
    model_manager_name: null
    host: api.acme.com
    log_channel: api
    cors:
        default:
            pattern: '^.*/cors/test$'
            origins:
                - 'http://acme.com'
            allow_methods: {  }
            allow_headers: {  }
            allow_credentials: false
            expose_headers: {  }
            max_age: 3600
    firewalls:
        wsse:
            enabled: true
            user_class: Acme\ApiBundle\Entity\WsseUser
            enabled_allowed_ips_voter: true # Enables the voter that allows access to requests at only certain ips allocated for the current authenticated client
            log_channel: wsse
```

Add the following configuration to your `security.yml`.

``` yaml
# app/config/security.yml
security:
# Add `wsse_user_provider` in providers configuration section
    providers:
        wsse_user_provider:
            id: oka_api.wsse_user_provider

# Add `wsse` in firewalls configuration section
    firewalls:
        wsse:
            request_matcher:  oka_api.wsse.firewall.request_matcher
            provider: wsse_user_provider
            stateless: true
            wsse: { lifetime: 300 }
            anonymous: true

# To activate the wsse voter which control user access by ip 
# you must define at least one entry in your `access_control`
    access_control:
      - { path: '^/v1/rest', host: 'api.exemple.com', roles: ROLE_API_USER }

# Define strategy decision like `unanimous` for allows wsse voter has denied access or abstain
    access_decision_manager:
        strategy: unanimous
```

``` yaml
# app/config/security.yml
security:
    access_control:
      - { path: '^/v1/rest', host: 'api.exemple.com', roles: ROLE_API_USER }
```

Step 5: Update your database schema
-----------------------------------

Now that the bundle is configured, the last thing you need to do is update your
database schema because you have added a new entity, the `WsseUser` class which you
created in Step 4.

Run the following command.

``` bash
$ php app/console doctrine:schema:update --force
```

You now can access at the index page `http://app.com/app_dev.php/`!

How use this?
=============

Now that the bundle is installed

```
curl -i http://app.com/app_dev.php -X GET -H 'Authorization: UsernameToken Username="admin", PasswordDigest="53dGT2c83M446zUJfpr9lanpeY0=", Nonce="MTM3OGM2YzJlZDYyNDE5Ng==", Created="2019-03-30T09:52:33Z"'
```
