# trusted - SSL certificates manager

[![Latest Stable Version](https://poser.pugx.org/designoid/trusted/v/stable.png)](https://packagist.org/packages/designoid/trusted) [![License](https://poser.pugx.org/designoid/trusted/license.png)](https://packagist.org/packages/designoid/trusted)

This simple SSL certificates manager includes the following features:
* Individual root CA setup to sign certficate sign requests (CSR)
* Manage users and their permissions based on domains
* Create SSL certficates with private key
* Intuitive and simple to use GUI based on bootstrap
* Built with Laravel 4 and passion in Berlin



## Installation (Vagrant virtual machine)
### Pre-Requirements
* only [Vagrant](https://www.vagrantup.com/)

### Setup
* Clone the archive: `git clone https://github.com/designoid/trusted.git`
* Change into trusted directory: `cd trusted`
* Fire up the vagrant machine: `vagrant up`
* Wait a few minutes a vagrant magically sets up the virtual machine

Open up `localhost:8080` in your browser follow the instructions under Usage.

Vagrant will 

## Installation (Stand-alone machine)
### Pre-Requirements
* git
* composer
* openssl
* php5-sqlite

### Setup
* Clone the archive: `git clone https://github.com/designoid/trusted.git`
* Change into trusted directory: `cd trusted`
* Install composer dependencies: `composer install`
* Set up the app: `php artisan trusted:setup`
* Add a vhost with `trusted/public` as DocumentRoot.

## Usage

After the setup open the app in a browser of your choice. You will be asked for username and password. Initial credentials are admin/password.

Create a root CA first. Afterwards create users and certificates.
You can determine different domains of a user seperating them by comma.

Enjoy and contribute!


## License

Package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
